<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staffs;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\StaffExport;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('staffs');
            
            // Get total count before pagination
            $totalRecords = $query->count();
            
            // Handle search
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('staffId', 'like', "%{$searchValue}%")
                      ->orWhere('staffType', 'like', "%{$searchValue}%")
                      ->orWhere('name', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%")
                      ->orWhere('status', 'like', "%{$searchValue}%");
                });
            }
            
            // Get filtered count
            $filteredRecords = $query->count();
            
            // Handle pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $query->skip($start)->take($length);

            // Handle ordering
            if ($request->has('order')) {
                $columns = ['staffId', 'staffType', 'name', 'email', 'phone', 'status'];
                $column = $columns[$request->order[0]['column']];
                $direction = $request->order[0]['dir'];
                $query->orderBy($column, $direction);
            }

            $staff = $query->get();

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $staff
            ]);
        }

        return view('auth.staff');
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'staffId' => 'required|unique:staffs,staffId',
                'staffType' => 'required',
                'name' => 'required',
                'email' => 'required|email|unique:staffs,email',
                'gender' => 'required',
                'religion' => 'nullable',
                'race' => 'nullable',
                'nric' => 'required|unique:staffs,nric',
                'dateOfBirth' => 'required|date',
                'phone' => 'required|unique:staffs,phone',
                'address' => 'required',
                'status' => 'required',
                'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Handle file upload
            $profilePicturePath = null;
            if ($request->hasFile('profilePicture')) {
                $file = $request->file('profilePicture');
                $fileName = time() . '_' . $validated['staffId'] . '.' . $file->getClientOriginalExtension();
                $profilePicturePath = $file->storeAs('public/profile_pictures', $fileName);
                // Convert storage path to public URL
                $profilePicturePath = str_replace('public/', 'storage/', $profilePicturePath);
            }

            // Create the staff
            $staff = Staffs::create([
                'staffId' => $validated['staffId'],
                'staffType' => $validated['staffType'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'religion' => $validated['religion'],
                'race' => $validated['race'],
                'nric' => $validated['nric'],
                'dateOfBirth' => $validated['dateOfBirth'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
                'profilePicture' => $profilePicturePath,
                'password' => bcrypt('123456'), // Default password
                'created_at' => now()
            ]);
                
            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating staff: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'staffType' => 'required',
                'name' => 'required',
                'email' => 'required|email|unique:staffs,email,' . $id . ',staffId',
                'gender' => 'required',
                'religion' => 'nullable',
                'race' => 'nullable',
                'nric' => 'required|unique:staffs,nric,' . $id . ',staffId',
                'dateOfBirth' => 'required|date',
                'phone' => 'required|unique:staffs,phone,' . $id . ',staffId',
                'address' => 'required',
                'status' => 'required',
                'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);
           
            $staff = Staffs::findOrFail($id);
            
            // Handle file upload if a new file is provided
            if ($request->hasFile('profilePicture')) {
                // Delete old profile picture if it exists
                if ($staff->profilePicture && file_exists(public_path($staff->profilePicture))) {
                    unlink(public_path($staff->profilePicture));
                }
                
                $file = $request->file('profilePicture');
                $fileName = time() . '_' . $id . '.' . $file->getClientOriginalExtension();
                $profilePicturePath = $file->storeAs('public/profile_pictures', $fileName);
                $validated['profilePicture'] = str_replace('public/', 'storage/', $profilePicturePath);
            }

       // Update staff
       $staff->update($validated);

       return response()->json([
           'success' => true,
           'message' => 'Staff updated successfully'
       ]);

   } catch (\Exception $e) {
       \Log::error('Error updating staff: ' . $e->getMessage());
       return response()->json([
           'success' => false,
           'message' => 'Error updating staff: ' . $e->getMessage()
       ], 500);
   }
}

