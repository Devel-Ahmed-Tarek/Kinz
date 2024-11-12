<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PointController extends Controller
{
    public function index()
    {
        // تنفيذ عملية الترقيم لعرض 10 سجلات في كل صفحة
        $points = Point::paginate(10);

        $data = $points->map(function ($point) {
            return [
                'id' => $point->id,
                'points' => $point->points,
                'amount' => $point->amount,
                'status' => $point->status,
            ];
        });

        // إرجاع الاستجابة مع البيانات المرقمة
        return resourceApi::pagination($points, $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $point = Point::create([

            'points' => $request->points,
            'amount' => $request->amount,
            'status' => 1, // تعيين الحالة افتراضياً إلى 1 (تم الدفع)
        ]);

        return resourceApi::sendResponse(201, 'Points purchased successfully', $point);
    }

    public function updateStatus(Request $request, $id)
    {
        // تحقق من وجود نقطة الشراء بناءً على المعرف
        $point = Point::find($id);

        if (!$point) {
            return resourceApi::sendResponse(404, 'Point not found', []);
        }

        // تحقق من صحة البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean', // الحالة يجب أن تكون 0 أو 1
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // تحديث الحالة
        $point->status = $request->input('status');
        $point->save();

        return resourceApi::sendResponse(200, 'Status updated successfully', ['status' => $point->status]);
    }

    public function update(Request $request, $id)
    {
        // Find the point record by its ID
        $point = Point::find($id);

        if (!$point) {
            return resourceApi::sendResponse(404, 'Point not found', []);
        }

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1', // Ensure points are a positive integer
            'amount' => 'required|numeric|min:0', // Ensure amount is a non-negative number

        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Update the point record
        $point->points = $request->input('points');
        $point->amount = $request->input('amount');

        $point->save();

        // Return a success response with the updated point data
        return resourceApi::sendResponse(200, 'Point updated successfully', $point);
    }

    // عرض نقاط الشراء لمستخدم معين
    public function show($id)
    {
        $point = Point::findOrFail($id);
        return resourceApi::sendResponse(200, 'Point fetched successfully', $point);
    }

    // حذف نقاط الشراء
    public function destroy($id)
    {
        $point = Point::findOrFail($id);
        $point->delete();
        return resourceApi::sendResponse(200, 'Point deleted successfully', []);
    }
}
