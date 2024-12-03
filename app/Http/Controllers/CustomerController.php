<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use OpenSpout\Common\Entity\Style\Style;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('customers');
            
            // Get total count before pagination
            $totalRecords = $query->count();
            
            // Handle search
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('customerId', 'like', "%{$searchValue}%")
                      ->orWhere('customerType', 'like', "%{$searchValue}%")
                      ->orWhere('name', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('phoneNum', 'like', "%{$searchValue}%")
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
                $columns = ['customerId', 'customerType', 'name', 'email', 'phoneNum', 'status'];
                $column = $columns[$request->order[0]['column']];
                $direction = $request->order[0]['dir'];
                $query->orderBy($column, $direction);
            }
            
            $customers = $query->get();
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $customers
            ]);
        }

        return view('auth.customers');
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'customerId' => 'required|unique:customers,customerId',
                'customerType' => 'required',
                'name' => 'required',
                'email' => 'required|email|unique:customers,email',
                'gender' => 'required',
                'religion' => 'nullable',
                'race' => 'nullable',
                'nric' => 'required|unique:customers,nric',
                'dateOfBirth' => 'required|date',
                'phoneNum' => 'required|unique:customers,phoneNum',
                'address' => 'required',
                'status' => 'required',
                'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            // Handle file upload
            $profilePicturePath = null;
            if ($request->hasFile('profilePicture')) {
                $file = $request->file('profilePicture');
                $fileName = time() . '_' . $validated['customerId'] . '.' . $file->getClientOriginalExtension();
                $profilePicturePath = $file->storeAs('public/profile_pictures', $fileName);
                // Convert storage path to public URL
                $profilePicturePath = str_replace('public/', 'storage/', $profilePicturePath);
            }

            // Create the customer
            $customer = Customers::create([
                'customerId' => $validated['customerId'],
                'customerType' => $validated['customerType'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'religion' => $validated['religion'],
                'race' => $validated['race'],
                'nric' => $validated['nric'],
                'dateOfBirth' => $validated['dateOfBirth'],
                'phoneNum' => $validated['phoneNum'],
                'address' => $validated['address'],
                'status' => $validated['status'],
                'profilePicture' => $profilePicturePath,
                'password' => bcrypt('12345678'), // Default password
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'customerType' => 'required',
                'name' => 'required',
                'email' => 'required|email|unique:customers,email,' . $id . ',customerId',
                'gender' => 'required',
                'religion' => 'nullable',
                'race' => 'nullable',
                'nric' => 'required|unique:customers,nric,' . $id . ',customerId',
                'dateOfBirth' => 'required|date',
                'phoneNum' => 'required|unique:customers,phoneNum,' . $id . ',customerId',
                'address' => 'required',
                'status' => 'required',
                'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            $customer = Customers::findOrFail($id);
            
            // Handle file upload if a new file is provided
            if ($request->hasFile('profilePicture')) {
                // Delete old profile picture if it exists
                if ($customer->profilePicture && file_exists(public_path($customer->profilePicture))) {
                    unlink(public_path($customer->profilePicture));
                }
                
                $file = $request->file('profilePicture');
                $fileName = time() . '_' . $id . '.' . $file->getClientOriginalExtension();
                $profilePicturePath = $file->storeAs('public/profile_pictures', $fileName);
                $validated['profilePicture'] = str_replace('public/', 'storage/', $profilePicturePath);
            }

            // Update customer
            $customer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export($type = 'xlsx')
    {
        try {
            $customers = Customers::select(
                'customerId as Customer ID',
                'customerType as Customer Type',
                'name as Customer Name',
                'email as Email',
                'gender as Gender',
                'religion as Religion',
                'race as Race',
                'nric as NRIC',
                'dateOfBirth as Date of Birth',
                'phoneNum as Phone Number',
                'address as Address',
                'status as Status'
            )->get();

            $data = $customers->map(function ($customer) {
                return (array) $customer->getAttributes();
            })->toArray();

            switch ($type) {
                case 'csv':
                    $filename = 'customers_' . date('Y-m-d') . '.csv';
                    return (new FastExcel(collect($data)))->download($filename);

                case 'pdf':
                    $pdf = PDF::loadView('exports.customers-pdf', ['customers' => $customers]);
                    return $pdf->download('customers_' . date('Y-m-d') . '.pdf');

                default: // xlsx
                    $filename = 'customers_' . date('Y-m-d') . '.xlsx';
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
            DB::table('customers')
                ->where('customerId', $id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting customer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function template()
    {
        $headers = [
            'Customer ID',
            'Customer Type',
            'Name',
            'Email',
            'Gender',
            'Religion',
            'Race',
            'NRIC',
            'Date of Birth (YYYY-MM-DD or DD/MM/YYYY)',
            'Phone Number',
            'Address',
            'Status',
            'Profile Picture'
        ];

        $f = fopen('php://memory', 'r+');
        fputcsv($f, $headers);

        rewind($f);
        $content = stream_get_contents($f);
        fclose($f);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="customers_template.csv"');
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

                    Customers::create([
                        'customerId' => $row[0],
                        'customerType' => $row[1],
                        'name' => $row[2],
                        'email' => $row[3],
                        'gender' => $row[4],
                        'religion' => $row[5] ?? null,
                        'race' => $row[6] ?? null,
                        'nric' => $row[7],
                        'dateOfBirth' => $dateOfBirth,
                        'phoneNum' => $row[9],
                        'address' => $row[10],
                        'status' => $row[11],
                        'profilePicture' => $row[12],
                        'password' => bcrypt('12345678'),
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
                'message' => "$successCount customers imported successfully." . 
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

    public function checkCustomerId(Request $request)
    {
        try {
            $customerId = $request->input('customerId');
            $exists = Customers::where('customerId', $customerId)->exists();
            
            return response()->json([
                'exists' => $exists,
                'message' => $exists ? 
                    '<span class="text-danger">Customer ID already exists</span>' : 
                    '<span class="text-success">Customer ID is available</span>'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error checking Customer ID: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkNric(Request $request)
    {
        try {
            $nric = $request->input('nric');
            $exists = Customers::where('nric', $nric)->exists();
            
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

    public function edit($id)
    {
        try {
            $customer = Customers::where('customerId', $id)->firstOrFail();
            return response()->json(['customer' => $customer]);
        } catch (\Exception $e) {
            \Log::error('Error fetching customer: ' . $e->getMessage());
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }

    public function getCustomer($id)
    {
        try {
            $customer = Customers::where('customerId', $id)->firstOrFail();
            return response()->json(['customer' => $customer]);
        } catch (\Exception $e) {
            \Log::error('Error fetching customer: ' . $e->getMessage());
            return response()->json(['error' => 'Customer not found'], 404);
        }
    }
}
