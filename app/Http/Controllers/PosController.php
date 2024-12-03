<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosController extends Controller
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
                'orderitems.servedtime',
                'orderitems.servedBy',
                'orderitems.remark',
                'menu.dishName',
                'orders.tableNum',
                'orders.created_at',
                'orders.status as orderStatus',
                'staffs.name as staffName',
                'customers.name as customerName'
            )
            ->join('menu', 'orderitems.dishId', '=', 'menu.dishId')
            ->join('orders', 'orderitems.orderId', '=', 'orders.orderId')
            ->leftJoin('staffs', 'orderitems.staffid', '=', 'staffs.staffId')
            ->leftJoin('customers', 'orders.customerId', '=', 'customers.customerId')
            ->whereIn('orderitems.status', ['Ready to Serve', 'Served', 'Cancelled'])
            ->orderBy('orders.created_at', 'desc')
            ->get()
            ->groupBy('orderId');

        return view('pos.index', compact('orders'));
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
                    'orderitems.servedtime',
                    'orderitems.servedBy as staffid',
                    'staffs.name',
                    'menu.dishName'
                )
                ->leftJoin('staffs', 'orderitems.servedBy', '=', 'staffs.staffId')
                ->leftJoin('menu', 'orderitems.dishId', '=', 'menu.dishId')
                ->where('orderItemId', $id)
                ->first();

            return response()->json([
                'success' => true,
                'status' => $dish->status,
                'finishcook_time' => $dish->finishcook_time ? Carbon::parse($dish->finishcook_time)->format('Y-m-d H:i:s') : null,
                'servedtime' => $dish->servedtime ? Carbon::parse($dish->servedtime)->format('Y-m-d H:i:s') : null,
                'staffid' => $dish->staffid,
                'name' => $dish->name,
                'dishName' => $dish->dishName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dish status'
            ], 500);
        }
    }

    public function serveDish(Request $request)
    {
        try {
            $now = Carbon::now();
            
            DB::table('orderitems')
                ->where('orderItemId', $request->dishId)
                ->update([
                    'servedtime' => $now,
                    'servedBy' => $request->staffId,
                    'status' => 'Served',
                    'updated_at' => $now
                ]);

            return response()->json([
                'success' => true,
                'serve_time' => $now->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error serving dish'
            ], 500);
        }
    }
}