<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\Tables;
use App\Models\Menu;
use App\Models\OrderItems;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\ReservationsExport;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Step 1: Retrieve orders with basic details and join customers and tables
            $orders = DB::table('orders')
                ->join('customers', 'orders.customerId', '=', 'customers.customerId')
                ->join('tables', 'orders.tableNum', '=', 'tables.tableNum')
                ->select([
                    'orders.orderId',
                    'orders.customerId',
                    'tables.tableNum',
                    'orders.totalAmount',
                    DB::raw('DATE_FORMAT(orders.orderDate, "%d/%m/%Y %h:%i %p") as orderDate'),
                    'orders.status'
                ])
                ->orderBy('orders.orderDate', 'desc')
                ->get();

            // Step 2: Calculate orderQuantity for each order and add it to the result
            $orders->transform(function ($order) {
                // Retrieve the Order model instance by orderId
                $orderModel = Orders::find($order->orderId);

                // Call the calculateOrderQuantity function
                $order->orderQuantity = $orderModel ? $orderModel->calculateOrderQuantity() : 0;

                return $order;
            });

            // Step 3: Return the orders as JSON with the additional orderQuantity field
            return response()->json([
                'data' => $orders
            ]);
        }

        return view('orders.index');
    }

    public function generateOrderId()
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur'));
        $microTime = microtime(true);  // Get the current Unix timestamp with microseconds
        $microseconds = sprintf("%02d", ($microTime - floor($microTime)) * 100);  // Get microseconds as a 2-digit string
        
        // Format the orderId: DateTime + "v" + microseconds
        $orderId = $now->format('YmdHis') . $microseconds;
        
        return response()->json(['orderId' => $orderId]); // Return the order ID in the response
    }

    public function generateOrderDate()
{
    $now = new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur'));
    $formattedDate = $now->format('Y-m-d H:i:s'); 

    return response()->json(['orderDate' => $formattedDate]);
}


    public function checkCustomer(Request $request)
    {
            $customerId = strtoupper(trim($request->customerId));
            $exists = DB::table('customers')
                ->where('customerId', $customerId)
                ->exists();

            return response()->json([
                'exists' => $exists,
                'message' => $exists 
                    ? '<span style="color:green"> Customer ID exists.</span>'
                    : '<span style="color:red"> No record.</span>'
            ]);
        }

        
// Check if staff ID exists in the staff table
public function checkStaff(Request $request)
{
    $staffId = strtoupper(trim($request->staffId));
    $exists = DB::table('staffs')
        ->where('staffId', $staffId)
        ->exists();

    return response()->json([
        'exists' => $exists,
        'message' => $exists
            ? '<span style="color:green"> Staff ID exists.</span>'
            : '<span style="color:red"> No record.</span>'
    ]);
}
    public function getCustomerDetail(Request $request)
    {
        $customerId = $request->customerId;
        
        // Add some logging to debug
        \Log::info('Searching for customer: ' . $customerId);
        
        $customer = DB::table('customers')
            ->where('customerId', $customerId)
            ->first();
        
        // Add logging for the result
        \Log::info('Found customer:', ['customer' => $customer]);
        
        return response()->json([
            'name' => $customer ? $customer->name : '', 
            'success' => true
        ]);
    }

