<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Drivers\Gd\Driver;
use App\Http\Requests\ProfileUpdateRequest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    public function avatar(Request $request){
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:3000',
        ]);
    
        $user = $request->user();
    
        // Delete old avatar if it exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    
        // Get the uploaded file
        $uploadedFile = $request->file('avatar');
    
        // Resize the image to 120x120
        $manager = new ImageManager(new Driver());
        $image = $manager->read($uploadedFile);
        $imgData = $image->cover(120, 120)->toJpeg(); // Resizes and crops to 120x120
        
    
        // Generate a unique filename
        $filename = 'avatars/' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
    
        // Store the resized image
        Storage::disk('public')->put($filename, $imgData);
    
        // Update the user's avatar path in the database
        $user->update(['avatar' => $filename]);
    
        return redirect()->back()->with('success', 'Avatar uploaded and successfully!');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
