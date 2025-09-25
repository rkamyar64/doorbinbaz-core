<?php

namespace App\Http\Controllers;

use App\Http\Libs\Response;
use App\Http\Requests\StoreBusinessRequest;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BusinessController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/businesses/show",
     *     tags={"Businesses"},
     *     summary="Get all businesses",
     *     description="Retrieve a list of all businesses with optional search functionality",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search term to filter businesses by name, family, business_name, mobile, or national_id",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="john"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Businesses retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Businesses retrieved successfully"
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error retrieving businesses"
     *             ),

     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $businesses = Business::with('storeUser');

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
            return Response::success("Businesses retrieved successfully", $businesses);

        } catch (\Exception $e) {
            return Response::error("Error retrieving businesses", [], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/businesses/store",
     *     tags={"Businesses"},
     *     summary="Create a new business",
     *     description="Create a new business record",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "family", "business_name", "address", "mobile"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="John",
     *                 description="First name of the business owner"
     *             ),
     *             @OA\Property(
     *                 property="family",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Doe",
     *                 description="Last name of the business owner"
     *             ),
     *             @OA\Property(
     *                 property="business_name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="John's Electronics Store",
     *                 description="Name of the business"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 maxLength=500,
     *                 example="123 Main Street, City, State",
     *                 description="Business address"
     *             ),
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+1234567890",
     *                 description="Mobile phone number"
     *             ),
     *             @OA\Property(
     *                 property="tell",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+1987654321",
     *                 description="Telephone number (optional)"
     *             ),
     *             @OA\Property(
     *                 property="zipcode",
     *                 type="string",
     *                 maxLength=10,
     *                 example="12345",
     *                 description="ZIP/postal code (optional)"
     *             ),
     *             @OA\Property(
     *                 property="national_id",
     *                 type="string",
     *                 maxLength=20,
     *                 example="1234567890",
     *                 description="National ID number (optional)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Business created successfully"
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation failed"
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error creating business"
     *             ),

     *         )
     *     )
     * )
     */
    public function store(StoreBusinessRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['store_user_id'] = auth()->id();
            $business = Business::create($validated);
            $business->load('storeUser');
            return Response::success("Business created successfully", $business);

        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);

        } catch (\Exception $e) {
            return Response::error("Error creating business", $e->getMessage(), 500);

        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/businesses/{business}",
     *     tags={"Businesses"},
     *     summary="Update an existing business",
     *     description="Update a specific business record",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="business",
     *         in="path",
     *         description="Business ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "family", "business_name", "address", "mobile"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="John",
     *                 description="First name of the business owner"
     *             ),
     *             @OA\Property(
     *                 property="family",
     *                 type="string",
     *                 maxLength=255,
     *                 example="Doe",
     *                 description="Last name of the business owner"
     *             ),
     *             @OA\Property(
     *                 property="business_name",
     *                 type="string",
     *                 maxLength=255,
     *                 example="John's Electronics Store",
     *                 description="Name of the business"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 maxLength=500,
     *                 example="123 Main Street, City, State",
     *                 description="Business address"
     *             ),
     *             @OA\Property(
     *                 property="mobile",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+1234567890",
     *                 description="Mobile phone number"
     *             ),
     *             @OA\Property(
     *                 property="tell",
     *                 type="string",
     *                 maxLength=20,
     *                 example="+1987654321",
     *                 description="Telephone number (optional)"
     *             ),
     *             @OA\Property(
     *                 property="zipcode",
     *                 type="string",
     *                 maxLength=10,
     *                 example="12345",
     *                 description="ZIP/postal code (optional)"
     *             ),
     *             @OA\Property(
     *                 property="national_id",
     *                 type="string",
     *                 maxLength=20,
     *                 example="1234567890",
     *                 description="National ID number (optional)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Business updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Business updated successfully"
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation failed"
     *             ),

     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error creating business"
     *             ),

     *         )
     *     )
     * )
     */
    public function update(Request $request, Business $business): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'family' => 'required|string|max:255',
                'business_name' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'mobile' => 'required|string|max:20',
                'tell' => 'nullable|string|max:20',
                'zipcode' => 'nullable|string|max:10',
                'national_id' => 'nullable|string|max:20,' . $business->id,
            ]);

            $business->update($validated);
            $business->load('storeUser');
            return Response::success("Business updated successfully", $business);


        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);
        } catch (\Exception $e) {
            return Response::error("Error creating business", $e->getMessage(), 500);
        }
    }

    /**
     * Get businesses by specific user
     */
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
}
