<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = MessageReport::with(['user', 'message', 'reportType'])
            ->orderBy('status', 'asc')
            ->paginate(10);

        $mappedData = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'user' => $report->user->name,
                'message' => $report->message ? $report->message->message : null, // استرجاع محتوى الرسالة
                'report_type' => json_decode($report->reportType->name, true), // فك تشفير اسم نوع البلاغ
                'status' => $report->status,
                'created_at' => $report->created_at,
            ];
        });

        return resourceApi::pagination($reports, $mappedData);
    }

    public function store(Request $request, )
    {
        $validator = Validator::make($request->all(), [
            'report_type_id' => 'required|exists:report_types,id',
            'message_id' => 'required|exists:messages,id',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $message = Message::findOrFail($request->message_id);

        $report = MessageReport::create([
            'user_id' => Auth::id(),
            'message_id' => $message->id,
            'report_type_id' => $request->input('report_type_id'),
        ]);

        return resourceApi::sendResponse(201, 'Report created successfully', $report);
    }

    public function update(Request $request, $id)
    {
        $report = MessageReport::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $report->update([
            'status' => $request->input('status'),
        ]);

        return resourceApi::sendResponse(200, 'Report status updated successfully', $report);
    }

    public function destroy($id)
    {
        $report = MessageReport::findOrFail($id);
        Message::where('id', $report->message_id)->delete();
        return resourceApi::sendResponse(200, 'Report deleted successfully');
    }
}
