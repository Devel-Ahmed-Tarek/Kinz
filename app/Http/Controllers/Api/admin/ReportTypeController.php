<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportTypeController extends Controller
{
    public function index()
    {
        // Retrieve paginated report types
        $data = ReportType::paginate(10); // Paginate with 10 items per page

        // Map the data to include decoded JSON fields
        $mappedData = $data->getCollection()->map(function ($reportType) {
            return [
                'id' => $reportType->id,
                'name' => json_decode($reportType->name, true), // Decode the name from JSON
                'type' => json_decode($reportType->type, true), // Decode the type from JSON
                'status' => $reportType->status,
            ];
        });

        // Return the paginated data
        return resourceApi::pagination($data, $mappedData);
    }

    public function add(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255', // Validate the name in Arabic
            'name.en' => 'required|string|max:255', // Validate the name in English
            'type' => 'required|array', // Validate that the type is an array
            'type.*' => 'string|max:255', // Validate that each type element is a string
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Add the report type
        ReportType::create([
            'name' => json_encode([
                'ar' => $request->input('name.ar'), // Save the name in Arabic
                'en' => $request->input('name.en'), // Save the name in English
            ]),
            'type' => json_encode($request->type), // Save the type as a JSON array
        ]);

        // Return a success response
        return resourceApi::sendResponse(201, 'Report type created successfully', []);
    }

    public function update(Request $request, $id)
    {
        // Find the report type by ID or fail
        $report = ReportType::findOrFail($id);

        // Validate the input
        $validator = Validator::make($request->all(), [
            'name.ar' => 'required|string|max:255', // Validation for Arabic name
            'name.en' => 'required|string|max:255', // Validation for English name
            'type' => 'required|array', // Ensure type is an array
            'type.*' => 'string|max:255', // Validate that each type element is a string
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Update the report type fields
        $report->name = json_encode([
            'ar' => $request->input('name.ar'),
            'en' => $request->input('name.en'),
        ]);
        $report->type = json_encode($request->input('type'));

        // Save the updated report
        $report->save();

        // Return a success response
        return resourceApi::sendResponse(200, 'Report type updated successfully', []);
    }

    public function delete($id)
    {
        $report = ReportType::findOrFail($id);
        $report->delete();
        return resourceApi::sendResponse(200, 'تم حذف  نوع البلاغ بنجاح', );

    }

    public function updateStatus(Request $request)
    {

        try {
            // Find the report type by ID or throw a ModelNotFoundException
            $report = ReportType::findOrFail($request->id);

            // Validate the input to ensure the status is a boolean
            $validator = Validator::make($request->all(), [
                'status' => 'required|boolean', // Validate that status is a boolean (0 or 1)
            ]);

            if ($validator->fails()) {
                return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
            }

            // Update the status field
            $report->status = $request->input('status');

            // Save the updated report type
            $report->save();

            // Return a success response
            return resourceApi::sendResponse(200, 'Status updated successfully', []);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // If no report type is found, return a 404 error
            return resourceApi::sendResponse(404, 'Report type not found', []);
        }
    }

    public function show($id)
    {
        try {
            // Find the report type by ID or throw a ModelNotFoundException
            $report = ReportType::findOrFail($id);

            // Format the report type details to return
            $data = [
                'id' => $report->id,
                'name' => json_decode($report->name, true), // Decode the name from JSON
                'type' => json_decode($report->type, true), // Decode the type from JSON
                'status' => $report->status,
            ];

            // Return the report details
            return resourceApi::sendResponse(200, 'Report type retrieved successfully', $data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Return a 404 error if the report type is not found
            return resourceApi::sendResponse(404, 'Report type not found', []);
        }
    }

}