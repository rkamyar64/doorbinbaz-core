<?php

namespace App\Http\Controllers;

use App\Http\Libs\Response;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Business;
use App\Models\Services;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="Service management endpoints"
 * )
 */
class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     summary="Get list of services",
     *     description="Retrieve a paginated list of services with optional search functionality",
     *     operationId="getServices",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term to filter services by name, family, business_name, mobile, or national_id",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Services retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Web Development"),
     *                     @OA\Property(property="family", type="string", example="Technology"),
     *                     @OA\Property(property="business_name", type="string", example="Tech Solutions Ltd"),
     *                     @OA\Property(property="mobile", type="string", example="+1234567890"),
     *                     @OA\Property(property="national_id", type="string", example="123456789"),
     *                     @OA\Property(property="price", type="number", format="float", example=99.99),
     *                     @OA\Property(property="description", type="string", example="Professional web development services"),
     *                     @OA\Property(property="store_user_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                     @OA\Property(
     *                         property="store_user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error retrieving Service"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $businesses = Services::with('storeUser');

            if ($request->filled('q')) {
                $searchTerm = $request->q;

                $businesses->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('family', 'like', '%' . $searchTerm . '%')
                        ->orWhere('business_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
                        ->orWhere('national_id', 'like', '%' . $searchTerm . '%');
                });
            }

            $businesses = $businesses->get();
            return Response::success("Service retrieved successfully", $businesses);

        } catch (\Exception $e) {
            return Response::error("Error retrieving Service", [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services",
     *     summary="Create a new service",
     *     description="Create a new service with the provided data",
     *     operationId="createService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Service data",
     *         @OA\JsonContent(
     *             required={"name", "price", "description"},
     *             @OA\Property(property="name", type="string", example="Web Development", description="Service name (must be unique)"),
     *             @OA\Property(property="family", type="string", example="Technology", description="Service category/family"),
     *             @OA\Property(property="business_name", type="string", example="Tech Solutions Ltd", description="Business name"),
     *             @OA\Property(property="mobile", type="string", example="+1234567890", description="Contact mobile number"),
     *             @OA\Property(property="national_id", type="string", example="123456789", description="National ID"),
     *             @OA\Property(property="price", type="number", format="float", example=99.99, description="Service price"),
     *             @OA\Property(property="description", type="string", example="Professional web development services", description="Service description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Web Development"),
     *                 @OA\Property(property="family", type="string", example="Technology"),
     *                 @OA\Property(property="business_name", type="string", example="Tech Solutions Ltd"),
     *                 @OA\Property(property="mobile", type="string", example="+1234567890"),
     *                 @OA\Property(property="national_id", type="string", example="123456789"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="description", type="string", example="Professional web development services"),
     *                 @OA\Property(property="store_user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(
     *                     property="store_user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Please enter your service name.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error creating Service"),
     *             @OA\Property(property="data", type="string", example="Database connection failed")
     *         )
     *     )
     * )
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['store_user_id'] = auth()->id();
            $business = Services::create($validated);
            $business->load('storeUser');
            return Response::success("Service created successfully", $business);

        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);

        } catch (\Exception $e) {
            return Response::error("Error creating Service", $e->getMessage(), 500);

        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{service}",
     *     summary="Update an existing service",
     *     description="Update a service with the provided data",
     *     operationId="updateService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Service ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Service data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Advanced Web Development", description="Service name"),
     *             @OA\Property(property="family", type="string", example="Technology", description="Service category/family"),
     *             @OA\Property(property="business_name", type="string", example="Tech Solutions Ltd", description="Business name"),
     *             @OA\Property(property="mobile", type="string", example="+1234567890", description="Contact mobile number"),
     *             @OA\Property(property="national_id", type="string", example="123456789", description="National ID"),
     *             @OA\Property(property="price", type="number", format="float", example=149.99, description="Service price"),
     *             @OA\Property(property="description", type="string", example="Advanced professional web development services", description="Service description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Advanced Web Development"),
     *                 @OA\Property(property="family", type="string", example="Technology"),
     *                 @OA\Property(property="business_name", type="string", example="Tech Solutions Ltd"),
     *                 @OA\Property(property="mobile", type="string", example="+1234567890"),
     *                 @OA\Property(property="national_id", type="string", example="123456789"),
     *                 @OA\Property(property="price", type="number", format="float", example=149.99),
     *                 @OA\Property(property="description", type="string", example="Advanced professional web development services"),
     *                 @OA\Property(property="store_user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(
     *                     property="store_user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="This name is already registered.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Service not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Service not found"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error creating Service"),
     *             @OA\Property(property="data", type="string", example="Database connection failed")
     *         )
     *     )
     * )
     *
     * @OA\Patch(
     *     path="/api/v1/services/{service}",
     *     summary="Partially update an existing service",
     *     description="Partially update a service with the provided data",
     *     operationId="partialUpdateService",
     *     tags={"Services"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="service",
     *         in="path",
     *         description="Service ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Service data to update (partial)",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Advanced Web Development", description="Service name"),
     *             @OA\Property(property="price", type="number", format="float", example=149.99, description="Service price")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Service updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Advanced Web Development"),
     *                 @OA\Property(property="price", type="number", format="float", example=149.99)
     *             )
     *         )
     *     )
     * )
     */
    public function update(StoreServiceRequest $request, Services $service): JsonResponse
    {
        try {
            $validated = $request->validated();

            $service->update($validated);
            $service->load('storeUser');
            return Response::success("Service updated successfully", $service);

        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);
        } catch (\Exception $e) {
            return Response::error("Error creating Service", $e->getMessage(), 500);
        }
    }
}
