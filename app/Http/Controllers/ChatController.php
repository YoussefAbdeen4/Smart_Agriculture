<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Http\Traits\ApiTrait;
use App\Http\Traits\media;
use App\Models\AttachmentMessage;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use ApiTrait, AuthorizesRequests,media;

    /**
     * Get or create a chat between the user and another user.
     * If user is a Farmer, automatically targets their supervisor (engineer).
     */
    public function getOrCreateChat(Request $request): JsonResponse
    {
        $user = $request->user();
        $receiverId = $request->input('receiver_id');

        if (! $receiverId) {
            return $this->errorResponse(
                ['receiver_id' => ['Receiver ID is required for non-farmers']],
                'Invalid request',
                422
            );
        }

        // Find or create chat
        $chat = Chat::where(function ($query) use ($user, $receiverId) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($user, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $user->id);
        })->first();

        if (! $chat) {
            $chat = Chat::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
            ]);
        }

        $chat->load(['sender', 'receiver', 'messages' => function ($query) {
            $query->with('sender', 'attachments')->latest();
        }]);

        return $this->dataResponse(
            new ChatResource($chat),
            'Chat retrieved successfully',
            201
        );
    }

    /**
     * Display a listing of chats for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $chats = Chat::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id);
        })
            ->with([
                'sender',
                'receiver',
                'messages.sender',
                'messages.attachments'
            ])
            ->latest('created_at')
            ->get();

        return $this->dataResponse(
            ChatResource::collection($chats),
            'Chats retrieved successfully'
        );
    }

    /**
     * Send a message with attachment in a chat.
     */
    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        $this->authorize('sendMessage', $chat);

        $validated = $request->validate([
            'content' => ['required', 'string'],
            'attachments'   => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf,mp4', 'max:10240'],
        ]);

        $message = Message::create([
            'content' => $validated['content'],
            'sender_id' => $request->user()->id,
            'chat_id' => $chat->id,
        ]);

          // Store the attachment
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Store the file
                $fileName = $this->uploadPhoto($file, 'chats/attachments');
                // Create attachment record
                $message->attachments()->create([
                    'name' => $fileName,
                ]);
            }
        }

        $message->load('sender', 'attachments');

        return $this->dataResponse(
            new MessageResource($message),
            'Message with attachment sent successfully',
            201
        );
    }
}
