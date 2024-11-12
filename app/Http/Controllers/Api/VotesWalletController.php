<?php

namespace App\Http\Controllers\Api;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VotesWalletController extends Controller
{

    public function index()
    {
        // Paginate the votes, displaying 10 records per page
        $votes = Vote::where("status", '1')->paginate(10); // 10 votes per page

        // Map votes to a more descriptive structure
        $voteData = $votes->map(function ($vote) {
            return [
                'id' => $vote->id,
                'points' => $vote->points,
                'amount' => $vote->amount,
            ];
        });

        // Return paginated response
        return resourceApi::pagination($votes, $voteData);
    }

    public function showVotesWallet()
    {
        $user = Auth::user();
        $wallet = $user->votesWallet;

        if (!$wallet) {
            return response()->json(['message' => 'Votes wallet not found'], 404);
        }

        return response()->json(['balance' => $wallet->balance], 200);
    }

    // إضافة رصيد إلى محفظة التصويطات
    public function addVotes(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->votesWallet;

        if (!$wallet) {
            return response()->json(['message' => 'Votes wallet not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $wallet->balance += $request->amount;
        $wallet->save();

        return response()->json(['message' => 'Votes added successfully', 'balance' => $wallet->balance], 200);
    }

    public function deductVotes(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'votes' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        if ($user->votes_wallet < $request->votes) {
            return response()->json(['message' => 'Not enough votes in the wallet.'], 400);
        }

        $user->votes_wallet -= $request->votes;
        $user->save();

        return response()->json(['message' => 'Votes deducted successfully.', 'remaining_votes' => $user->votes_wallet], 200);
    }

}
