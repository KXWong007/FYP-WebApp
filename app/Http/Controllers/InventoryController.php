<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use DataTables;
use Maatwebsite\Excel\Facades\Excel; // If using maatwebsite/excel for import/export
use App\Exports\InventoryExport;
use App\Imports\InventoryImport;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class InventoryController extends Controller
{
    // Display the inventory management page
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $inventory = DB::table('inventory')
                ->select([
                    'inventory.inventoryId',
                    'inventory.itemName',
                    'inventory.quantity',
                    'inventory.minimum',
                    'inventory.maximum',
                    'inventory.unitPrice',
                    'inventory.measurementUnit',
                ])
                ->get();

            return response()->json([
                'data' => $inventory
            ]);
        }

        return view('inventory.inventory');
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'inventoryId' => 'required|string|max:50|unique:inventory,inventoryId',
                'itemName' => 'required|string|max:50',
                'quantity' => 'required|integer|min:0',
                'minimum' => 'required|integer|min:0',
                'maximum' => 'required|integer|min:0',
                'unitPrice' => 'required|numeric|min:0',
                'measurementUnit' => 'required|string|max:10',
            ]);

            // Create a new table record
            Inventory::create([
                'inventoryId' => $validated['inventoryId'],
                'itemName' => $validated['itemName'],
                'quantity' => $validated['quantity'],
                'minimum' => $validated['minimum'],
                'maximum' => $validated['maximum'],
                'unitPrice' => $validated['unitPrice'],
                'measurementUnit' => $validated['measurementUnit'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Return a JSON response with success message
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Return a JSON response with an error message
            return response()->json(['success' => false], 500);
        }
    }

    public function edit($inventoryId)
    {
        try {
            // Fetch item details from the database
            $inventory = DB::table('inventory')
                ->where('inventoryId', $inventoryId)
                ->first();

            if ($inventory) {
                return response()->json([
                    'success' => true,
                    'data' => $inventory,
                ]);
            }

            // Return a not found response if the item is not found
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found',
            ], 404);

        } catch (\Exception $e) {
            // Return an error response if fetching the item fails
            return response()->json([
                'success' => false,
                'message' => 'Error fetching item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $inventoryId)
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'itemName' => 'required|string|max:50',
                'quantity' => 'required|integer|min:0',
                'minimum' => 'required|integer|min:0',
                'maximum' => 'required|integer|min:0',
                'unitPrice' => 'required|numeric|min:0',
                'measurementUnit' => 'required|string|max:10',
            ]);

            $inventory = Inventory::where('inventoryId', $inventoryId)->first(); 
            
            if (!$inventory) {
                return response()->json(['success' => false, 'message' => 'Inventory item not found.'], 404);
            }

            // Update the fields
            $inventory->itemName = $validated['itemName'];
            $inventory->quantity = $validated['quantity'];
            $inventory->minimum = $validated['minimum'];
            $inventory->maximum = $validated['maximum'];
            $inventory->unitPrice = $validated['unitPrice'];
            $inventory->measurementUnit = $validated['measurementUnit'];

            // Save the updated table
            $inventory->save();

            // Return a success response
            return response()->json(['success' => true, 'message' => 'Inventory item updated successfully']);
        } catch (\Exception $e) {
            // Return a generic error message for unexpected exceptions
            return response()->json(['success' => false, 'message' => 'Error updating inventory item: ' . $e->getMessage()], 500);
        }
    }

   public function destroy($inventoryId)
    {
        try {
            // Attempt to delete the item from the 'inventory' table
            $deleted = DB::table('inventory')
                ->where('inventoryId', $inventoryId)  // Correct column name and variable
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export($type = 'xlsx') 
    {
        // Fetch inventory data from the database
        $inventory = DB::table('inventory')
            ->select(
                'inventoryId as Inventory ID',
                'itemName as Item Name',
                'quantity as Quantity',
                'minimum as Minimum Quantity',
                'maximum as Maximum Quantity',
                'unitPrice as Unit Price',
                'measurementUnit as Measurement Unit',
                'created_at as Created At',
                'updated_at as Updated At'
            )
            ->get();

        switch ($type) {
            case 'csv':
                return (new \Rap2hpoutre\FastExcel\FastExcel($inventory))->download('inventory.csv');

            case 'pdf':
                $pdf = Pdf::loadView('exports.inventory-pdf', ['inventory' => $inventory]);
                return $pdf->download('inventory.pdf');

            default: // xlsx
                return (new \Rap2hpoutre\FastExcel\FastExcel($inventory))->download('inventory.xlsx');
        }
    }

    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('csvFile')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded',
                ], 400);
            }

            $file = $request->file('csvFile');
            $rows = array_map('str_getcsv', file($file->getPathname()));
            $header = array_shift($rows); // Remove the header row

            $successCount = 0;
            $errorRows = [];

            foreach ($rows as $index => $row) {
                try {
                    // Validate required fields
                    if (!isset($row[0], $row[1], $row[2], $row[3], $row[4], $row[5])) {
                        $errorRows[] = "Row " . ($index + 2) . ": Missing required fields";
                        continue;
                    }

                    // Validate numeric values
                    if (!is_numeric($row[2]) || !is_numeric($row[3]) || !is_numeric($row[4]) || !is_numeric($row[5])) {
                        $errorRows[] = "Row " . ($index + 2) . ": Quantity, Minimum, Maximum, and Unit Price must be numeric";
                        continue;
                    }

                    // Insert inventory item
                    DB::table('inventory')->insert([
                        'inventoryId' => $row[0],
                        'itemName' => $row[1],
                        'quantity' => $row[2],
                        'minimum' => $row[3],
                        'maximum' => $row[4],
                        'unitPrice' => $row[5],
                        'measurementUnit' => $row[6] ?? null, // Optional field
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorRows[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            $message = "$successCount inventory items imported successfully.";
            if (count($errorRows) > 0) {
                $message .= " Errors: " . implode(", ", $errorRows);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing data: ' . $e->getMessage(),
            ], 500);
        }
    }

}