<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Gift;
use App\Models\GiftPurchase;
use App\Models\PointsWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BayGiftsController extends Controller
{

    // Display a listing of gifts
    public function index()
    {

        $gifts = Gift::where('status', "1")->paginate(10); // Fetch 10 gifts per page

        $data = $gifts->map(function ($gift) {
            return [
                'id' => $gift->id,
                'price' => $gift->price,
                'image' => $gift->image,
            ];
        });
        return resourceApi::pagination($gifts, $data);
    }

    public function buyGift(Request $request)
    {
        // تحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'gift_id' => 'required|exists:gifts,id', // التحقق من أن الهدية موجودة
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        // الحصول على المستخدم الحالي
        $user = Auth::user();

        // الحصول على محفظة النقاط الخاصة بالمستخدم
        $wallet = PointsWallet::where('user_id', $user->id)->first();

        // الحصول على الهدية
        $gift = Gift::find($request->gift_id);

        // التحقق من أن المستخدم لديه نقاط كافية لشراء الهدية
        if ($wallet->points < $gift->price) {
            return response()->json(['message' => 'Not enough points to buy this gift.'], 400);
        }

        // خصم النقاط من المحفظة
        $wallet->points -= $gift->price;
        $wallet->save();

        // تسجيل عملية شراء الهدية
        GiftPurchase::create([
            'user_id' => $user->id,
            'gift_id' => $gift->id,
            'points_spent' => $gift->price,
        ]);

        $wallet = $user->pointsWallet;
        $wallet->balance -= $gift->price;
        $wallet->save();

        return response()->json(['message' => 'Gift purchased successfully.'], 200);
    }

}
