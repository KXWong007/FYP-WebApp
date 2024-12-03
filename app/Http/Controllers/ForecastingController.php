<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Forecasting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockAlert;

class ForecastingController extends Controller
{
    /**
     * Display a list of items available for forecasting.
     */
    public function index(Request $request)
    {
        // Get the selected duration (default is 30 days for a month)
        $duration = $request->input('duration', 30);

        // Fetch all inventory items with their associated forecasting data
        $items = Inventory::with('forecasting')->get(); // Eager load forecasting data

        // Loop through items and calculate total requirement based on selected duration
        foreach ($items as $item) {
            $totalUsage = $item->forecasting->sum('dailyUsage');
            $daysCount = $item->forecasting->count();
            $averageDailyUsage = $daysCount > 0 ? $totalUsage / $daysCount : 0;

            // Calculate total requirement for selected duration
            $item->totalRequirement = $averageDailyUsage * $duration;
        }

        // Pass items and duration to the view
        return view('forecasting.index', ['items' => $items, 'duration' => $duration]);
    }

    /**
     * Store daily usage input by the user for a specific item.
     *
     * @param Request $request
     * @param string $inventoryId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $inventoryId)
    {
        // Retrieve the item from the Inventory table
        $inventoryItem = Inventory::find($inventoryId);

        if (!$inventoryItem) {
            return redirect()->back()->with('error', 'Item not found');
        }

        // Create new forecasting entry
        $forecast = new Forecasting();

        // Set the necessary fields, including 'itemName'
        $forecast->inventoryId = $inventoryId;
        $forecast->dailyUsage = $request->dailyUsage;
        $forecast->date = $request->date;
        $forecast->measurementUnit = $inventoryItem->measurementUnit;
        $forecast->itemName = $inventoryItem->itemName;

        // Save the forecast
        $forecast->save();

        // Reduce the inventory quantity based on the daily usage
        $inventoryItem->quantity -= $request->dailyUsage;

        // Ensure the quantity doesn't go below 0
        if ($inventoryItem->quantity < 0) {
            $inventoryItem->quantity = 0;
        }

        // Check if the updated inventory is below the minimum threshold and handle alert
        $this->handleLowStockAlert($inventoryItem);

        // Save the updated inventory item
        $inventoryItem->save();

        return redirect()->back()->with('success', 'Forecast data added successfully');
    }

    /**
     * Update the specified forecasting data.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Find the forecasting entry
        $forecast = Forecasting::find($id);
        if (!$forecast) {
            return redirect()->back()->with('error', 'Forecast not found');
        }

        // Retrieve the associated inventory item
        $inventoryItem = Inventory::find($forecast->inventoryId);
        if (!$inventoryItem) {
            return redirect()->back()->with('error', 'Inventory item not found');
        }

        // Calculate the difference between old and new usage
        $oldUsage = $forecast->dailyUsage;
        $newUsage = $request->dailyUsage;
        $usageDifference = $newUsage - $oldUsage;

        // Update the forecasting data
        $forecast->dailyUsage = $newUsage;
        $forecast->date = $request->date;
        $forecast->save();

        // Update the inventory based on the usage difference
        $inventoryItem->quantity += $oldUsage;  // Return the old usage to inventory
        $inventoryItem->quantity -= $newUsage;  // Subtract the new usage from inventory

        // Ensure the quantity doesn't go below 0
        if ($inventoryItem->quantity < 0) {
            $inventoryItem->quantity = 0;
        }

        // Check if the updated inventory is below the minimum threshold and handle alert
        $this->handleLowStockAlert($inventoryItem);

        // Save the updated inventory item
        $inventoryItem->save();

        return redirect()->back()->with('success', 'Forecast and inventory updated successfully');
    }

    /**
     * Handle low stock alerts: store in session or remove if stock is above the minimum.
     *
     * @param Inventory $inventoryItem
     */
    private function handleLowStockAlert(Inventory $inventoryItem)
    {
        if ($inventoryItem->quantity <= $inventoryItem->minimum) {
            // Create a formatted low stock alert message
            $alertMessage = 'Warning: Stock for ' . $inventoryItem->itemName . ' is below the minimum threshold! ' . 
                            'Current stock: ' . $inventoryItem->quantity . ' ' . $inventoryItem->measurementUnit . 
                            '. Minimum stock required: ' . $inventoryItem->minimum . ' ' . $inventoryItem->measurementUnit;

            // Store low stock alert message in session if not already set
            if (!session()->has('low_stock_alerts') || !in_array($alertMessage, session('low_stock_alerts'))) {
                session()->push('low_stock_alerts', $alertMessage);
            }
        } else {
            // If the inventory exceeds the minimum, remove the low stock alert if it exists
            $lowStockAlerts = session('low_stock_alerts', []);
            $lowStockAlerts = array_filter($lowStockAlerts, function ($alert) use ($inventoryItem) {
                return strpos($alert, $inventoryItem->itemName) === false;  // Remove alert for the item
            });

            // Update session with filtered alerts (removes the cleared low stock alert)
            session()->put('low_stock_alerts', array_values($lowStockAlerts));
        }
    }

    /**
     * Remove the specified forecasting data.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Find and delete the forecasting record
        $forecast = Forecasting::findOrFail($id);
        $forecast->delete();

        // Redirect back with success message
        return redirect()->route('forecast.index')->with('success', 'Forecast deleted successfully.');
    }

    /**
     * Forecast for the next 7 or 30 days, depending on selected duration.
     *
     * @param Request $request
     * @param string $inventoryId
     * @return \Illuminate\View\View
     */
    public function forecast(Request $request, $inventoryId)
    {
        // Get the selected duration (default is 30 days for a month)
        $duration = $request->input('duration', 30);

        // Retrieve the last $duration days of usage data for the specific inventory item
        $forecastData = Forecasting::where('inventoryId', $inventoryId)
                                    ->orderBy('date', 'desc')
                                    ->take($duration)
                                    ->get();

        // Check if there is enough data to forecast
        if ($forecastData->isEmpty()) {
            return redirect()->route('forecast.index')
                             ->with('error', 'Not enough data available for forecasting.');
        }

        // Calculate the average daily usage based on historical data
        $averageDailyUsage = $forecastData->avg('dailyUsage');

        // Get the number of days for the forecast duration
        $daysInForecast = $duration;

        // Generate a forecast for the selected duration
        $nextForecast = [];
        $startDate = Carbon::now();
        for ($i = 1; $i <= $daysInForecast; $i++) {
            $nextDate = $startDate->copy()->addDays($i);
            $nextForecast[] = [
                'date' => $nextDate->toDateString(),
                'predictedUsage' => $averageDailyUsage,
            ];
        }

        // Calculate the total stock needed for the forecast period
        $totalStockNeeded = $averageDailyUsage * $daysInForecast;

        // Retrieve the item details from the Inventory table
        $itemDetails = Inventory::findOrFail($inventoryId);

        // Return data to the forecast view
        return view('forecasting.forecast', [
            'forecastData' => $nextForecast,
            'totalStockNeeded' => $totalStockNeeded,
            'itemDetails' => $itemDetails,
            'duration' => $duration
        ]);
    }

    /**
     * Get all items with low stock levels.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLowStockItems(): JsonResponse
    {
        $lowStockItems = Inventory::whereColumn('quantity', '<=', 'minimum')->get();
        return response()->json([
            'success' => true,
            'data' => $lowStockItems,
        ]);
    }
}