// Get staff name based on the staffId
public function getStaffDetail(Request $request)
{
    $staffId = $request->staffId;

    \Log::info('Searching for staff: ' . $staffId);

    // Find the staff details
    $staff = DB::table('staffs')
        ->where('staffId', $staffId)
        ->first();

        // Add logging for the result
        \Log::info('Found staff:', ['staff' => $staff]);

    return response()->json([
        'name' => $staff ? $staff->name : '',
        'success' => true
    ]);
}

    public function getTables()
    {
        $tables = Tables::all(); 
        return response()->json($tables);
    }

    public function getMenuItems()
    {
        $menu = Menu::all();
        return response()->json($menu);
    }

    public function store(Request $request)
{
    try {
        // Validate the request
        $validated = $request->validate([
            'orderId' => 'required|unique:orders,orderId',
            'customerId' => 'required',
            'tableNum' => 'required',
            'orderDate' => 'required|date',
            'totalAmount' => 'required|numeric',
            'status' => 'required',
            'orderItems.*.dishId' => 'required|exists:menu,dishId',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'orderItems.*.remark' => 'nullable|string'
        ]);

        // Insert into orders table
        DB::table('orders')->insert([
            'orderId' => $validated['orderId'],
            'customerId' => $validated['customerId'],
            'tableNum' => $validated['tableNum'],
            'orderDate' => $validated['orderDate'],
            'totalAmount' => $validated['totalAmount'],
            'status' => $validated['status'],
            'created_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
            'updated_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
        ]);

        // Store order items
        foreach ($validated['orderItems'] as $item) {
            $orderItemData = [
                'orderId' => $validated['orderId'],
                'dishId' => $item['dishId'],
                'servedBy' => null,
                'quantity' => $item['quantity'],
                'status' => 'Pending',
                'created_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
                'updated_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
            ];

            // Add the remark if it's not null or empty
            if (!empty($item['remark'])) {
                $orderItemData['remark'] = $item['remark'];
            }

            DB::table('orderItems')->insert($orderItemData);
        }

        return response()->json(['success' => true]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['success' => false, 'message' => $e->validator->errors()], 422);
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return response()->json(['success' => false, 'message' => 'Unable to create order: ' . $e->getMessage()], 500);
    }
}

public function details($orderId, Request $request)
{
    // Retrieve the filter values from the request
    $filterDishName = $request->input('filterDishName', ''); // Default to empty string
    $filterStatus = $request->input('filterStatus', ''); // Default to empty string
    $filterStaff = $request->input('filterStaff', ''); // Default to empty string

    // Retrieve the order items and join other necessary tables with filters applied
    $orderItems = DB::table('orderItems')
        ->join('orders', 'orderItems.orderId', '=', 'orders.orderId')
        ->join('menu', 'orderItems.dishId', '=', 'menu.dishId')
        ->leftJoin('staffs', 'staffs.staffId', '=', 'orderItems.servedBy')
        ->join('tables', 'orders.tableNum', '=', 'tables.tableNum')
        ->where('orders.orderId', '=', $orderId)
        // Apply filters if provided
        ->when($filterDishName, function ($query, $filterDishName) {
            return $query->where(function ($q) use ($filterDishName) {
                $q->where('menu.dishId', 'like', '%' . $filterDishName . '%')
                  ->orWhere('menu.dishName', 'like', '%' . $filterDishName . '%');
            });
        })
        ->when($filterStatus, function ($query, $filterStatus) {
            return $query->where('orderItems.status', 'like', '%' . $filterStatus . '%');
        })
        ->when($filterStaff, function ($query, $filterStaff) {
            return $query->where(function ($q) use ($filterStaff) {
                $q->where('staffs.staffId', 'like', '%' . $filterStaff . '%')
                  ->orWhere('staffs.name', 'like', '%' . $filterStaff . '%');
            });
        })
        ->select(
            'orders.orderId',
            'orders.customerId',
            DB::raw('DATE(orders.orderDate) as orderDate'),
            'orders.tableNum',
            'orders.totalAmount',
            'orderItems.dishId',
            'orderItems.orderItemId',
            'orders.status as orderStatus',
            'menu.dishName',
            'orderItems.quantity',
            'menu.price as unitPrice',
            DB::raw('TIME(orders.orderDate) as orderTime'),
            'staffs.name as servedBy',
            'orderItems.status as orderItemStatus',
            'orderItems.remark',
            'orderItems.created_at'
        )
        ->paginate(10);  // Use pagination here to limit results to 10 per page

    // If it's an AJAX request, return the order items as JSON in the format DataTables expects
    if ($request->ajax()) {
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $orderItems->total(),
            'recordsFiltered' => $orderItems->total(),
            'data' => $orderItems->items(),  // Get the current page of order items
        ]);
    }

    // Assuming the order is a single row, get the first record for display purposes
    $orderDetails = $orderItems->first();

    if (!$orderDetails) {
        return redirect()->route('orders.index')->with('error', 'Order not found');
    }

    // Return the order data to the view for non-AJAX request
    return view('orders.details', [
        'order' => $orderItems,  // Pass all order items to the view
        'orderDetails' => $orderDetails,  // Pass single order details to display
    ]);
}



