<?php

namespace App\Http\Controllers\Api\admin;

use App\Helpers\resourceApi;
use App\Http\Controllers\Controller;
use App\Models\Voice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VoiceController extends Controller
{

    // Create a new voice entry
    public function store(Request $request)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'voice' => 'nullable|mimes:mp3,wav,m4a|max:10240', // Validate as file, no string rule
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $imagePath = null;
        $audioPath = null;

        // Save image if uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public'); // Store image in public folder
        }

        // Save audio if uploaded
        if ($request->hasFile('voice')) {
            $audio = $request->file('voice');
            $audioPath = $audio->store('voice', 'public'); // Store audio in public folder
        }

        // Create new voice entry
        $voice = Voice::create([
            'voice' => $audioPath,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'image' => $imagePath, // Save image path
        ]);

        return resourceApi::sendResponse(201, 'Voice created successfully', $voice);
    }

    // Retrieve all voices
    public function index()
    {
        $voices = Voice::paginate(10);

        return resourceApi::pagination($voices, $voices->getCollection()->map(function ($voice) {
            return [
                'id' => $voice->id,
                'name' => $voice->name,
                'description' => $voice->description,
                'image' => $voice->image ? asset('storage/' . $voice->image) : null, // Full URL to image
                'voice' => $voice->voice ? asset('storage/' . $voice->voice) : null, // Full URL to audio
                'created_at' => $voice->created_at,
            ];
        }));
    }

    // Retrieve a specific voice
    public function show($id)
    {
        $voice = Voice::findOrFail($id);

        return resourceApi::sendResponse(200, 'Voice retrieved successfully', [
            'id' => $voice->id,
            'name' => $voice->name,
            'description' => $voice->description,
            'image' => $voice->image ? asset('storage/' . $voice->image) : null,
            'voice' => $voice->voice ? asset('storage/' . $voice->voice) : null,
            'created_at' => $voice->created_at,
        ]);
    }

    // Update an existing voice
    public function update(Request $request, $id)
    {
        // Validate input data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
            'voice' => 'nullable|mimes:mp3,wav,m4a|max:10240', // Validate audio file
        ]);

        if ($validator->fails()) {
            return resourceApi::sendResponse(422, 'Validation errors', $validator->messages()->all());
        }

        $voice = Voice::findOrFail($id);

        // Update image if a new one is uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
            $voice->image = $imagePath;
        }

        // Update audio if a new one is uploaded
        if ($request->hasFile('voice')) {
            $audio = $request->file('voice');
            $audioPath = $audio->store('voice', 'public');
            $voice->voice = $audioPath;
        }

        // Update other fields
        $voice->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return resourceApi::sendResponse(200, 'Voice updated successfully', $voice);
    }

    // Delete a specific voice
    public function destroy($id)
    {
        $voice = Voice::findOrFail($id);

        // Delete image and audio files if they exist
        if ($voice->image) {
            \Storage::disk('public')->delete($voice->image);
        }
        if ($voice->voice) {
            \Storage::disk('public')->delete($voice->voice);
        }

        // Delete voice entry from database
        $voice->delete();

        return resourceApi::sendResponse(200, 'Voice deleted successfully');
    }

}
