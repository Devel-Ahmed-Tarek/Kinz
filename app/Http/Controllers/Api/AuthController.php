<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Mail\email_verified_otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'country_code' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
        ], [], [
            'username' => __('messages.username'),
            'name' => __('messages.name'),
            'country_code' => __('messages.country_code'),
            'email' => __('messages.email'),
            'password' => __('messages.password'),
            'phone_number' => __('messages.phone_number'),
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $img = 'https://ui-avatars.com/api/?name=' . $request->name;
        $user = User::create([
            'username' => $request->username,
            'name' => $request->name,
            'country_code' => $request->country_code,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            "profile_image" => $img,

        ]);

        // توليد OTP
        // $otp = rand(1000, 9999);
        $otp = 1111;

        $user->update([
            'email_verified_otp' => $otp,
        ]);

        // إرسال OTP عبر البريد الإلكتروني
        try {
            Mail::to($user->email)->send(new email_verified_otp($otp));
        } catch (\Exception $e) {
            return resourceApi::sendResponse(500, __('messages.email_send_failed'));
        }

        return resourceApi::sendResponse(201, __('messages.user_created'));
    }

    public function login(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|min:8',
            'password' => 'required|string|min:8',
        ], [], [
            'login' => __('messages.login'),
            'password' => __('messages.password'),
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // البحث عن المستخدم بناءً على البريد الإلكتروني أو اسم المستخدم
        $user = User::where('email', $request->login)
            ->orWhere('username', $request->login) // تأكد من اسم الحقل "username" وليس "userName"
            ->first();

        if (!$user) {
            return resourceApi::sendResponse(422, __('messages.user_not_found'), []);
        }

        // التحقق من حالة تفعيل البريد الإلكتروني
        if (is_null($user->email_verified_at)) {
            // توليد OTP وإرساله عبر البريد الإلكتروني
            $otp = rand(1000, 9999);

            $user->update([
                'email_verified_otp' => 1111,
            ]);

            Mail::to($user->email)->send(new email_verified_otp($otp));

            return resourceApi::sendResponse(403, __('messages.email_not_verified'), []);
        }

        // الحصول على بيانات تسجيل الدخول
        $login = $request->input('login');
        $password = $request->input('password');

        // التحقق من صحة كلمة المرور
        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('authToken')->plainTextToken;
            $data['id'] = $user->id;
            $data['token'] = $token;
            $data['email'] = $user->email;
            $data['username'] = $user->username;
            $data['name'] = $user->name;
            $data['country_code'] = $user->country_code;
            $data['phone_number'] = $user->phone_number;
            $data['profile_image'] = $user->profile_image;

            return resourceApi::sendResponse(200, __('messages.login_successful'), $data);
        } else {
            return resourceApi::sendResponse(401, __('messages.login_unsuccessful'), []);
        }
    }

    public function profile()
    {
        $user = Auth::user();

        return resourceApi::sendResponse(200, '', new UserResource($user));

    }
    public function profileUser($id)
    {
        $authUser = Auth::user(); // Current authenticated user
        $user = User::with(['videos', 'followers', 'following'])->find($id); // Target user

        if (!$user) {
            return resourceApi::sendResponse(404, 'User not found.', []);
        }

        // Calculate the total likes on the user's videos
        $totalLikes = $user->videos->sum(function ($video) {
            return $video->likes;
        });

        // Check if the current user is following the target user
        $isFollowing = $authUser->following()->where('user_id', $user->id)->exists();

        // Prepare the data to return
        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'profile_image' => $user->profile_image,
            'country_code' => $user->country_code ?? 'N/A', // Default value if null
            'type' => $user->type, // Assuming "type" refers to the user role (admin/user)
            'email' => $user->email,
            'videos_count' => $user->videos->count(), // Count of videos
            'followers_count' => $user->followers->count(), // Count of followers
            'following_count' => $user->following->count(), // Count of following
            'total_likes' => $totalLikes, // Total likes on user's videos
            'is_following' => $isFollowing, // True if the current user follows the target user
        ];

        // Show phone number only for admin users
        if ($user->type === 'admin') {
            $data['phone_number'] = $user->phone_number;
        }

        // Return the response using resourceApi
        return resourceApi::sendResponse(200, 'Profile data fetched successfully.', $data);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return resourceApi::sendResponse(200, __('messages.logged_out'), []);
    }

    public function update_profile(Request $request)
    {
        $user = Auth::user();

        // التحقق من المدخلات باستخدام Validator
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255', // تصحيح 'unallble' إلى 'nullable'
            'phone_number' => 'nullable|string|max:255', // تصحيح 'unallble' إلى 'nullable'
            'country_code' => 'nullable|string|max:255', // تصحيح 'unallble' إلى 'nullable'
        ]);

        // إذا فشل التحقق، يتم إرجاع رسائل التحقق
        if ($validator->fails()) {
            return ResourceApi::sendResponse(422, __('validation.failed'), $validator->messages()->all());
        }

        // تحديث معلومات المستخدم
        $user->update([
            'name' => $request->name ?? $user->name, // استخدام الاسم الحالي إذا لم يكن موجودًا
            'phone_number' => $request->phone_number ?? $user->phone_number, // استخدام الرقم الحالي إذا لم يكن موجودًا
            'country_code' => $request->country_code ?? $user->country_code, // استخدام الرقم الحالي إذا لم يكن موجودًا
        ]);

        // إرجاع استجابة ناجحة مع رسالة مترجمة
        return ResourceApi::sendResponse(200, __('messages.profile_updated'), [
            'user' => $user,
        ]);
    }

    public function updateProfileImage(Request $request)
    {
        $user = Auth::user();

        // التحقق من صحة الملف
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // deleteFile($user->profile_image);

        // $user->profile_image = uploadFile('users', $request->profile_image);
        // $user->save();

        // return apiResponse(true, 200, $user->profile_image);

        // رفع الصورة
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('profile_images', 'public');

            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // تحديث الحقل في قاعدة البيانات
            $user->update(['profile_image' => $imagePath]);

            // إرجاع رابط الصورة الكامل
            $fullImagePath = asset('storage/' . $imagePath);
        }

        return response()->json([
            'message' => 'Profile image updated successfully',
            'profile_image' => $fullImagePath,
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ], [], [
            'current_password' => __('messages.current_password'),
            'new_password' => __('messages.new_password'),
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $user = Auth::user();

        // التحقق من أن كلمة المرور الحالية صحيحة
        if (!Hash::check($request->current_password, $user->password)) {
            return resourceApi::sendResponse(400, __('messages.incorrect_current_password'), []);
        }

        // تحديث كلمة المرور
        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return resourceApi::sendResponse(200, __('messages.password_changed_successfully'), []);
    }

    public function verified(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|min:8',
            'code' => 'required|string|min:4|max:4',
        ], [], [
            'login' => __('messages.email'), // استخدم الرسائل من ملفات الترجمة
            'code' => __('messages.verification_code'),
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $user = User::where('email', $request->login)
            ->orWhere('userName', $request->login)
            ->first();

        if (!$user) {
            return resourceApi::sendResponse(422, __('messages.user_not_found'), []);
        }

        if ($user->email_verified_otp == $request->code) {
            $user->update(['email_verified_at' => now()]);
            return resourceApi::sendResponse(200, __('messages.email_verified_successfully'), $user);
        } else {
            return resourceApi::sendResponse(422, __('messages.invalid_otp_code'), []);
        }
    }

    public function reset(Request $request)
    {
        // تحقق من بيانات الإدخال
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        // جلب المستخدم من البريد الإلكتروني
        $user = User::where('email', $request->email)->first();

        // إذا لم يتم العثور على المستخدم، يجب أن نعود برسالة "غير مصرح به"
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return resourceApi::sendResponse(200, __('messages.password_changed_successfully'), []);

    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [], [
            'email' => __('messages.email'),
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $otp = 1111;
        $user = User::where('email', $request->email)->first();

        $user->update([
            'email_verified_otp' => $otp,
        ]);

        Mail::to($user->email)->send(new email_verified_otp($otp));

        return resourceApi::sendResponse(200, __('messages.otp_sent'), []);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ], [], [
            'email' => __('messages.email'),
            'otp' => __('messages.otp'),
        ]);

        if ($validator->fails()) {
            return ResourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        $user = User::where('email', $request->email)->first();

        if ($user && $user->email_verified_otp === $request->otp) {
            // تحقق ناجح
            $user->update([
                'email_verified_at' => now(),
            ]);

            // إنشاء توكن جديد
            $token = $user->createToken('YourAppName')->plainTextToken;

            // إرجاع استجابة مع التوكن
            return ResourceApi::sendResponse(200, __('messages.otp_verified'), [
                'token' => $token,
            ]);
        } else {
            return ResourceApi::sendResponse(422, __('messages.invalid_otp'), []);
        }
    }

}
