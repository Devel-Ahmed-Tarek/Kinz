<?php

namespace App\Http\Controllers;

use App\Events\Message as EventsMessage;
use App\Helpers\resourceApi;
use App\Http\Resources\User\ChatResource;
use App\Http\Resources\User\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{

    public function getOrCreateChat($userId)
    {
        $authUser = Auth::user();

        // التحقق من وجود شات مسبق بين المستخدمين
        $chat = Chat::where(function ($query) use ($authUser, $userId) {
            $query->where('user1_id', $authUser->id)
                ->where('user2_id', $userId);
        })->orWhere(function ($query) use ($authUser, $userId) {
            $query->where('user1_id', $userId)
                ->where('user2_id', $authUser->id);
        })->first();

        // إذا لم يكن الشات موجودًا، قم بإنشائه
        if (!$chat) {
            $chat = Chat::create([
                'user1_id' => $authUser->id,
                'user2_id' => $userId,
            ]);
        }

        return response()->json($chat);
    }

    public function sendMessage(Request $request, $chatId)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:1000',
            'type' => 'required|string|in:text,file',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chat = Chat::findOrFail($chatId);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        // Check message content based on type
        if ($request->input('type') == 'text' && !$request->input('message')) {
            return response()->json(['error' => 'Message content cannot be empty for text type'], 422);
        }

        if ($request->input('type') == 'file' && !$request->hasFile('file')) {
            return response()->json(['error' => 'File is required for file type'], 422);
        }

        // Handle file upload
        $fileUrl = null;
        if ($request->input('type') == 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileUrl = $file->storeAs('files', $fileName);
        }

        // Create the message
        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => $authUser->id,
            'message' => $request->input('message'),
            'type' => $request->input('type'),
            'file_url' => $fileUrl,
        ]);

        // Determine the receiver
        $receiver = User::find($chat->user2_id);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found'], 404);
        }
        $sender = Auth::user();

        event(new EventsMessage($message, $sender, $receiver));

        // Return the response using MessageResource
        return resourceApi::sendResponse(200, '', new MessageResource($message));
    }

    /**
     * حذف رسالة معينة.
     */
    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);

        // تأكد من أن المستخدم هو المرسل أو لديه صلاحيات الحذف
        if ($message->sender_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return resourceApi::sendResponse(200, 'Message deleted successfully', []);

    }

    /**
     * البحث عن محادثات المستخدم.
     */
    public function searchChats(Request $request)
    {
        $user = Auth::user();
        $searchTerm = $request->input('search_term');

        $chats = Chat::where(function ($query) use ($user, $searchTerm) {
            $query->where('user1_id', $user->id)
                ->orWhere('user2_id', $user->id);
        })->whereHas('user1', function ($query) use ($searchTerm) {
            $query->where('name', 'like', "%$searchTerm%");
        })->orWhereHas('user2', function ($query) use ($searchTerm) {
            $query->where('name', 'like', "%$searchTerm%");
        })->get();

        return resourceApi::sendResponse(200, '', ChatResource::collection($chats));
    }
    public function getUserChats()
    {
        $user = Auth::user();
        $chats = Chat::where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->with(['user1', 'user2', 'messages' => function ($query) {
                $query->latest()->limit(1); // Get only the latest message
            }])
            ->get();

        // Collect relevant user info, chat ID, and the last message
        $chatUsers = $chats->map(function ($chat) use ($user) {
            $chatUser = $chat->user1_id === $user->id ? $chat->user2 : $chat->user1;
            $lastMessage = $chat->messages->first(); // Latest message (already limited by with)

            return [
                'id' => $chatUser->id,
                'chat_id' => $chat->id, // Correct chat ID
                'name' => $chatUser->name,
                'profile_image' => $chatUser->profile_image,
                'last_message' => $lastMessage ? $lastMessage->message : null,
            ];
        });

        // Remove duplicate users based on user ID
        $uniqueChatUsers = $chatUsers->unique('id')->values();

        return response()->json([
            'status' => 200,
            'data' => $uniqueChatUsers,
        ]);
    }

    /**
     * عرض الرسائل في شات معين.
     */
    public function getMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)
            ->orderBy('created_at', 'asc')
            ->get();

        return resourceApi::sendResponse(200, '', MessageResource::collection($messages));
    }

}
