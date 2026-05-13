<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Kavenegar\KavenegarApi;

class AuthController extends Controller
{
    // 1. ارسال کد تایید برای ثبت‌نام
    public function sendOtp(Request $request)
    {
        // فقط validation
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/'
        ]);
    
        $code = rand(100000, 999999);
        Cache::put('otp_' . $request->phone, $code, 300);
        
        // فقط لاگ بزن (بدون ارسال واقعی)
        \Log::info("OTP for {$request->phone}: {$code}");
        
        return response()->json(['message' => 'کد تایید ارسال شد (تست)']);
    }

    // 2. ثبت‌نام با کد تایید
    public function registerWithOtp(Request $request)
    {
        \Log::info('Register OTP request:', $request->all());
        
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'code' => 'required|string|size:6'
        ]);
    
        $cachedCode = Cache::get('otp_' . $request->phone);
        
        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['message' => 'کد نامعتبر است'], 422);
        }
        
        // پیدا کردن یا ایجاد کاربر
        $user = User::where('phone', $request->phone)->first();
        
        if (!$user) {
            $tempPassword = Hash::make(uniqid());
            
            $user = User::create([
                'name' => 'کاربر جدید',
                'phone' => $request->phone,
                'email' => $request->phone . '@temp.com',
                'password' => $tempPassword,
                'phone_verified_at' => now(),
            ]);
            
            Profile::create([
                'user_id' => $user->id,
                'full_name' => null,
                'role' => 'user',
            ]);
        }
        
        Cache::forget('otp_' . $request->phone);
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return response()->json([
            'token' => $token,
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    // 3. ورود با شماره و رمز عبور
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'password' => 'required|string'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'شماره یا رمز عبور اشتباه است'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    // 4. تکمیل اطلاعات پروفایل (بعد از ثبت‌نام)
    public function completeProfile(Request $request)
    {
        $user = $request->user();
        $profile = Profile::where('user_id', $user->id)->first();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'email' => 'nullable|email',
            'age' => 'nullable|integer',
            'education' => 'nullable|string',
            'economic_status' => 'nullable|string',
            'number_of_children' => 'nullable|integer',
            'children_ages' => 'nullable|integer',
        ]);
        
        // بروزرسانی نام کاربر و رمز عبور
        $user->name = $request->full_name;
        $user->email = $request->email ?? $user->email;
        $user->password = Hash::make($request->password);
        $user->save();
        
        // بروزرسانی پروفایل
        $profile->update([
            'full_name' => $request->full_name,
            'age' => $request->age,
            'education' => $request->education,
            'economic_status' => $request->economic_status,
            'number_of_children' => $request->number_of_children,
            'children_ages' => $request->children_ages,
        ]);
        
        return response()->json([
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    // 5. دریافت اطلاعات کاربر جاری
    public function user(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    // 6. خروج از سیستم
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'خروج موفقیت آمیز بود']);
    }
    
    // 7. فراموشی رمز - ارسال کد
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/|exists:users,phone'
        ]);

        $code = rand(100000, 999999);
        Cache::put('reset_' . $request->phone, $code, 300);
        
        Log::info('Reset code for ' . $request->phone . ': ' . $code);
        
        return response()->json(['message' => 'کد بازنشانی ارسال شد']);
    }
    
    // 8. بازنشانی رمز عبور
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $cachedCode = Cache::get('reset_' . $request->phone);
        
        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['message' => 'کد نامعتبر است'], 422);
        }
        
        $user = User::where('phone', $request->phone)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        
        Cache::forget('reset_' . $request->phone);
        
        return response()->json(['message' => 'رمز عبور با موفقیت تغییر کرد']);
    }
}