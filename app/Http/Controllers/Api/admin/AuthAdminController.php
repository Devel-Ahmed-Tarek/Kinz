<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Mail\email_verified_otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthAdminController extends Controller
{

    public function login(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [

            'login' => 'required|string|min:8',
            'password' => 'required|string|min:8',

        ], [], [

            'login' => 'البريد الاكترني او اسم المستخدم ',
            'password' => ' كلمه السر  ',

        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'هناك رسائل تحقق', $validator->messages()->all());
        }

        // البحث عن المستخدم بناءً على البريد الإلكتروني أو اسم المستخدم
        $user = User::where('email', $request->login)
            ->orWhere('userName', $request->login)
            ->first();

        if (!$user) {
            return resourceApi::sendResponse(422, 'لايوجد اسم مستخدم او بريد بهذا لاسم ', []);
        }
        if ($user->type == 'user') {
            return resourceApi::sendResponse(422, 'لايوجد صلاحيه لدخول ', []);
        }
        // تابع تنفيذ الكود إذا تم العثور على المستخدم

        if (is_null($user->email_verified_at)) {
            for ($i = 0; $i < 5; $i++) {
                $randomNumbers[] = rand(1, 9); // يمكنك تغيير 100 لتحديد النطاق الأقصى للأرقام
            }

            $randomNumbersString = implode('', $randomNumbers);

            $user->update([
                'email_verified_otp' => $randomNumbersString,
            ]);

            Mail::to($user->email)->send(new email_verified_otp($randomNumbersString));
            return resourceApi::sendResponse(403, 'Email not verified', []);
        }

        // الحصول على بيانات التسجيل
        $login = $request->input('login');
        $password = $request->input('password');

        // التحقق من وجود المستخدم
        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('authToken')->plainTextToken;
            $data['id'] = $user->id;
            $data['token'] = $token;
            $data['email'] = $user->email;
            $data['username'] = $user->username;
            $data['name'] = $user->name;
            $data['country_code'] = $user->country_code;
            $data['phone_number'] = $user->phone_number;
            $data['type'] = $user->type;

            return resourceApi::sendResponse(200, ['en' => 'Login Successfuly', 'ar' => 'تم تسجيل الدخول بنجاح'], $data);
        } else {
            return resourceApi::sendResponse(200, ['en' => 'Login UnSuccessfuly', 'ar' => 'لم تم تسجيل الدخول بنجاح  '], []);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ], [], [
            'current_password' => 'كلمه السر القديمه',
            'new_password' => '  كلمه السر الجديده',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'هناك رسائل تحقق', $validator->messages()->all());

        }

        $user = Auth::user();

        // التحقق من أن كلمة المرور الحالية صحيحة
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // return response()->json(['message' => 'Password changed successfully'], 200);
        return resourceApi::sendResponse(200, 'Password changed successfully', []);

    }

}
