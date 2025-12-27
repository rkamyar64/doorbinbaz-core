<?php

namespace App\Http\Controllers;

use App\Http\Libs\GetApiData;
use App\Http\Libs\Response;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Business;
use App\Models\Orders;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders/show",
     *     summary="Get all orders for authenticated user",
     *     description="Retrieve all orders belonging to the authenticated store user with optional search functionality",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term to filter orders by description, status, prices, service users, or business details",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="orders retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="store_user_id", type="integer", example=1),
     *                     @OA\Property(property="business_id", type="integer", example=1),
     *                     @OA\Property(property="service_user_id", type="integer", example=2),
     *                     @OA\Property(property="services", type="string", example="Service details"),
     *                     @OA\Property(property="description", type="string", example="Order description"),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="full_price", type="number", format="float", example=1000.00),
     *                     @OA\Property(property="fee_price", type="number", format="float", example=100.00),
     *                     @OA\Property(property="profit_price", type="number", format="float", example=50.00),
     *                     @OA\Property(property="discount", type="number", format="float", example=10.00),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
     *                     @OA\Property(property="storeUser", type="object"),
     *                     @OA\Property(property="serviceUsers", type="object"),
     *                     @OA\Property(property="businessId", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving orders")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orders = Orders::with(['storeUser','serviceUsers','businessId'])->where('store_user_id', $request->user()->id);

            if ($request->filled('q')) {
                $searchTerm = $request->q;

                $orders->where(function ($query) use ($searchTerm) {
                    // Search in the main Orders table
                    $query->where('description', 'like', '%' . $searchTerm . '%')
                        ->orWhere('status', 'like', '%' . $searchTerm . '%')
                        ->orWhere('full_price', 'like', '%' . $searchTerm . '%')
                        ->orWhere('fee_price', 'like', '%' . $searchTerm . '%')
                        ->orWhere('profit_price', 'like', '%' . $searchTerm . '%')
                        ->orWhere('discount', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('serviceUsers', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('family', 'like', '%' . $searchTerm . '%');
                        })
                        ->orWhereHas('businessId', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('family', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('national_id', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('zipcode', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('mobile', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('address', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('business_name', 'like', '%' . $searchTerm . '%');
                            $subQuery->orWhere('tell', 'like', '%' . $searchTerm . '%');

                        });
                });
            }

            $orders = $orders->orderBy('id', 'desc')->get();
            return Response::success("orders retrieved successfully", $orders);

        } catch (\Exception $e) {
            return Response::error("Error retrieving orders", [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/store",
     *     summary="Create a new order",
     *     description="Create a new order for the authenticated store user",
     *     operationId="createOrder",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"business_id"},
     *             @OA\Property(property="business_id", type="integer", example=1, description="Business ID"),
     *             @OA\Property(property="service_user_id", type="integer", example=2, description="Service user ID"),
     *             @OA\Property(property="services", type="string", example="Haircut and styling", description="Services provided"),
     *             @OA\Property(property="description", type="string", example="Regular haircut with styling", description="Order description"),
     *             @OA\Property(property="status", type="string", example="pending", description="Order status (pending, completed, cancelled, etc.)"),
     *             @OA\Property(property="full_price", type="integer", format="float", example=1000.00, description="Full price of the order"),
     *             @OA\Property(property="fee_price", type="integer", format="float", example=100.00, description="Fee price"),
     *             @OA\Property(property="profit_price", type="integer", format="float", example=50.00, description="Profit price"),
     *             @OA\Property(property="discount", type="integer", format="float", example=10.00, description="Discount amount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error creating Order")
     *         )
     *     )
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['store_user_id'] = auth()->id();
            $orders = Orders::create($validated);
            $orders->load('storeUser');
            $orders->load('serviceUsers');
            $orders->load('businessId');

           // (new GetApiData())->KaveNegarPattern($orders->businessId->mobile,'new-service',$orders->id);
            (new GetApiData())->KaveNegarPattern($orders->businessId->mobile,'new-service',$orders->businessId->name."_".$orders->businessId->family,$orders->businessId->business_name,$orders->id);

            return Response::success("Order created successfully", $orders->only(['id','businessId','serviceUsers', 'storeUser', 'services', 'description', 'status', 'full_price', 'fee_price', 'profit_price', 'discount','created_at']));

        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);

        } catch (\Exception $e) {
            return Response::error("Error creating Order", $e->getMessage(), 500);

        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/{orders}",
     *     summary="Update an existing order",
     *     description="Update an existing order by ID",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="orders",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="business_id", type="integer", example=1, description="Business ID"),
     *             @OA\Property(property="service_user_id", type="integer", example=2, description="Service user ID"),
     *             @OA\Property(property="services", type="string", example="Haircut and styling", description="Services provided"),
     *             @OA\Property(property="description", type="string", example="Regular haircut with styling", description="Order description"),
     *             @OA\Property(property="status", type="string", example="completed", description="Order status"),
     *             @OA\Property(property="full_price", type="number", format="float", example=1000.00, description="Full price of the order"),
     *             @OA\Property(property="fee_price", type="number", format="float", example=100.00, description="Fee price"),
     *             @OA\Property(property="profit_price", type="number", format="float", example=50.00, description="Profit price"),
     *             @OA\Property(property="discount", type="number", format="float", example=10.00, description="Discount amount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error updating Order")
     *         )
     *     )
     * )
     */
    public function update(StoreOrderRequest $request, Orders $orders): JsonResponse
    {
        try {
            $validated = $request->validated();
            $orders->fill($validated);
            $serviceUserChanged = $orders->isDirty('service_user_id');
            $orders->save();
            if ($serviceUserChanged) {
                 (new GetApiData())->KaveNegarPattern($orders->serviceUsers->mobile,'serviceman-sms',$orders->id);
            }

            return Response::success("Order updated successfully",  $orders->only(['id','businessId','serviceUsers', 'storeUser', 'services', 'description', 'status', 'full_price', 'fee_price', 'profit_price', 'discount','created_at']));


        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);
        } catch (\Exception $e) {
            return Response::error("Error updating Order", $e->getMessage(), 500);
        }
    }


    public function getByUser(User $user): JsonResponse
    {
        try {
            $businesses = $user->businesses; // Make sure you have this relationship in User model

            return response()->json([
                'success' => true,
                'message' => 'User businesses retrieved successfully',
                'data' => $businesses
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user businesses',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function orders(Orders $id): JsonResponse
    {
        try {
            return Response::success("Showing order successfully",  $id->only(['id','businessId','serviceUsers', 'storeUser', 'services', 'description', 'status', 'full_price', 'fee_price', 'profit_price', 'discount','created_at']));

        }catch (\Exception $e) {
            return Response::error("Error showing order", $e->getMessage(), 500);
        }
    }

}
