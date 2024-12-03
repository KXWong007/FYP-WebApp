<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KitchenController extends Controller
{
    public function index()
    {
        $orders = DB::table('orderitems')
            ->select(
                'orderitems.orderItemId',
                'orderitems.orderId',
                'orderitems.dishId',
                'orderitems.quantity',
                'orderitems.status',
                'orderitems.start_time',
                'orderitems.finishcook_time',
                'orderitems.staffid',
                'orderitems.remark',
                'menu.dishName',
                'orders.tableNum',
                'orders.created_at',
                'orders.status as orderStatus',
                'staffs.name as staffName',
                'customers.name as customerName',
                DB::raw('TIMESTAMPDIFF(SECOND, orders.created_at, NOW()) as elapsed_seconds')
            )
            ->join('menu', 'orderitems.dishId', '=', 'menu.dishId')
            ->join('orders', 'orderitems.orderId', '=', 'orders.orderId')
            ->leftJoin('staffs', 'orderitems.staffid', '=', 'staffs.staffId')
            ->leftJoin('customers', 'orders.customerId', '=', 'customers.customerId')
            ->orderBy('orders.created_at', 'asc')
            ->get()
            ->groupBy('orderId');

        return view('kitchen.index', compact('orders'));
    }

    public function getDishStatus($id)
    {
        try {
            $dish = DB::table('orderitems')
                ->select(
                    'orderitems.orderItemId',
                    'orderitems.status',
                    'orderitems.start_time',
                    'orderitems.finishcook_time',
                    'orderitems.staffid',
                    'staffs.name',
                    'menu.dishName'
                )
                ->leftJoin('staffs', 'orderitems.staffid', '=', 'staffs.staffId')
                ->leftJoin('menu', 'orderitems.dishId', '=', 'menu.dishId')
                ->where('orderItemId', $id)
                ->first();

            if (!$dish) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dish not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'status' => $dish->status,
                'start_time' => $dish->start_time ? Carbon::parse($dish->start_time)->format('Y-m-d H:i:s') : null,
                'finishcook_time' => $dish->finishcook_time ? Carbon::parse($dish->finishcook_time)->format('Y-m-d H:i:s') : null,
                'staffid' => $dish->staffid,
                'name' => $dish->name,
                'dishName' => $dish->dishName
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getDishStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dish status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function startCooking(Request $request)
    {
        try {
            $now = Carbon::now();
            
            DB::table('orderitems')
                ->where('orderItemId', $request->dishId)
                ->update([
                    'start_time' => $now,
                    'staffid' => $request->staffId,
                    'status' => 'Cooking',
                    'updated_at' => $now
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Started cooking successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error starting cooking'
            ], 500);
        }
    }

    public function finishCooking(Request $request)
    {
        try {
            $now = Carbon::now();
            
            // Get the order ID for this item
            $orderItem = DB::table('orderitems')
                ->where('orderItemId', $request->dishId)
                ->first();

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item not found'
                ], 404);
            }

            // Update just this specific item
            DB::table('orderitems')
                ->where('orderItemId', $request->dishId)
                ->update([
                    'finishcook_time' => $now,
                    'status' => 'Ready to Serve',
                    'staffid' => $request->staffId,
                    'updated_at' => $now
                ]);

            // Check the status of all items in this order
            $orderItems = DB::table('orderitems')
                ->where('orderId', $orderItem->orderId)
                ->get();

            $allItemsFinished = $orderItems->every(function($item) {
                return in_array($item->status, ['Ready to Serve', 'Cancelled']);
            });

            // If all items are either Ready to Serve or Cancelled, update the order status
            if ($allItemsFinished) {
                DB::table('orders')
                    ->where('orderId', $orderItem->orderId)
                    ->update([
                        'status' => 'Completed',
                        'updated_at' => $now
                    ]);
            } else {
                // Just update the timestamp if not all items are finished
                DB::table('orders')
                    ->where('orderId', $orderItem->orderId)
                    ->update([
                        'updated_at' => $now
                    ]);
            }

            return response()->json([
                'success' => true,
                'finishcook_time' => $now->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in finishCooking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error finishing cooking'
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $now = Carbon::now();
            
            // Get the order ID for this item
            $orderItem = DB::table('orderitems')
                ->where('orderItemId', $request->orderItemId)
                ->first();

            if ($request->status === 'Ready to Serve') {
                // First, mark this specific item as finished cooking
                DB::table('orderitems')
                    ->where('orderItemId', $request->orderItemId)
                    ->update([
                        'status' => 'Ready to Serve',
                        'finishcook_time' => $now,
                        'staffid' => $request->staffId,
                        'updated_at' => $now
                    ]);

                // Check if ALL items in the order are Ready to Serve or Cancelled
                $pendingItems = DB::table('orderitems')
                    ->where('orderId', $orderItem->orderId)
                    ->whereNotIn('status', ['Ready to Serve', 'Cancelled'])
                    ->count();

                // Only update order status if all items are ready or cancelled
                if ($pendingItems === 0) {
                    DB::table('orders')
                        ->where('orderId', $orderItem->orderId)
                        ->update([
                            'status' => 'Completed',
                            'updated_at' => $now
                        ]);
                }

            } else if ($request->status === 'Cooking') {
                // Update all pending items in the order to Cooking status
                DB::table('orderitems')
                    ->where('orderId', $orderItem->orderId)
                    ->where('status', 'Pending')
                    ->update([
                        'status' => 'Cooking',
                        'start_time' => $now,
                        'staffid' => $request->staffId,
                        'updated_at' => $now
                    ]);

                // Update the order's updated_at timestamp
                DB::table('orders')
                    ->where('orderId', $orderItem->orderId)
                    ->update([
                        'updated_at' => $now
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    public function cancelOrder(Request $request)
    {
        try {
            // Update order status
            DB::table('orders')
                ->where('orderId', $request->orderId)
                ->update([
                    'status' => 'Cancelled'
                ]);

            // Update all order items status
            DB::table('orderitems')
                ->where('orderId', $request->orderId)
                ->update([
                    'status' => 'Cancelled'
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order'
            ], 500);
        }
    }

    public function cancelItem(Request $request)
    {
        try {
            // Get the order item first
            $orderItem = DB::table('orderitems')
                ->where('orderItemId', $request->orderItemId)
                ->first();

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order item not found'
                ], 404);
            }

            // Update the item status
            DB::table('orderitems')
                ->where('orderItemId', $request->orderItemId)
                ->update([
                    'status' => 'Cancelled',
                    'updated_at' => now()
                ]);

            // Check the status of all remaining items in the order
            $orderItems = DB::table('orderitems')
                ->where('orderId', $orderItem->orderId)
                ->get();

            // Determine the appropriate order status based on remaining items
            $allCancelled = $orderItems->every(function($item) {
                return $item->status === 'Cancelled';
            });

            $hasReadyItems = $orderItems->contains(function($item) {
                return $item->status === 'Ready to Serve';
            });

            $hasCookingItems = $orderItems->contains(function($item) {
                return $item->status === 'Cooking';
            });

            // Update order status based on item statuses
            $newOrderStatus = 'Pending'; // Default status
            if ($allCancelled) {
                $newOrderStatus = 'Cancelled';
            } elseif ($hasReadyItems && !$hasCookingItems) {
                $newOrderStatus = 'Completed';
            } elseif ($hasCookingItems) {
                $newOrderStatus = 'Cooking';
            }

            // Update the order status
            DB::table('orders')
                ->where('orderId', $orderItem->orderId)
                ->update([
                    'status' => $newOrderStatus,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Item cancelled successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in cancelItem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling item'
            ], 500);
        }
    }

    public function checkStaff($staffId)
    {
        try {
            $staff = DB::table('staffs')
                ->where('staffId', $staffId)
                ->first();

            if ($staff) {
                return response()->json([
                    'success' => true,
                    'name' => $staff->name
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking staff'
            ], 500);
        }
    }
}