<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserReportController extends Controller
{
    // Create a new report
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reported_id' => 'required|exists:users,id',
            'report_type_id' => 'required|exists:report_types,id',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $report = UserReport::create([
            'reporter_id' => auth()->user()->id,
            'reported_id' => $request->input('reported_id'),
            'report_type_id' => $request->input('report_type_id'),
            'status' => $request->input('status', 0),
        ]);

        return resourceApi::sendResponse(201, 'Report created successfully', $report);
    }

    // Retrieve all reports
    public function index()
    {
        $reports = UserReport::with(['reporter', 'reported', 'reportType'])->paginate(10);

        return resourceApi::pagination($reports, $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'reporter' => $report->reporter->name,
                'reported' => $report->reported->name,
                'reported_id' => $report->reported_id,
                'reportType' => json_decode($report->reportType->name, true),
                'reportTypeCount' => UserReport::where('reported_id', $report->reported_id)->count(),
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

        $report = UserReport::findOrFail($id);

        $report->update([
            'status' => $request->input('status', $report->status),
        ]);

        return resourceApi::sendResponse(200, 'Report updated successfully', $report);
    }

}