public function export($type = 'xlsx')
{
    try {
        $staff = Staffs::select(
            'staffId as Staff ID',
            'staffType as Staff Type',
            'name as Staff Name',
            'email as Email',
            'gender as Gender',
            'religion as Religion',
            'race as Race',
            'nric as NRIC',
            'dateOfBirth as Date of Birth',
            'phone as Phone Number',
            'address as Address',
            'status as Status'
        )->get();

        $data = $staff->map(function ($staff) {
            return (array) $staff->getAttributes();
        })->toArray();

        switch ($type) {
            case 'csv':
                $filename = 'staff_' . date('Y-m-d') . '.csv';
                return (new FastExcel(collect($data)))->download($filename);

            case 'pdf':
                $pdf = PDF::loadView('exports.staff-pdf', ['staff' => $staff]);
                return $pdf->download('staff_' . date('Y-m-d') . '.pdf');

            default: // xlsx
                $filename = 'staff_' . date('Y-m-d') . '.xlsx';
                return (new FastExcel(collect($data)))->download($filename);
        }
    } catch (\Exception $e) {
        \Log::error('Export error: ' . $e->getMessage());
        return back()->with('error', 'Error exporting data: ' . $e->getMessage());
    }
}

public function destroy($id)
    {
        try {
            DB::table('staffs')
                ->where('staffId', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting staff: ' . $e->getMessage()
            ], 500);
        }
    }
   
    public function template()
    {
        $headers = [
            'Staff ID',
            'Staff Type',
            'Name',
            'Email',
            'Gender',
            'Religion',
            'Race',
            'NRIC',
            'Date of Birth (YYYY-MM-DD or DD/MM/YYYY)',
            'Phone Number',
            'Address',
            'Status'
        ];

        $f = fopen('php://memory', 'r+');
        fputcsv($f, $headers);

        rewind($f);
        $content = stream_get_contents($f);
        fclose($f);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="staff_template.csv"');
    }

    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('csvFile')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded'
                ], 400);
            }

            $file = $request->file('csvFile');
            $rows = array_map('str_getcsv', file($file->getPathname()));
            $header = array_shift($rows);

            $successCount = 0;
            $errorRows = [];

            foreach ($rows as $index => $row) {
                try {
                    // Handle date format
                    $dateOfBirth = null;
                    if (!empty($row[8])) {
                        // Try YYYY-MM-DD format first
                        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $row[8])) {
                            $dateOfBirth = $row[8];
                        } 
                        // Try DD/MM/YYYY format
                        else {
                            $date = \DateTime::createFromFormat('d/m/Y', $row[8]);
                            if ($date) {
                                $dateOfBirth = $date->format('Y-m-d');
                            } else {
                                throw new \Exception('Invalid date format. Please use either YYYY-MM-DD or DD/MM/YYYY format.');
                            }
                        }
                    }

                    Staffs::create([
                        'staffId' => $row[0],
                        'staffType' => $row[1],
                        'name' => $row[2],
                        'email' => $row[3],
                        'gender' => $row[4],
                        'religion' => $row[5] ?? null,
                        'race' => $row[6] ?? null,
                        'nric' => $row[7],
                        'dateOfBirth' => $dateOfBirth,
                        'phone' => $row[9],
                        'address' => $row[10],
                        'status' => $row[11],
                        'password' => bcrypt('123456'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorRows[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    \Log::error('Import error at row ' . ($index + 2) . ': ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "$successCount staff imported successfully." . 
                            (count($errorRows) > 0 ? " Errors: " . implode(", ", $errorRows) : "")
            ]);

        } catch (\Exception $e) {
            \Log::error('CSV import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkStaffId(Request $request)
    {
        try {
            $staffId = $request->input('staffId');
            $exists = Staffs::where('staffId', $staffId)->exists();
            
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 
                    '<span class="text-danger">Staff ID already exists</span>' : 
                    '<span class="text-success">Staff ID is available</span>'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error checking Staff ID: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkNric(Request $request)
    {
        try {
            $nric = $request->input('nric');
            $exists = Staffs::where('nric', $nric)->exists();
            
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 
                    '<span class="text-danger">NRIC already exists</span>' : 
                    '<span class="text-success">NRIC is available</span>'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error checking NRIC: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStaff($id)
    {
        try {
            $staff = Staffs::findOrFail($id);
            return response()->json(['staff' => $staff]);
        } catch (\Exception $e) {
            \Log::error('Error fetching staff: ' . $e->getMessage());
            return response()->json(['error' => 'Staff not found'], 404);
        }
    }
}
