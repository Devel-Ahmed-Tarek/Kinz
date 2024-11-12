<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiftsController extends Controller
{
    // Display a listing of gifts
    public function index()
    {

        $gifts = Gift::paginate(10); // Fetch 10 gifts per page

        $data = $gifts->map(function ($gift) {
            return [
                'id' => $gift->id,
                'price' => $gift->price,
                'image' => $gift->image,
                'status' => $gift->status,
            ];
        });
        return resourceApi::pagination($gifts, $data);
    }

    // Store a newly created gift
    public function store(Request $request)
    {
        // Validate the data
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric', // Ensuring price is a numeric value
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Initialize image URL
        $url = null;

        // Handle the image upload
        if ($request->hasFile('image')) {
            $img = $request->file('image');
            $reimg = time() . '.' . $img->getClientOriginalExtension();
            $dest = public_path('/images/gifts'); // Ensure the 'images/gifts' folder exists
            if (!file_exists($dest)) {
                mkdir($dest, 0775, true); // Create the folder if it doesn't exist
            }

            // Move the image and generate its URL
            $img->move($dest, $reimg);
            $url = url('/images/gifts/' . $reimg); // Generate the full URL
        }

        // Create a new gift entry
        Gift::create([
            'price' => $request->price,
            'status' => 0, // Default status set to 0
            'image' => $url, // Store the full URL of the image
        ]);

        return resourceApi::sendResponse(201, 'Gift added successfully', [
            'image_url' => $url, // Return the full image URL in the response
        ]);
    }

    // Show the details of a specific gift
    public function show($id)
    {
        $gift = Gift::find($id);

        if (!$gift) {
            return resourceApi::sendResponse(404, 'Gift not found', []);
        }

        return resourceApi::sendResponse(200, 'Gift details', $gift);
    }

    // Update the specified gift
    public function update(Request $request, $id)
    {
        $gift = Gift::findOrFail($id);

        // Validate the data
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image if provided
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Handle the image upload if there's a new one
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($gift->image && file_exists(public_path($gift->image))) {
                unlink(public_path($gift->image));
            }

            // Store new image
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            // Generate the full URL for the image
            $fullImageUrl = url('images/' . $imageName);
            $gift->image = $fullImageUrl;
        }

        // Update gift data
        $gift->price = $request->price;
        $gift->save();

        // Return the response with the updated data
        return resourceApi::sendResponse(200, 'Gift updated successfully', [
            'id' => $gift->id,
            'price' => $gift->price,
            'image_url' => $gift->image, // Full image URL
        ]);
    }

    // Delete the specified gift
    public function destroy($id)
    {
        $gift = Gift::find($id);

        if (!$gift) {
            return resourceApi::sendResponse(404, 'Gift not found', []);
        }

        $gift->delete();

        return resourceApi::sendResponse(200, 'Gift deleted successfully', []);
    }

    public function updateStatus(Request $request, $id)
    {
        // Validate that status is a boolean (true/false)
        $validator = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        // Find the gift by ID
        $gift = Gift::find($id);

        if (!$gift) {
            return resourceApi::sendResponse(404, 'Gift not found', []);
        }

        // Update the status
        $gift->status = $request->input('status');
        $gift->save();

        return resourceApi::sendResponse(200, 'Gift status updated successfully', $gift);
    }

}
