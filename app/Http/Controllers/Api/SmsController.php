<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use HoomanMirghasemi\Sms\Facades\Sms;
use Illuminate\Support\Facades\Cache;

class SmsController extends Controller
{
    // 1. ارسال کد تایید برای شماره موبایل
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/'
        ]);

        $code = rand(100000, 999999);
        
        // ذخیره کد در کش به مدت 10 دقیقه
        Cache::put('sms_code_' . $request->phone, $code, 600);
        
        // ارسال پیامک
        try {
            Sms::to($request->phone)->message("کد تایید شما: $code")->send();
            return response()->json(['message' => 'کد تایید ارسال شد']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'خطا در ارسال پیامک'], 500);
        }
    }

    // 2. تأیید کد و فعال‌سازی شماره
    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^09[0-9]{9}$/',
            'code' => 'required|string|size:6'
        ]);

        $cachedCode = Cache::get('sms_code_' . $request->phone);
        
        if (!$cachedCode || $cachedCode != $request->code) {
            return response()->json(['message' => 'کد نامعتبر است'], 422);
        }
        
        // پاک کردن کد از کش بعد از تأیید
        Cache::forget('sms_code_' . $request->phone);
        
        return response()->json(['message' => 'شماره با موفقیت تأیید شد']);
    }
}