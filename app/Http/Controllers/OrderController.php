<?php

namespace App\Http\Controllers;

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

            $orders = $orders->get();
            return Response::success("orders retrieved successfully", $orders);

        } catch (\Exception $e) {
            return Response::error("Error retrieving orders", [], 500);
        }
    }


    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $validated['store_user_id'] = auth()->id();
            $orders = Orders::create($validated);
            $orders->load('storeUser');
            $orders->load('serviceUsers');
            $orders->load('businessId');
            return Response::success("Order created successfully", $orders->only(['id','businessId','serviceUsers', 'storeUser', 'services', 'description', 'status', 'full_price', 'fee_price', 'profit_price', 'discount','created_at']));

        } catch (ValidationException $e) {
            return Response::error("Validation failed", $e->getMessage(), 422);

        } catch (\Exception $e) {
            return Response::error("Error creating Order", $e->getMessage(), 500);

        }
    }

    public function update(StoreOrderRequest $request, Orders $orders): JsonResponse
    {
        try {
            $validated = $request->validated();

            $orders->update($validated);
            $orders->load('storeUser');
            $orders->load('serviceUsers');
            $orders->load('businessId');
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

}
