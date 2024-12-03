<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer; // Customer or Customers??

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        // Step 1: Validate the request data
        $request->validate([
            'id' => 'required|string',
            'password' => 'required',
        ]);

        // Step 2: Find the customer by ID
        $customer = Customers::where('id', $request->id)->first();

        // Step 3: Verify the credentials
        if (!$customer || !\Hash::check($request->password, $customer->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Step 4: Generate a token for the authenticated customer
        $token = $customer->createToken('CustomerAuthToken')->plainTextToken;

        // Step 5: Send back the token and customer details
        return response()->json([
            'token' => $token,
            'customer' => $customer,
        ], 200);
    }


public function forgotPassword(Request $request)
{
    // Step 1: Validate that the ID is provided
    $request->validate([
        'id' => 'required|string',
    ]);

    // Step 2: Check if the customer exists
    $customer = Customers::where('id', $request->id)->first();

    if (!$customer) {
        return response()->json(['message' => 'Customer not found'], 404);
    }

    // Step 3: Generate a temporary password or a reset token
    $resetToken = \Str::random(10); // Random token
    // You would typically save the token to the database or send it via email/SMS.

    return response()->json(['message' => 'Password reset link has been sent', 'reset_token' => $resetToken]);
    }
    public function updatePassword(Request $request)
    {
    // Step 1: Validate the request
    $request->validate([
        'new_password' => 'required|min:8|confirmed', // The confirmed field expects 'new_password_confirmation' in the request
    ]);

    // Step 2: Get the currently authenticated user (assumes they are logged in)
    $customer = Auth::user();

    // Step 3: Update the customer's password
    $customer->password = bcrypt($request->new_password);
    $customer->save();

    // Step 4: Return a success response
    return response()->json(['message' => 'Password updated successfully'], 200);
}
}
