<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\Ban;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        // Get the search query input from the request
        $searchQuery = $request->input('query');

        // Build the query to get users of type 'user'
        $query = User::where('type', 'user');

        // If a search query is provided, apply the search filters
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                // Search in 'name', 'email', or 'username' fields using the same query
                $q->where('name', 'like', "%{$searchQuery}%")
                    ->orWhere('email', 'like', "%{$searchQuery}%")
                    ->orWhere('username', 'like', "%{$searchQuery}%");
            });
        }

        // Execute the query with pagination (10 items per page)
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        // Return the paginated data along with the UserResource collection
        return resourceApi::pagination($users, UserResource::collection($users));
    }

    public function delete($id)
    {
        User::findOrFail($id)->delete();
        return resourceApi::sendResponse(200, 'تم الحذف بنجاح ', []);
    }

    public function banUser(Request $request)
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id', // Validate that the userId exists in the users table
            'duration' => 'required|string|in:permanent,3_hours,6_hours,12_hours,1_day,3_days,1_week,1_month,1_year', // Validate duration value
            'reason' => 'nullable|string|max:255', // Validate optional reason field
        ]);



        // If validation fails, return the error messages
        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Get the ban details from the request
        $duration = $request->duration;
        $userId = $request->userId;
        $reason = $request->reason;

        // Determine the ban type and unban date based on the duration
        $banData = [
            'user_id' => $userId,
            'reason' => $reason,
            'ban_type' => $duration === 'permanent' ? 'permanent' : 'temporary',
        ];

        if ($duration !== 'permanent') {
            // Set the unbanned_at date for temporary bans
            switch ($duration) {
                case '3_hours':
                    $banData['unbanned_at'] = Carbon::now()->addHours(3);
                    break;
                case '6_hours':
                    $banData['unbanned_at'] = Carbon::now()->addHours(6);
                    break;
                case '12_hours':
                    $banData['unbanned_at'] = Carbon::now()->addHours(12);
                    break;
                case '1_day':
                    $banData['unbanned_at'] = Carbon::now()->addDay();
                    break;
                case '3_days':
                    $banData['unbanned_at'] = Carbon::now()->addDays(3);
                    break;
                case '1_week':
                    $banData['unbanned_at'] = Carbon::now()->addWeek();
                    break;
                case '1_month':
                    $banData['unbanned_at'] = Carbon::now()->addMonth();
                    break;
                case '1_year':
                    $banData['unbanned_at'] = Carbon::now()->addYear();
                    break;
                default:
                    return resourceApi::sendResponse(400, 'Invalid duration specified', []);
            }
        }

        // Update or create the ban record
        Ban::updateOrCreate(
            ['user_id' => $userId], // Unique identifier for the ban record
            $banData // Data to update or insert
        );

        // Return a success response
        return resourceApi::sendResponse(200, 'تم حظر المستحدم بنجاح', []);
    }

    public function deleteBan($userId)
    {
        // Check if the user is banned first
        $ban = Ban::where('user_id', $userId)->first();

        if (!$ban) {
            // If there is no ban for this user
            return resourceApi::sendResponse(200, 'هذا المستخد لبس محظور ', []);
        }

        // If the user is banned, delete the ban
        $ban->delete();

        // Return response after deletion
        return resourceApi::sendResponse(200, 'User ban deleted successfully', []);
    }

}