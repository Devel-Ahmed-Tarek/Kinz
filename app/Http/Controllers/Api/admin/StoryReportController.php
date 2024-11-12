<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryReport;

class StoryReportController extends Controller
{
    public function destroy($id)
    {

        $story = Story::findOrFail($id);

        // Check if the authenticated user is the one who created the report
        if ($story->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the report
        $story->delete();

        return response()->json(['message' => 'story deleted successfully'], 200);
    }

    public function index()
    {
        // Retrieve all reports with selected fields and pagination (10 per page), ordered by status
        $reports = StoryReport::with(['user:id,name', 'story:id,user_id,content,type,created_at', 'story.user:id,name', 'reportType:id,name'])
            ->select('id', 'user_id', 'story_id', 'report_type_id', 'created_at') // Specify the fields to select
        // Order by status
            ->paginate(10);

        // Map the report data to prepare it for the response
        $mappedData = $reports->getCollection()->map(function ($report) {
            return [
                'id' => $report->id,
                'story_id' => $report->story_id,
                'reporter' => $report->user ? $report->user->name : null, // Name of the user who reported
                'story_creator' => $report->story && $report->story->user ? $report->story->user->name : null, // Name of the user who created the story
                'story_content' => $report->story ? $report->story->content : null,
                'story_type' => $report->story ? $report->story->type : null,
                'report_type' => json_decode($report->reportType->name, true), // Decode JSON
                'report_created_at' => $report->created_at, // Date of the report
                'story_created_at' => $report->story ? $report->story->created_at : null, // Date the story was created
            ];
        });

        // Replace the collection in the paginator with the mapped data
        $reports->setCollection($mappedData);

        // Return the paginated response with the mapped data
        return resourceApi::pagination($reports, $mappedData);
    }

}
