<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\database;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login()
    {
        return view('auth/login');
    }

    public function loginAction(Request $request)
{
    // Validate input fields
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    // Retrieve user based on the provided email
    $user = DB::table('staffs')->where('email', $request->email)->first();

    if (!$user) {
        return back()->withErrors(['email' => 'Invalid email address.']);
    }

    // Verify password
    if (!Hash::check($request->password, $user->password)) {
        return back()->withErrors(['password' => 'Invalid password.']);
    }

    // Log the user in (create session, redirect)
    // Store user information in session
    session([
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'staffType' => $user->staffType,
            'staffId' => $user->staffId
        ]
    ]);
    // Redirect based on user type
    switch ($user->staffType) {
        case 'F&B Manager':
            return redirect('/dashboard');
        case 'Dining Area Staff':
            return redirect('/dine-side');
        case 'Kitchen Area Staff':
            return redirect('/kitchen');
        case 'Inventory Manager':
            return redirect('/inventory');
        default:
            return redirect('/home'); // Default redirect if no specific role is matched
    }
}
 
public function logout(Request $request)
{
    // Use Laravel's built-in function to log the user out.
    Auth::guard('web')->logout();

    // Clear all session data
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // Redirect to login page
    return redirect('/login');
}

 
    public function profile()
    {
        return view('userprofile');
    }

    public function customerLogin(Request $request)
    {
        try {
            // Change from 'users' to 'customers' table
            $customer = DB::table('customers')
                ->where('customerId', $request->customerId)
                ->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Assuming the password is stored in plain text for now
            // In production, you should use Hash::check if passwords are hashed
            if (!Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'customer' => $customer
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
