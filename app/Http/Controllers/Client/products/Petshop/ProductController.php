<?php

namespace App\Http\Controllers\Client\products\petshop;

use App\Models\Category; // Asegúrate de importar Category y Product
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    // Método para la ruta GET /productos/petshop (client.products.petshop)
    public function index(Request $request)
    {
        // Define la categoría principal para la página (ej. 'Petshop' con ID 1)
        // Ajusta este ID según la ID real de tu categoría "Petshop" en la base de datos
        $mainPetshopCategoryId = 1; 
        $currentMainPageCategory = Category::find($mainPetshopCategoryId);

        if (!$currentMainPageCategory) {
            abort(404, 'Categoría principal "Petshop" no encontrada.');
        }

        // Obtener la categoría por la que se está filtrando (desde el dropdown o por defecto la principal)
        $filterCategoryId = $request->input('category_id', $mainPetshopCategoryId);
        $searchQuery = $request->input('query');

        // ********************************************************************************
        // CAMBIO CLAVE AQUÍ: Usar allDescendantIds() para obtener todos los IDs relevantes
        // Esto incluye la categoría principal y todos sus descendientes, sin importar la profundidad.
        $categoryAndDescendantIds = $currentMainPageCategory->allDescendantIds();
        // ********************************************************************************

        // Lógica de consulta de productos
        $productsQuery = Product::with('category', 'promotions')
            // ********************************************************************************
            // CAMBIO CLAVE AQUÍ: Usar whereIn en lugar de whereHas con orWhere anidado.
            // Esto consulta directamente los productos que tienen una category_id dentro de los IDs obtenidos.
            ->whereIn('category_id', $categoryAndDescendantIds); 
            // ********************************************************************************

        // Aplicar filtro por categoría si se seleccionó una subcategoría específica
        // Esta condición ahora significa: si el filtro no es "Todas las categorías" (que es el ID principal)
        // Y si el ID de filtro es realmente parte de la jerarquía de la categoría actual.
        if ($filterCategoryId != $mainPetshopCategoryId) {
            // Asegurarse de que el filterCategoryId exista dentro de la jerarquía de categorías de la página actual
            if (in_array($filterCategoryId, $categoryAndDescendantIds)) {
                $productsQuery->where('category_id', $filterCategoryId);
            } else {
                // Opcional: Si el filterCategoryId no es válido para esta jerarquía, no mostrar nada o un error.
                // Para este caso, simplemente devolveremos 0 productos si el ID no es válido.
                $productsQuery->whereRaw('1 = 0'); // Esto siempre es falso, no devuelve productos.
            }
        }

        // Aplicar filtro por búsqueda
        if ($searchQuery) {
            $productsQuery->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }

        // Si es una petición AJAX, devuelve JSON
        if ($request->ajax()) {
            $products = $productsQuery->get();
            $formattedProducts = $products->map(function ($product) {
                $activePromotions = $product->getActivePromotions();
                $appliedData = $product->applyPromotions($product->price, 1, $activePromotions);
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => number_format($product->price, 2, '.', ''), // Asegurar formato de string
                    'final_price' => number_format($appliedData['final_price_per_unit'], 2, '.', ''),
                    'gift_quantity' => $appliedData['gift_quantity'],
                    'applied_promotions' => $appliedData['applied_promotion_titles'],
                    'category_name' => $product->category->name ?? 'N/A',
                    'image_url' => asset('storage/' . $product->image),
                    'stock' => $product->stock,
                ];
            });

            // Obtener todas las categorías para el filtro (solo las subcategorías directas para el dropdown)
            // Ya que 'Todas las categorías' es el ID principal
            $allCategories = Category::where('parent_id', $mainPetshopCategoryId) 
                                     ->orWhere('id', $mainPetshopCategoryId) // Incluir la categoría principal para la opción "Todas"
                                     ->get();

            // Determinar el nombre de la categoría mostrada para el título
            $displayingCategoryName = $currentMainPageCategory->name ?? 'Mascota';

            return response()->json([
                'products' => $formattedProducts,
                'categories' => $allCategories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name]),
                'displaying_category_name' => $displayingCategoryName,
                'current_main_category_id' => $mainPetshopCategoryId,
            ]);

        }

        // Si no es AJAX, devuelve la vista Blade
        $productos = $productsQuery->get();
        // Obtener las categorías relacionadas con la principal (Petshop y sus subcategorías directas)
        // para el dropdown de filtro. Esto no cambia.
        $categories = Category::where('parent_id', $mainPetshopCategoryId)
                             ->orWhere('id', $mainPetshopCategoryId)
                             ->get();

        // Pasa las variables a la vista
        return view('client.products.petshop', [
            'productos' => $productos,
            'categories' => $categories,
            'currentMainPageCategory' => $currentMainPageCategory,
            'storeType' => $currentMainPageCategory->name ?? 'Mascota',
        ]);
    }

    // Método para la ruta GET /productos/categoria/{id} (productos.por_categoria)
    public function porCategoriaPadre(Request $request, $id)
    {
        $currentMainPageCategory = Category::find($id);

        if (!$currentMainPageCategory) {
            abort(404, 'Categoría no encontrada.');
        }

        // Obtener la categoría por la que se está filtrando (desde el dropdown o por defecto la principal)
        $filterCategoryId = $request->input('category_id', $id); // Si no se pasa category_id, usa el ID de la URL 
        $searchQuery = $request->input('query');

        // ********************************************************************************
        // CAMBIO CLAVE AQUÍ: Usar allDescendantIds() para obtener todos los IDs relevantes
        // Esto incluye la categoría principal y todos sus descendientes.
        $categoryAndDescendantIds = $currentMainPageCategory->allDescendantIds();
        // ********************************************************************************

        // Lógica de consulta de productos
        $productsQuery = Product::with('category', 'promotions')
            // ********************************************************************************
            // CAMBIO CLAVE AQUÍ: Usar whereIn en lugar de whereHas con orWhere anidado.
            // Esto consulta directamente los productos que tienen una category_id dentro de los IDs obtenidos.
            ->whereIn('category_id', $categoryAndDescendantIds);
            // ********************************************************************************

        // Aplicar filtro por categoría si se seleccionó una subcategoría específica
        // Esta condición ahora significa: si el filtro no es "Todas las categorías" (que es el ID principal)
        if ($filterCategoryId != $currentMainPageCategory->id) {
            // Asegurarse de que el filterCategoryId exista dentro de la jerarquía de categorías de la página actual
            if (in_array($filterCategoryId, $categoryAndDescendantIds)) {
                $productsQuery->where('category_id', $filterCategoryId);
            } else {
                $productsQuery->whereRaw('1 = 0'); // No devuelve productos si el ID no es válido.
            }
        }

        // Aplicar filtro por búsqueda
        if ($searchQuery) {
            $productsQuery->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%')
                      ->orWhere('description', 'like', '%' . $searchQuery . '%');
            });
        }

        // Si es una petición AJAX, devuelve JSON
        if ($request->ajax()) {
            $products = $productsQuery->get();
            $formattedProducts = $products->map(function ($product) {
                $activePromotions = $product->getActivePromotions();
                $appliedData = $product->applyPromotions($product->price, 1, $activePromotions);
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => number_format($product->price, 2, '.', ''),
                    'final_price' => number_format($appliedData['final_price_per_unit'], 2, '.', ''),
                    'gift_quantity' => $appliedData['gift_quantity'],
                    'applied_promotions' => $appliedData['applied_promotion_titles'],
                    'category_name' => $product->category->name ?? 'N/A',
                    'image_url' => asset('storage/' . $product->image),
                    'stock' => $product->stock,
                ];
            });

            // Obtener todas las categorías relacionadas con esta `currentMainPageCategory`
            $allCategories = Category::where('parent_id', $currentMainPageCategory->id) // Subcategorías directas
                                     ->orWhere('id', $currentMainPageCategory->id) // La categoría padre misma
                                     ->get();

            return response()->json([
                'products' => $formattedProducts,
                'categories' => $allCategories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name]),
                'displaying_category_name' => $currentMainPageCategory->name,
                'current_main_category_id' => $currentMainPageCategory->id,
            ]);
        }

        // Si no es AJAX, devuelve la vista Blade
        $productos = $productsQuery->get();
        // Obtener las categorías relacionadas para el dropdown de filtro
        $categories = Category::where('parent_id', $currentMainPageCategory->id)
                             ->orWhere('id', $currentMainPageCategory->id)
                             ->get();

        // Pasa las variables a la vista
        return view('client.products.petshop', [
            'productos' => $productos,
            'categories' => $categories,
            'currentMainPageCategory' => $currentMainPageCategory,
            'storeType' => $currentMainPageCategory->name,
        ]);
    }
}