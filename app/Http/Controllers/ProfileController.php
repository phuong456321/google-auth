<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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

    public function uploadImage(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $user = Auth::user(); // Lấy người dùng đã đăng nhập
    
        if ($user) { // Kiểm tra xem người dùng có tồn tại không
            // Lưu file ảnh mới
            $path = $request->file('profile_image');
            $imageData = file_get_contents($path);
            $base64 = base64_encode($imageData);
            // Cập nhật đường dẫn ảnh vào cơ sở dữ liệu
            $user->profile_image = $base64;
            $user->save(); // Lưu lại thông tin
    
            return redirect()->back()->with('success', 'Image uploaded successfully.');
        } else {
            return redirect()->back()->with('error', 'User not found.');
        }
    }
}