public function edit($orderId)
{
    try {
        $order = DB::table('orders')
            ->where('orderId', $orderId)
            ->first(); // Get the first matching order 

        if ($order) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Order not found'
        ], 404);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching order: ' . $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $orderId)
    {
        try {
            // Validation to ensure all fields are present and correct
            $validated = $request->validate([
                'customerId' => 'required|exists:customers,customerId',
                'tableNum' => 'required|string',
                'orderDate' => 'required|date',
                'status' => 'required|string',
            ]);

            // Update order in the database
            DB::table('orders')
                ->where('orderId', $orderId)
                ->update([
                    'customerId' => $validated['customerId'],
                    'tableNum' => $validated['tableNum'],
                    'orderDate' => $validated['orderDate'],
                    'status' => $validated['status'],
                    'updated_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($orderId)
    {
        try {
            // Start a transaction
            DB::beginTransaction();

            // Delete order items with the respective orderId
            DB::table('orderitems')->where('orderId', $orderId)->delete();

            // Delete the order itself
            DB::table('orders')->where('orderId', $orderId)->delete();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order and associated items deleted successfully'
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order and items: ' . $e->getMessage()
            ], 500);
        }
    }

    public function itemStore(Request $request, $orderId)
{
    try {
        // Validate orderItems as an array of objects
        $validatedData = $request->validate([
            'orderItems' => 'required|array',
            'orderItems.*.dishId' => 'required|exists:menu,dishId',
            'orderItems.*.quantity' => 'required|integer|min:1',
            'orderItems.*.status' => 'required|string',
            'orderItems.*.servedBy' => 'nullable|string',
            'orderItems.*.remark' => 'nullable|string',
        ]);

        $newTotalAmount = 0;

        // Insert each validated item into the orderItems table
        foreach ($validatedData['orderItems'] as $item) {
            DB::table('orderItems')->insert([
                'orderId' => $orderId,
                'dishId' => $item['dishId'],
                'servedBy' => $item['servedBy'],
                'quantity' => $item['quantity'],
                'remark' => $item['remark'] ?? null,
                'status' => $item['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get the price of the dish
            $dishPrice = DB::table('menu')->where('dishId', $item['dishId'])->value('price');

            // Calculate the total for this item
            $newTotalAmount += $dishPrice * $item['quantity'];
        }

        // Update the order's totalAmount
        $order = DB::table('orders')->where('orderId', $orderId)->first();
        $updatedTotalAmount = $order->totalAmount + $newTotalAmount;

        DB::table('orders')->where('orderId', $orderId)->update([
            'totalAmount' => $updatedTotalAmount,
        ]);

        return response()->json(['success' => true]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => $e->validator->errors()
        ], 422);
    } catch (\Exception $e) {
        \Log::error($e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Unable to create order items: ' . $e->getMessage()
        ], 500);
    }
}


    public function itemEdit($orderId, $orderItemId)
    {
        try {
            // Retrieve the specific order item based on orderId and orderItemId
            $orderItem = DB::table('orderItems')
                ->join('menu', 'orderItems.dishId', '=', 'menu.dishId')
                ->leftJoin('staffs', 'staffs.staffId', '=', 'orderItems.servedBy') // Optional join if servedBy is nullable
                ->where('orderItems.orderId', $orderId)
                ->where('orderItems.orderItemId', $orderItemId)
                ->select('orderItems.*', 'staffs.name as staffName', 'menu.dishName', 'menu.price as unitPrice', 'staffs.staffId as servedBy')
                ->first();
        
            if ($orderItem) {
                return response()->json([
                    'success' => true,
                    'data' => $orderItem
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Order item not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order item: ' . $e->getMessage()
            ], 500);
        }
    }


    public function itemUpdate(Request $request, $orderId, $orderItemId) 
    {
        try {
            // Validate input data
            $validated = $request->validate([
                'dishId' => 'required|exists:menu,dishId',  
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string',
                'servedBy' => 'nullable|exists:staffs,staffId',
                'remark' => 'nullable|string|max:255',
            ]);
    
            // Retrieve the menu price for the updated dish
            $dish = DB::table('menu')->where('dishId', $validated['dishId'])->first();
            if (!$dish) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dish not found.'
                ], 404);
            }
    
            // Update the order item
            DB::table('orderitems')
                ->where('orderId', $orderId) 
                ->where('orderItemId', $orderItemId)
                ->update([
                    'dishId' => $validated['dishId'], 
                    'quantity' => $validated['quantity'],
                    'status' => $validated['status'],
                    'servedBy' => $validated['servedBy'],
                    'remark' => $validated['remark'],
                    'updated_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
                ]);
    
            // Recalculate the total amount for the order
            $orderItems = DB::table('orderitems')
                ->where('orderId', $orderId)
                ->get();
    
            $totalAmount = 0;
            foreach ($orderItems as $item) {
                $menuItem = DB::table('menu')->where('dishId', $item->dishId)->first();
                if ($menuItem) {
                    $totalAmount += $menuItem->price * $item->quantity; // Calculate total
                }
            }
    
            // Update the total amount of the order
            DB::table('orders')
                ->where('orderId', $orderId)
                ->update([
                    'totalAmount' => $totalAmount,
                    'updated_at' => new \DateTime('now', new \DateTimeZone('Asia/Kuala_Lumpur')),
                ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Order item updated successfully!',
                'totalAmount' => $totalAmount  // Return the updated totalAmount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order item: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function itemDestroy($orderId, $orderItemId)
    {
        try {
            DB::beginTransaction();

            // Step 1: Retrieve the order item to get the price and quantity
            $orderItem = DB::table('orderItems')
                ->where('orderId', $orderId)
                ->where('orderItemId', $orderItemId)
                ->first();

            // Step 2: If the item doesn't exist, return an error
            if (!$orderItem) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Order item not found.'
                ], 404);
            }

            // Get the price of the dish from the menu table
            $dishPrice = DB::table('menu')
                ->where('dishId', $orderItem->dishId)
                ->value('price');

            // Calculate the price to subtract (price * quantity)
            $priceToSubtract = $dishPrice * $orderItem->quantity;

            // Step 3: Delete the order item from the orderItems table
            DB::table('orderItems')
                ->where('orderId', $orderId)
                ->where('orderItemId', $orderItemId)
                ->delete();

            // Step 4: Get the current totalAmount for the order
            $currentTotalAmount = DB::table('orders')->where('orderId', $orderId)->value('totalAmount');

            // Step 5: Calculate the new totalAmount
            $newTotalAmount = $currentTotalAmount - $priceToSubtract;

            // Step 6: Update the order's totalAmount
            DB::table('orders')
                ->where('orderId', $orderId)
                ->update(['totalAmount' => $newTotalAmount]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order item deleted successfully and total amount updated.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function customerOrder()
    {
        $orders = Orders::select(
                'orders.orderId', 
                'orders.orderDate', 
                'orders.tableNum', 
                'orders.totalAmount', 
                'orders.status as orderStatus', 
                'tables.area', 
                'orders.orderDate'
            )
            ->join('tables', 'orders.tableNum', '=', 'tables.tableNum') 
            ->get();
    
        return response()->json($orders, 200, [], JSON_UNESCAPED_SLASHES);
    }
    
    public function customerOrderItems($orderId)
    {
        $orderItems = Orders::select(
            'orders.orderId', 
            'menu.dishName', 
            'orderItems.quantity', 
            'menu.price', 
            'menu.image', 
            \DB::raw('(orderItems.quantity * menu.price) as totalPrice'),
            'orderItems.status as orderItemStatus'
        )
        ->join('orderItems', 'orders.orderId', '=', 'orderItems.orderId')
        ->join('menu', 'orderItems.dishId', '=', 'menu.dishId')
        ->where('orders.orderId', $orderId)
        ->get();
        
    
        $totalAmount = $orderItems->sum('totalPrice');
    
        return response()->json([
            'orderId' => (string) $orderId, // Ensure it's returned as a string
            'orderItems' => $orderItems,
            'totalAmount' => $totalAmount
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    public function editOrder(Request $request, $orderId)
    {
        // Validate Request Data
        $validated = $request->validate([
            'tableNum' => 'required|string', // Validate table number format
        ]);

        try {
            // Find the Order by ID
            $order = Orders::findOrFail($orderId);
            
            // Update the table number
            $order->tableNum = $validated['tableNum'];
            $order->save();

            // Return success response
            return response()->json(['message' => 'Table number updated successfully'], 200);
        } catch (\Exception $e) {
            // Handle any errors and return failure response
            return response()->json(['error' => 'Failed to update order', 'details' => $e->getMessage()], 500);
        }
    }

    public function cancelOrder($orderId)
    {
        // Fetch the order by its ID
        $order = Orders::findOrFail($orderId);

        // Update the status to cancelled
        $order->status = 'Cancelled';
        $order->save();
    }
}