<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User; // Necesario para obtener el nombre del cliente si se filtra por ID
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Importa Log para mensajes de depuración
use Carbon\Carbon; // Importa Carbon para el manejo de fechas

class OrderExportController extends Controller
{
    /**
     * Exporta los pedidos a un archivo PDF, aplicando los filtros y búsquedas recibidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        Log::info('Iniciando exportación de PDF de Órdenes.');

        // 1. Control de Acceso por Rol (Crucial para seguridad)
        // Asegúrate de que solo los usuarios con el rol 'admin' puedan acceder.
        // Si tu rol es 'Administrador' (con mayúsculas completas), cámbialo aquí.
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            Log::warning('Acceso denegado a exportación de órdenes. Usuario ID: ' . (Auth::id() ?? 'No autenticado'));
            abort(403, 'Acceso denegado. No tienes permisos para realizar esta acción.');
        }

        // 2. Obtener los parámetros de filtro y búsqueda de la solicitud
        $rawFilters = $request->query('filters'); // Filtros de columna de Filament
        $searchTerm = $request->query('table_search'); // Término de búsqueda global de Filament

        // 3. Iniciar la consulta base para el modelo Order
        // Cargar las relaciones necesarias para la vista del PDF (user, orderItems, product)
        $query = Order::with(['user', 'Items.product']);

        // Aplicamos el withoutGlobalScopes para TrashedFilter,
        // ya que la tabla de Filament también lo hace.
        $query->withoutGlobalScopes([\Illuminate\Database\Eloquent\SoftDeletingScope::class]);

        // 4. Procesar y aplicar filtros dinámicamente, y preparar para la vista
        $activeFilters = []; // Array para almacenar los filtros que se mostrarán en el PDF

        if (!empty($rawFilters)) {
            foreach ($rawFilters as $filterName => $filterData) {
                $filterValue = null;

                // Lógica para extraer el valor real del filtro según su tipo
                if (is_array($filterData)) {
                    // Para SelectFilter o filtros con sub-arrays (ej. rango de fechas)
                    if (isset($filterData['value']) && $filterData['value'] !== '') {
                        $filterValue = $filterData['value'];
                    } elseif (isset($filterData['created_from']) || isset($filterData['created_until'])) {
                        // Manejo de filtros de fecha con 'created_from'/'created_until'
                        $dateFrom = $filterData['created_from'] ?? null;
                        $dateUntil = $filterData['created_until'] ?? null;
                        if ($dateFrom) {
                            $query->whereDate('created_at', '>=', $dateFrom);
                            $activeFilters[] = ['label' => 'Fecha Desde', 'value' => Carbon::parse($dateFrom)->format('d/m/Y')];
                            Log::info("Filtro aplicado: Fecha Desde = {$dateFrom}");
                        }
                        if ($dateUntil) {
                            $query->whereDate('created_at', '<=', $dateUntil);
                            $activeFilters[] = ['label' => 'Fecha Hasta', 'value' => Carbon::parse($dateUntil)->format('d/m/Y')];
                            Log::info("Filtro aplicado: Fecha Hasta = {$dateUntil}");
                        }
                        // Continuar al siguiente filtro si ya se procesó el rango de fechas
                        continue;
                    }
                } else {
                    // Para filtros simples (ej. TextInput, Checkbox)
                    $filterValue = $filterData;
                }

                if ($filterValue !== null && $filterValue !== '') {
                    switch ($filterName) {
                        case 'status':
                            $query->where('status', $filterValue);
                            $activeFilters[] = ['label' => 'Estado', 'value' => ucfirst(strtolower($filterValue))];
                            Log::info("Filtro aplicado: Estado = {$filterValue}");
                            break;
                        case 'trashed':
                            if ($filterValue === 'only_trashed') {
                                $query->onlyTrashed();
                                $activeFilters[] = ['label' => 'Elementos eliminados', 'value' => 'Solo eliminados'];
                            } elseif ($filterValue === 'with_trashed') {
                                $query->withTrashed();
                                $activeFilters[] = ['label' => 'Elementos eliminados', 'value' => 'Incluir eliminados'];
                            }
                            Log::info("Filtro aplicado: Trashed = {$filterValue}");
                            break;
                        case 'user_id': // Si tienes un SelectFilter para user_id
                            $query->where('user_id', $filterValue);
                            $userName = User::find($filterValue)->name ?? "ID: {$filterValue}";
                            $activeFilters[] = ['label' => 'Usuario', 'value' => $userName];
                            Log::info("Filtro aplicado: Usuario = {$userName}");
                            break;
                        // Añade más casos aquí para otros filtros que tengas en tu tabla de Filament.
                    }
                }
            }
        }

        // 5. Aplicar búsqueda global si se envía un término
        if (!empty($searchTerm)) {
            Log::info("Aplicando búsqueda global: '{$searchTerm}'");
            $query->where(function ($q) use ($searchTerm) {
                $q->where('paypal_order_id', 'like', "%{$searchTerm}%")
                  ->orWhere('total_amount', 'like', "%{$searchTerm}%") // Puede ser menos preciso para decimales
                  ->orWhere('currency', 'like', "%{$searchTerm}%")
                  ->orWhere('status', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($q2) use ($searchTerm) {
                      $q2->where('name', 'like', "%{$searchTerm}%");
                  });
                // Considera si 'customer_address' también es searchable
                // ->orWhere('customer_address', 'like', "%{$searchTerm}%");
            });
            $activeFilters[] = ['label' => 'Búsqueda Global', 'value' => $searchTerm];
        }


        // 6. Obtener los pedidos filtrados
        $orders = $query->get();

        // 7. Calcular totales para el resumen del PDF
        $totalOrders = $orders->count();
        $totalAmount = $orders->sum('total_amount');

        Log::info("Órdenes encontradas: {$totalOrders}, Monto Total: {$totalAmount}");

        // 8. Preparar los datos para la vista del PDF
        $data = [
            'orders' => $orders,
            'export_date' => Carbon::now('America/Lima')->format('d/m/Y H:i:s'), // Formato completo con segundos
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
            'active_filters' => $activeFilters,
        ];

        // 9. Generar el PDF usando DomPDF
        // Asegúrate de que la vista 'pdf.PdfOrders' exista en resources/views/pdf/PdfOrders.blade.php
        $pdf = Pdf::loadView('pdf.PdfOrders', $data);

        // 10. Descargar el archivo PDF con un nombre descriptivo
        $filename = 'reporte_pedidos_' . Carbon::now('America/Lima')->format('Ymd_His') . '.pdf';
        Log::info("PDF generado y listo para descargar: {$filename}");
        return $pdf->download($filename);
    }
}