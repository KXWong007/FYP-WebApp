<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tables;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\ReservationsExport;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class TablesController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tables = DB::table('tables')
                ->select([
                    'tables.tableNum',
                    'tables.capacity',
                    'tables.status',
                    'tables.area',
                ])
                ->get();

            return response()->json([
                'data' => $tables
            ]);
        }

        return view('tables.table');
    }

    // Change this part to return the correct view

    public function store(Request $request) 
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'tableNum' => 'required|unique:tables,tableNum',
                'capacity' => 'required|integer|min:1',
                'status' => 'required|in:available,occupied,reserved',
                'area' => 'required|string|max:255'
            ]);

            // Create a new table record
            Tables::create([
                'tableNum' => $validated['tableNum'],
                'capacity' => $validated['capacity'],
                'status' => $validated['status'],
                'area' => $validated['area'],
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

    public function edit($tableNum)
    {
        try {
            // Fetch table details from the database
            $table = DB::table('tables')
                ->where('tableNum', $tableNum)
                ->first();

            if ($table) {
                return response()->json([
                    'success' => true,
                    'data' => $table,
                ]);
            }

            // Return a not found response if the table is not found
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);

        } catch (\Exception $e) {
            // Return an error response if fetching the table fails
            return response()->json([
                'success' => false,
                'message' => 'Error fetching table: ' . $e->getMessage(),
            ], 500);
        }
    }

   public function update(Request $request, $tableNum)
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'capacity' => 'required|integer|min:1',  // Ensure capacity is a positive integer
                'area' => 'required|string|max:255',     // Area should be a string
                'status' => 'required|string|in:available,occupied',  // Assuming status is either 'available' or 'occupied'
            ]);

            // Find the table by tableNum using the correct model
            $table = Tables::where('tableNum', $tableNum)->first();  // Use Tables model here

            // If the table is not found
            if (!$table) {
                return response()->json(['success' => false, 'message' => 'Table not found.'], 404);
            }

            // Update the table fields
            $table->capacity = $validated['capacity'];
            $table->area = $validated['area'];
            $table->status = $validated['status'];

            // Save the updated table
            $table->save();

            // Return a success response
            return response()->json(['success' => true, 'message' => 'Table updated successfully']);
        } catch (\Exception $e) {
            // Return a generic error message for unexpected exceptions
            return response()->json(['success' => false, 'message' => 'Error updating table: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($tableNum)
    {
        try {
            // Attempt to delete the table from the 'tables' table
            $deleted = DB::table('tables')
                ->where('tableNum', $tableNum)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Table deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Table not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting table: ' . $e->getMessage()
            ], 500);
        }
    }

   public function generateQRCode(Request $request, $tableNum)
    {
        try {
            // Find the table in the database
            $table = Tables::where('tableNum', $tableNum)->first();

            if (!$table) {
                return response()->json(['success' => false, 'message' => 'Table not found.'], 404);
            }

            // Generate a new random string
            $randomString = Str::random(10); // Generates a 10-character random string

            // Save the random string in the `qrcode` column
            $table->qrcode = $randomString;
            $table->save();

            // Return success with the random string
            return response()->json([
                'success' => true,
                'qrcode' => $randomString,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error generating QR code.'], 500);
        }
    }

   public function showQRCode($tableNum)
    {
        return $qrCode = QrCode::size(200)->generate('Table Number: ' . $tableNum);
    }

    public function validateTableNumber(Request $request, $tableNum)
    {
        try {
            // Check if the table number exists in the database
            $table = Tables::where('tableNum', $tableNum)->first();

            if ($table) {
                return response()->json(['exists' => true], 200); // Table exists
            } else {
                return response()->json(['exists' => false], 404); // Table does not exist
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to validate table number', 'details' => $e->getMessage()], 500); 
        }
    }
}
