<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;

class ChatPolicy
{
    /**
     * Determine if the user can view any chats.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the chat.
     */
    public function view(User $user, Chat $chat): bool
    {
        // Only participants of the chat can view it
        return $chat->sender_id === $user->id || $chat->receiver_id === $user->id;
    }

    /**
     * Determine if the user can create a chat.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can send a message in the chat.
     */
    public function sendMessage(User $user, Chat $chat): bool
    {
        // Only participants can send messages
        return $chat->sender_id === $user->id || $chat->receiver_id === $user->id;
    }
}
