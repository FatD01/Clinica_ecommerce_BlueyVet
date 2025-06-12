<?php

namespace App\Http\Controllers\Client\Products\Petshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Muestra la página de productos de Petshop por defecto, o maneja un filtro general.
     * Este es el punto de entrada para la ruta /productos/petshop (o /productos).
     */
     public function index(Request $request)
    {
        // 1. Determinar la categoría principal de la página (Petshop, ID 1)
        $currentMainPageCategory = Category::find(1); // ID de la categoría "Petshop"

        if (!$currentMainPageCategory) {
            abort(404, 'Categoría principal "Petshop" no encontrada.');
        }

        // Obtener TODAS las categorías que pertenecen a esta rama (incluyendo la principal y todos sus descendientes)
        // Esto se usará para el select del filtro.
        $relevantCategoryIdsForFilter = $currentMainPageCategory->allDescendantIds()->push($currentMainPageCategory->id)->toArray();
        $categories = Category::whereIn('id', $relevantCategoryIdsForFilter)->get();

        // 2. Iniciar la consulta de productos
        $query = Product::with(['promotions', 'category']); // Incluir la relación 'category' aquí

        // 3. Determinar qué ID de categoría usar para la consulta de productos.
        // Si hay un 'category_id' en el request y es diferente de vacío, usarlo.
        // Si no hay o es vacío, usar el ID de la categoría principal de la página.
        $categoryIdToFilterBy = $request->category_id; // Esto será el ID del select o ""

        // Si el filtro es nulo o vacío, o es el mismo ID de la categoría principal,
        // significa que queremos mostrar "todas las categorías" dentro de esta rama principal.
        if (empty($categoryIdToFilterBy) || $categoryIdToFilterBy == $currentMainPageCategory->id) {
            $productCategoryIds = $relevantCategoryIdsForFilter; // Todos los IDs de la rama principal
            $displayingCategory = $currentMainPageCategory; // El contexto actual es la categoría principal
        } else {
            // Si se seleccionó una categoría específica desde el filtro (ej. "Gatos/Petshop")
            $selectedCategory = Category::find($categoryIdToFilterBy);
            if ($selectedCategory && in_array($selectedCategory->id, $relevantCategoryIdsForFilter)) {
                // Asegurarse de que la categoría seleccionada es parte de la rama actual de la tienda
                $productCategoryIds = $selectedCategory->allDescendantIds()->push($selectedCategory->id)->toArray();
                $displayingCategory = $selectedCategory; // El contexto actual es la categoría seleccionada
            } else {
                // Si la categoría seleccionada no existe o no es de esta rama, volvemos al default de la rama principal
                $productCategoryIds = $relevantCategoryIdsForFilter;
                $displayingCategory = $currentMainPageCategory;
            }
        }
        
        $query->whereIn('category_id', $productCategoryIds);

        // --- INICIO: Lógica de Búsqueda ---
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }
        // --- FIN: Lógica de Búsqueda ---

        // 4. Ejecutar la consulta para obtener los productos
        $productos = $query->get();

        // 5. Determinar el tipo de tienda para el título de la vista (basado en el contexto actual)
        $storeType = strtolower($displayingCategory->name);

        return view('client.products.petshop', compact('productos', 'categories', 'currentMainPageCategory', 'storeType', 'displayingCategory'));
    }

    /**
     * Muestra productos para una categoría padre específica (ej. desde el navbar).
     * Este es el punto de entrada para la ruta /productos/categoria/{id}.
     */
    public function porCategoriaPadre($id, Request $request)
    {
        // 1. Encontrar la categoría principal de la página basada en el ID de la URL
        $currentMainPageCategory = Category::findOrFail($id);

        // Obtener TODAS las categorías que pertenecen a la rama de esta categoría principal (para el select del filtro)
        $relevantCategoryIdsForFilter = $currentMainPageCategory->allDescendantIds()->push($currentMainPageCategory->id)->toArray();
        $categories = Category::whereIn('id', $relevantCategoryIdsForFilter)->get();

        // 2. Iniciar la consulta de productos
        $query = Product::with(['promotions', 'category']); // Asegúrate de cargar la relación 'category'

        // 3. Determinar qué ID de categoría usar para la consulta de productos.
        // Si hay un 'category_id' en el request y es diferente de vacío, usarlo.
        // Si no hay o es vacío, usar el ID de la categoría principal de la URL.
        $categoryIdToFilterBy = $request->category_id; // Esto será el ID del select o ""

        // Si el filtro es nulo o vacío, o es el mismo ID de la categoría principal de la URL,
        // significa que queremos mostrar "todas las categorías" dentro de esta rama principal.
        if (empty($categoryIdToFilterBy) || $categoryIdToFilterBy == $currentMainPageCategory->id) {
            $productCategoryIds = $relevantCategoryIdsForFilter; // Todos los IDs de la rama principal
            $displayingCategory = $currentMainPageCategory; // El contexto actual es la categoría principal
        } else {
            // Si se seleccionó una categoría específica desde el filtro (ej. "Gatos/Petshop")
            $selectedCategory = Category::find($categoryIdToFilterBy);
            if ($selectedCategory && in_array($selectedCategory->id, $relevantCategoryIdsForFilter)) {
                // Asegurarse de que la categoría seleccionada es parte de la rama actual de la tienda
                $productCategoryIds = $selectedCategory->allDescendantIds()->push($selectedCategory->id)->toArray();
                $displayingCategory = $selectedCategory; // El contexto actual es la categoría seleccionada
            } else {
                // Si la categoría seleccionada no existe o no es de esta rama, volvemos al default de la rama principal
                $productCategoryIds = $relevantCategoryIdsForFilter;
                $displayingCategory = $currentMainPageCategory; // Restaurar a la categoría principal de la URL
            }
        }
        
        $query->whereIn('category_id', $productCategoryIds);

        // --- AÑADE ESTO: Lógica de Búsqueda ---
        if ($request->filled('query')) {
            $searchQuery = $request->input('query');
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }
        // --- FIN DE LA LÓGICA DE BÚSQUEDA ---

        // 4. Ejecutar la consulta para obtener los productos
        $productos = $query->get();

        // 5. Determinar el tipo de tienda y la categoría principal para la vista
        $storeType = strtolower($displayingCategory->name); // Usa el nombre de la categoría que se está mostrando

        return view('client.products.petshop', [
            'productos' => $productos,
            'categories' => $categories,
            'currentMainPageCategory' => $currentMainPageCategory, // Siempre pasa la categoría principal de la URL
            'storeType' => $storeType,
            'displayingCategory' => $displayingCategory, // Esta es la categoría actualmente filtrada (o la principal)
        ]);
    }

}