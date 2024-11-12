<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentReportController extends Controller
{

    // Create a new report
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|exists:comments,id',
            'report_type_id' => 'required|exists:report_types,id',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $report = CommentReport::create([
            'user_id' => Auth::user()->id,
            'comment_id' => $request->input('comment_id'),
            'report_type_id' => $request->input('report_type_id'),
            'status' => $request->input('status', 0),
        ]);

        return resourceApi::sendResponse(201, 'Report created successfully', $report);
    }

    // Retrieve all reports
    public function index()
    {
        $reports = CommentReport::with(['comment', 'user', 'reportType'])->paginate(10);

        return resourceApi::pagination($reports, $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'comment' => $report->comment->content,
                'user' => $report->user->name,
                'reportType' => json_decode($report->reportType->name, true),
                'status' => $report->status,
                'created_at' => $report->created_at,
            ];
        }));
    }

    // Update a report
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $report = CommentReport::findOrFail($id);

        $report->update([
            'status' => $request->input('status', $report->status),
        ]);

        return resourceApi::sendResponse(200, 'Report updated successfully', $report);
    }

    // Delete a report
    public function destroy($id)
    {
        $report = CommentReport::findOrFail($id);
        Comment::findOrFail($report->comment_id)->delete();

        return resourceApi::sendResponse(200, 'Comment deleted successfully');
    }
}
