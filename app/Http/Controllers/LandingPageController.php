<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function searchCredentials(Request $request)
    {
        $request->validate([
            'matriculation_id' => 'required|string'
        ]);

        $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

        if (!$alumni) {
            return redirect()->back()->with('error', 'No alumni found with this matriculation number.');
        }

        // 2025+ graduates must go through login process
        if ($alumni->year_of_graduation >= 2025) {
            return redirect()->route('login')
                ->with('info', 'Please login to access your alumni account.');
        }

        // For 2023 and earlier, and 2024 graduates
        $user = $alumni->user;
        $tempEmail = strtolower(str_replace('/', '', $alumni->matric_number)) . '@alumni.fulafia.edu.ng';

        // Add a message for 2024 graduates about fee exemption
        $message = null;
        if ($alumni->year_of_graduation == 2024) {
            $message = 'As a 2024 graduate, you are exempted from all fees but must complete your bio data.';
        }

        return view('landing.credentials', [
            'alumni' => $alumni,
            'tempEmail' => $tempEmail,
            'name' => $user->name,
            'matriculation_id' => $alumni->matric_number,
            'category' => $alumni->category,
            'message' => $message
        ]);
    }

    public function updateEmail(Request $request)
    {
        try {
            $request->validate([
                'matriculation_id' => 'required|string',
                'new_email' => 'required|email|unique:users,email'
            ]);

            $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

            if (!$alumni) {
                return response()->json([
                    'success' => false,
                    'message' => 'No alumni found with this matriculation number.'
                ]);
            }

            $user = $alumni->user;
            $tempEmail = strtolower(str_replace('/', '', $alumni->matric_number)) . '@alumni.fulafia.edu.ng';

            // Update the user's email
            $user->email = $request->new_email;
            $user->save();

            // Generate password reset token
            $token = Password::createToken($user);

            // Send welcome email with password reset link
            Mail::send('emails.alumni-welcome', [
                'name' => $user->name,
                'email' => $tempEmail,
                'resetLink' => url('/reset-password/' . $token . '?email=' . urlencode($request->new_email)),
                'matriculation_id' => $alumni->matric_number
            ], function($message) use ($request) {
                $message->to($request->new_email)
                    ->subject('Welcome to FuLafia Alumni Portal - Set Your Password');
            });

            return response()->json([
                'success' => true,
                'message' => 'Email updated and password reset link sent successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating alumni email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the email. Please try again.'
            ]);
        }
    }

    public function resendCredentials(Request $request)
    {
        try {
            $request->validate([
                'matriculation_id' => 'required|string'
            ]);

            $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

            if (!$alumni) {
                return response()->json([
                    'success' => false,
                    'message' => 'No alumni found with this matriculation number.'
                ]);
            }

            // Check graduation year
            if ($alumni->year_of_graduation >= 2025) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to access your alumni account.'
                ]);
            }

            $user = $alumni->user;
            $tempEmail = strtolower(str_replace('/', '', $alumni->matric_number)) . '@alumni.fulafia.edu.ng';

            // Debug information
            Log::info('Resending Alumni Credentials (Landing Page):');
            Log::info('User ID: ' . $user->id);
            Log::info('Email: ' . $user->email);
            Log::info('Has Alumni Role: ' . ($user->hasRole('alumni') ? 'Yes' : 'No'));

            // Generate password reset token
            $token = Password::createToken($user);

            // Send welcome email with password reset link
            Mail::send('emails.alumni-welcome', [
                'name' => $user->name,
                'email' => $tempEmail,
                'resetLink' => url('/reset-password/' . $token . '?email=' . urlencode($user->email)),
                'matriculation_id' => $alumni->matric_number
            ], function($message) use ($user) {
                $message->to($user->email)
                    ->subject('Welcome to FuLafia Alumni Portal - Set Your Password');
            });

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been resent successfully to ' . $user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Error resending alumni credentials: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resending the credentials. Please try again.'
            ]);
        }
    }
} 