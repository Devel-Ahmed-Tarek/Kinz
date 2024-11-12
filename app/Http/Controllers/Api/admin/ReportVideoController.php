<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\ReportVideo;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportVideoController extends Controller
{
    public function index()
    {
        // Retrieve all reports with selected fields and pagination (10 per page), ordered by status
        $reports = ReportVideo::with(['user:id,name', 'video:id,video_path', 'reportType:id,name'])
            ->select('id', 'user_id', 'video_id', 'report_type_id', 'status', 'created_at') // Specify the fields to select
            ->orderBy('status', 'asc') // Order by status
            ->paginate(10);

        // Map the report data to prepare it for the response
        $mappedData = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'video_id' => $report->video_id,
                'user' => $report->user ? $report->user->name : null,
                'video' => $report->video ? url($report->video->video_path) : null, // Get the full URL of the video
                'report_type' => json_decode($report->reportType->name, true), // Decode JSON
                'status' => $report->status,
                'created_at' => $report->created_at,
            ];
        });

        // Replace the collection in the paginator with the mapped data
        $reports->setCollection($mappedData);

        // Return the paginated response with the mapped data
        return resourceApi::pagination($reports, $mappedData);
    }

    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
            'report_type_id' => 'required|exists:report_types,id',
        ]);

        // Ensure the user is authenticated
        $user = Auth::user();

        // Check if the user has already reported this video with the same report type
        $existingReport = ReportVideo::where('user_id', $user->id)
            ->where('video_id', $request->video_id)
            ->where('report_type_id', $request->report_type_id)
            ->first();

        if ($existingReport) {
            return resourceApi::sendResponse(400, 'You have already reported this video for the same reason.', null);
        }

        // Create a new report
        $report = ReportVideo::create([
            'user_id' => $user->id,
            'video_id' => $request->video_id,
            'report_type_id' => $request->report_type_id,
            'status' => 0, // Default to pending
        ]);

        return resourceApi::sendResponse(201, 'Report created successfully', $report);
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        // Find the report by ID
        $report = ReportVideo::find($id);

        if (!$report) {
            return resourceApi::sendResponse(404, 'Report not found', null);
        }

        // Update the report status
        $report->update([
            'status' => $request->status,
        ]);

        return resourceApi::sendResponse(200, 'Report updated successfully', $report);
    }

    /**
     *  video deleted.
     */
    public function destroy($id)
    {
        // Find the report by ID
        $report = ReportVideo::find($id);

        if (!$report) {
            return resourceApi::sendResponse(404, 'Report not found', null);
        }
        Video::findOrFail($report->video_id)->delete();

        return resourceApi::sendResponse(200, 'video deleted successfully', null);
    }
}
