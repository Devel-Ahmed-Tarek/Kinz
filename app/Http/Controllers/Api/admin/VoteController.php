<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoteController extends Controller
{
    public function index()
    {
        // Paginate the votes, displaying 10 records per page
        $votes = Vote::paginate(10);

        // Map votes to a more descriptive structure
        $voteData = $votes->map(function ($vote) {
            return [
                'id' => $vote->id,
                'points' => $vote->points,
                'amount' => $vote->amount,
                'status' => $vote->status,
            ];
        });

        // Return paginated response
        return resourceApi::pagination($votes, $voteData);
    }

    public function store(Request $request)
    {
        // Validate incoming data
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // Create a new vote record
        $newVote = Vote::create([
            'points' => $request->points,
            'amount' => $request->amount,
            'status' => 1, // Default status set to 1 (paid)
        ]);

        return resourceApi::sendResponse(201, __('messages.points_purchased_successfully'), $newVote);
    }

    public function updateStatus(Request $request, $voteId)
    {
        // Check for the vote record based on the ID
        $vote = Vote::find($voteId);

        if (!$vote) {
            return resourceApi::sendResponse(404, __('messages.vote_not_found'), []);
        }

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean', // Status should be 0 or 1
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // Update the status
        $vote->status = $request->input('status');
        $vote->save();

        return resourceApi::sendResponse(200, __('messages.status_updated_successfully'), ['status' => $vote->status]);
    }

    public function update(Request $request, $voteId)
    {
        // Find the vote record by its ID
        $vote = Vote::find($voteId);

        if (!$vote) {
            return resourceApi::sendResponse(404, __('messages.vote_not_found'), []);
        }

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1', // Ensure points are a positive integer
            'amount' => 'required|numeric|min:0', // Ensure amount is a non-negative number
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, __('messages.validation_failed'), $validator->messages()->all());
        }

        // Update the vote record
        $vote->points = $request->input('points');
        $vote->amount = $request->input('amount');
        $vote->save();

        // Return a success response with the updated vote data
        return resourceApi::sendResponse(200, __('messages.vote_updated_successfully'), $vote);
    }

    // Display a specific vote
    public function show($voteId)
    {
        $vote = Vote::findOrFail($voteId);
        return resourceApi::sendResponse(200, __('messages.vote_fetched_successfully'), $vote);
    }

    // Delete a vote
    public function destroy($voteId)
    {
        $vote = Vote::findOrFail($voteId);
        $vote->delete();
        return resourceApi::sendResponse(200, __('messages.vote_deleted_successfully'), []);
    }
}
