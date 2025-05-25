<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Alumni;
use App\Models\AlumniCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AlumniImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Cache;

class UploadAlumniController extends Controller
{
    public function index()
    {
        $programmes = Alumni::distinct()->pluck('programme')->sort();
        $departments = Alumni::distinct()->pluck('department')->sort();
        $faculties = Alumni::distinct()->pluck('faculty')->sort();
        $years = Alumni::distinct()->pluck('year_of_graduation')->sort()->reverse();
        $categories = AlumniCategory::active()->get();

        $user = auth()->user();
        $isAdmin = $user->hasRole('administrator');
        $isARO = $user->hasRole('alumni-relations-officer');

        // Determine which layout to use
        if ($isAdmin) {
            return view('admin.upload-alumni.index', compact('programmes', 'departments', 'faculties', 'years', 'categories'));
        } elseif ($isARO) {
            return view('aro.upload-alumni.index', compact('programmes', 'departments', 'faculties', 'years', 'categories'));
        }

        // Fallback to admin view if role is not recognized
        return view('admin.upload-alumni.index', compact('programmes', 'departments', 'faculties', 'years', 'categories'));
    }

    public function showRetrieveCredentials()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('administrator');
        $isARO = $user->hasRole('alumni-relations-officer');

        // Determine which view to use based on role
        if ($isAdmin) {
            return view('admin.retrieve-credentials');
        } elseif ($isARO) {
            return view('aro.retrieve-credentials');
        }

        // Fallback to admin view if role is not recognized
        return view('admin.retrieve-credentials');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240', // 10MB max
            ]);

            $import = new AlumniImport;
            
            // Import the file
            Excel::import($import, $request->file('file'));

            // Get any errors that occurred during import
            $errors = $import->getErrors();
            
            if (!empty($errors)) {
                $errorMessage = "Import completed with some issues:\n";
                foreach ($errors as $error) {
                    $errorMessage .= "- " . $error . "\n";
                }
                
                return redirect()->back()
                    ->with('warning', $errorMessage)
                    ->with('success', 'Alumni records were imported, but some records had issues.')
                    ->with('importId', $import->getImportId());
            }

            return redirect()->back()
                ->with('success', 'Alumni records imported successfully.')
                ->with('importId', $import->getImportId());

        } catch (\Exception $e) {
            Log::error('Alumni Import Error: ' . $e->getMessage());
            
            // Check if the error is about array conversion
            if (str_contains($e->getMessage(), 'Array to string conversion')) {
                return redirect()->back()
                    ->with('success', 'Alumni records were imported successfully, but there was an issue with the error reporting.')
                    ->with('importId', $import->getImportId());
            }
            
            return redirect()->back()
                ->with('error', 'Error uploading alumni records: ' . $e->getMessage());
        }
    }

    public function getImportProgress(Request $request)
    {
        $importId = $request->input('importId');
        if (!$importId) {
            return response()->json(['error' => 'Import ID is required'], 400);
        }

        $progress = Cache::get("import_progress_{$importId}");
        if (!$progress) {
            return response()->json(['error' => 'Import progress not found'], 404);
        }

        return response()->json($progress);
    }

    public function search(Request $request)
    {
        $query = Alumni::with(['user', 'category']);

        if ($request->filled('programme')) {
            $query->where('programme', $request->programme);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        if ($request->filled('year_of_graduation')) {
            $query->where('year_of_graduation', $request->year_of_graduation);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $alumni = $query->paginate(10);

        $programmes = Alumni::distinct()->pluck('programme')->sort();
        $departments = Alumni::distinct()->pluck('department')->sort();
        $faculties = Alumni::distinct()->pluck('faculty')->sort();
        $years = Alumni::distinct()->pluck('year_of_graduation')->sort()->reverse();
        $categories = AlumniCategory::active()->get();

        return view('admin.upload-alumni.search', compact('alumni', 'programmes', 'departments', 'faculties', 'years', 'categories'));
    }

    public function getFilterOptions()
    {
        $programmes = Alumni::distinct()->pluck('programme');
        $departments = Alumni::distinct()->pluck('department');
        $faculties = Alumni::distinct()->pluck('faculty');
        $years = Alumni::distinct()->pluck('year_of_graduation');
        $categories = AlumniCategory::active()->get();

        return response()->json([
            'programmes' => $programmes,
            'departments' => $departments,
            'faculties' => $faculties,
            'years' => $years,
            'categories' => $categories,
        ]);
    }

    public function getCredentials(Request $request)
    {
        $request->validate([
            'matriculation_id' => 'required|string'
        ]);

        $alumni = Alumni::with('category')->where('matric_number', $request->matriculation_id)->first();

        if (!$alumni) {
            return redirect()->back()->with('error', 'No alumni found with this matriculation number.');
        }

        $user = $alumni->user;
        $tempEmail = strtolower(str_replace('/', '', $alumni->matric_number)) . '@alumni.fulafia.edu.ng';
        
        // Debug information
        Log::info('Debugging Alumni Credentials:');
        Log::info('User ID: ' . $user->id);
        Log::info('User Email: ' . $user->email);
        Log::info('Has Alumni Role: ' . ($user->hasRole('alumni') ? 'Yes' : 'No'));
        Log::info('Alumni Category: ' . ($alumni->category ? $alumni->category->name : 'Not Set'));

        $data = [
            'alumni' => $alumni,
            'tempEmail' => $tempEmail,
            'name' => $user->name,
            'matriculation_id' => $alumni->matric_number,
            'category' => $alumni->category
        ];

        // Determine which view to use based on role
        $currentUser = Auth::user();
        if ($currentUser->hasRole('administrator')) {
            return view('admin.upload-alumni.credentials', $data);
        } elseif ($currentUser->hasRole('alumni-relations-officer')) {
            return view('aro.upload-alumni.credentials', $data);
        }

        // Fallback to admin view if role is not recognized
        return view('admin.upload-alumni.credentials', $data);
    }

    public function updateAlumniEmail(Request $request)
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['new_email'][0] ?? 'Validation failed.'
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
        $request->validate([
            'matriculation_id' => 'required|string'
        ]);

        $alumni = Alumni::where('matric_number', $request->matriculation_id)->first();

        if (!$alumni) {
            return redirect()->back()->with('error', 'No alumni found with this matriculation number.');
        }

        $user = $alumni->user;
        $tempEmail = strtolower(str_replace('/', '', $alumni->matric_number)) . '@alumni.fulafia.edu.ng';

        // Debug information
        Log::info('Resending Alumni Credentials:');
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

        return redirect()->back()->with('success', 'Password reset link has been resent successfully to ' . $user->email);
    }
}
