<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\OrderItems;
use App\Models\Orders;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $upcomingReservations = Reservation::where('rstatus', 'confirm')
            ->where('reservationDate', '>=', now())
            ->whereRaw('eventType REGEXP "[a-zA-Z]+"')
            ->orderBy('reservationDate', 'asc')
            ->take(5)
            ->get()
            ->map(function ($reservation) {
                $reservation->type_icon = $this->getTypeIcon($reservation->eventType);
                return $reservation;
            });

        $cookingCount = OrderItems::where('status', 'Cooking')->count();
        $pendingCount = Orders::where('status', 'Pending')->count();
        $readytoserveCount = OrderItems::where('status', 'Ready to Serve')->count();

        $preparingOrders = OrderItems::select(
                'orderitems.*',
                'orders.tableNum',
                'menu.dishName'
            )
            ->join('orders', 'orderitems.orderId', '=', 'orders.orderId')
            ->join('menu', 'orderitems.dishId', '=', 'menu.dishId')
            ->where('orderitems.status', 'Cooking')
            ->get()
            ->map(function ($orderItem) {
                return (object)[
                    'order_id' => $orderItem->orderId,
                    'table_number' => $orderItem->tableNum,
                    'item_name' => $orderItem->dishName,
                    'quantity' => $orderItem->quantity,
                    'started_at' => $orderItem->updated_at,
                ];
            });

        $readyToServeOrders = OrderItems::select(
            'orderitems.*',
            'orders.tableNum',
            'menu.dishName'
        )
        ->join('orders', 'orderitems.orderId', '=', 'orders.orderId')
        ->join('menu', 'orderitems.dishId', '=', 'menu.dishId')
        ->where('orderitems.status', 'Ready to Serve')
        ->get()
        ->map(function ($orderItem) {
            return (object)[
                'order_id' => $orderItem->orderId,
                'table_number' => $orderItem->tableNum,
                'item_name' => $orderItem->dishName,
                'quantity' => $orderItem->quantity,
                'started_at' => $orderItem->updated_at,
            ];
        });

        $pendingOrders = Orders::select(
            'orders.orderId',
            'orders.customerId',
            'orders.tableNum',
            'orders.totalAmount',
            'orders.created_at',
            'orders.status'
        )
        ->where('orders.status', 'Pending')
        ->get()
        ->map(function ($order) {
            return (object)[
                'order_id' => $order->orderId,
                'customerId' => $order->customerId,
                'tableNum' => $order->tableNum,
                'totalAmount' => $order->totalAmount,
                'created_at' => $order->created_at,
            ];
        });

        return view('welcome', compact('pendingCount', 'cookingCount', 'readytoserveCount', 'upcomingReservations', 'preparingOrders', 'readyToServeOrders', 'pendingOrders'));
    }

    public function getUpcomingReservations()
    {
        try {
            $reservations = Reservation::join('customers', 'reservations.customerId', '=', 'customers.customerId')
                ->where('rstatus', 'confirm')
                ->where('reservationDate', '>=', now())
                ->orderBy('reservationDate', 'asc')
                ->select(
                    'reservations.*',
                    'customers.Name',
                    'customers.phoneNum'
                )
                ->get()
                ->map(function ($reservation) {
                    return [
                        'id' => $reservation->reservationId,
                        'type' => $reservation->eventType,
                        'rarea' => $reservation->rarea,
                        'rdate' => $reservation->reservationDate,
                        'status' => $reservation->rstatus,
                        'icon' => $this->getTypeIcon($reservation->eventType) ?? 'None',
                        'pax' => $reservation->pax,
                        'customerId' => $reservation->customerId,
                        'customerName' => $reservation->Name,
                        'phoneNum' => $reservation->phoneNum,
                        'orderId' => $reservation->orderId ?? 'None',
                        'paymentId' => $reservation->paymentId ?? 'None',
                        'remark' => $reservation->remark ?? 'No remarks',
                    ];
                });

            return response()->json($reservations);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getOrderDetails($orderId)
    {
        try {
            $orderItems = OrderItems::select(
                'orderitems.quantity',
                'orderitems.status',
                'menu.dishName',
                'menu.price'
            )
            ->join('menu', 'orderitems.dishId', '=', 'menu.dishId')
            ->where('orderitems.orderId', $orderId)
            ->get();

            return response()->json($orderItems);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getTypeIcon($type)
    {
        return match (strtolower(trim($type))) {
            'wedding' => 'fa-ring',
            'birthday' => 'fa-birthday-cake',
            'party' => 'fa-glass-cheers',
            'meeting' => 'fa-briefcase',
            'business' => 'fa-briefcase',
            default => 'fa-calendar'
        };
    }
}