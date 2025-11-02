<?php

namespace App\Http\Controllers;

use App\Models\AriaCron;
use App\Models\ArioMapDoorbinbaz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArioController extends Controller
{
    private $baseUrl = 'https://arioimen.com/wp-json/wc/store/products';
    private $perPage = 100;
    private $totalPages = 30;

    public function import()
    {
        ini_set('max_execution_time', '0');
        $totalImported = 0;
        $errors = [];


        for ($page = 1; $page <= $this->totalPages; $page++) {
            try {
                $imported = $this->importPage($page);
                $totalImported += $imported;

                // Add a small delay to be respectful to the API
                usleep(500000); // 0.5 second delay

            } catch (\Exception $e) {
                $errorMsg = "Error importing page {$page}: " . $e->getMessage();
                $errors[] = $errorMsg;
                continue;
            }
        }


        return response()->json([
            'success' => true,
            'message' => "Import completed successfully!",
            'total_imported' => $totalImported,
            'total_pages_processed' => $this->totalPages,
            'errors' => $errors
        ]);
    }

    private function importPage($page)
    {
        $url = $this->baseUrl . "?per_page={$this->perPage}&page={$page}";

        $response = Http::timeout(300)->withOptions([
            'verify' => false,
            'timeout' => 30
        ])->get($url);

        if (!$response->successful()) {

            throw new \Exception("API request failed with status: " . $response->status());
        }

        $products = $response->json();


        $importedCount = 0;


        foreach ($products as $productData) {
            try {
                $this->saveProduct($productData);
                $importedCount++;
            } catch (\Exception $e) {
            }
        }

        return $importedCount;
    }

    private function saveProduct($data)
    {
        if (isset($data["prices"]) && isset($data["prices"]["price"])) {
            $price = $data["prices"]["price"];
            $regular_price = $data["prices"]["regular_price"];
        }
        if (isset($data["add_to_cart"]) && isset($data["add_to_cart"]["maximum"])) {
            $maximum = $data["add_to_cart"]["maximum"];
        }
        if($data['stock_availability']['class']=="out-of-stock")
            $status = false;
        else
            $status = true;
        AriaCron::updateOrCreate(
            ['wc_id' => $data['id']],
            [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'permalink' => $data['permalink'] ?? '',
                'sku' => $data['sku'],
                'description' => $data['description'] ?? '',
                'price' => $price,
                'regular_price' => $regular_price,
                'images' => json_encode($data['images'] ?? []),
                'is_in_stock' => $status,
                'maximum' => $maximum,
                'other' => json_encode($data ?? []),
            ]
        );

    }

    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     tags={"Products"},
     *     summary="Get products with pagination and filters",
     *     description="Retrieve a paginated list of products with optional filtering by search, sku, name, and slug",
     *     operationId="getProducts",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of products per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, minimum=1, maximum=100),
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1),
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term to filter products by name, description, sku, or other fields",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="Exact SKU to filter products",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Product name to filter (partial match)",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="query",
     *         description="Product slug to filter (partial match)",
     *         required=false,
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="products",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true,),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(
     *                 property="stats",
     *                 type="object",
     *                 @OA\Property(property="total_products", type="integer", example=150, description="Total number of products in database"),
     *                 @OA\Property(property="in_stock_products", type="integer", example=120, description="Number of products in stock"),
     *                 @OA\Property(property="out_of_stock_products", type="integer", example=30, description="Number of products out of stock")
     *             ),
     *             @OA\Property(
     *                 property="filters",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"search", "sku", "name", "slug"},
     *                 description="Available filter parameters"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - invalid parameters"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['search','sku','name', 'slug']);
        $products = $this->getAllProducts($perPage, $filters);
        $filter_by=['search','sku','name', 'slug'];

        $stats = $this->getProductStats();

        if ($request->expectsJson()) {
            return response()->json([
                'products' => $products,
                'stats' => $stats,
            ]);
        }
        return response()->json([
            'products' => $products,
            'stats' => $stats,
            'filters' => $filter_by
        ]);
    }

    public function getAllProducts($perPage = 15, $filters = [])
    {
        $query = AriaCron::query();

        // Apply filters if provided
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('other', 'LIKE', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'LIKE', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['slug'])) {
            $query->where('slug', 'LIKE', '%' . $filters['slug'] . '%');
        }
        if (!empty($filters['name'])) {
            $query->where('name', 'LIKE', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['sku'])) {
            $query->where('sku', $filters['sku']);
        }


        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }
    public function getProductStats()
    {
        return [
            'total_products' => AriaCron::count(),
            'in_stock_products' => AriaCron::where('is_in_stock', true)->count(),
            'out_of_stock_products' => AriaCron::where('is_in_stock', false)->count(),
        ];
    }

}



