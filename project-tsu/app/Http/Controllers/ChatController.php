<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get the list of contacts (other users) to chat with.
     * We'll fetch Admin, Dekan, Kaprodi, Sekprodi.
     */
    public function getContacts()
    {
        $currentUserId = Auth::id();

        // Get users that are relevant for chatting (exclude mahasiswa/dosen if needed, or just allow all)
        // Here we just get users with roles: admin, dekan, kaprodi, sekretaris prodi.
        $contacts = User::with('role')
            ->where('id_user', '!=', $currentUserId)
            ->whereHas('role', function($q) {
                $q->whereIn('nama_role', ['Admin', 'Dekan', 'Kaprodi', 'Sekretaris Prodi']);
            })
            ->get();

        // Fetch ALL messages related to the current user in one query
        $messages = Message::where('sender_id', $currentUserId)
            ->orWhere('receiver_id', $currentUserId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate unread count per contact (where we are the receiver and it's unread)
        $unreadCounts = $messages->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->map->count();

        // Get latest message per contact and group all messages for instant loading
        $latestMessages = [];
        $messagesByContact = [];
        
        // Sort descending for latest, but for full messages we need ascending order
        $messagesAsc = $messages->sortBy('created_at')->values();
        
        foreach($messagesAsc as $msg) {
            $otherId = $msg->sender_id === $currentUserId ? $msg->receiver_id : $msg->sender_id;
            if(!isset($messagesByContact[$otherId])) {
                $messagesByContact[$otherId] = [];
            }
            $messagesByContact[$otherId][] = [
                'id' => $msg->id,
                'text' => $msg->message,
                'is_mine' => $msg->sender_id === $currentUserId,
                'time' => $msg->created_at->format('H:i'),
                'is_read' => $msg->is_read
            ];
        }

        foreach($messages as $msg) {
            $otherId = $msg->sender_id === $currentUserId ? $msg->receiver_id : $msg->sender_id;
            if(!isset($latestMessages[$otherId])) {
                $latestMessages[$otherId] = $msg;
            }
        }

        $contactsData = $contacts->map(function($user) use ($unreadCounts, $latestMessages, $messagesByContact) {
            $otherId = $user->id_user;
            $unreadCount = $unreadCounts->get($otherId, 0);
            $lastMessage = $latestMessages[$otherId] ?? null;

            return [
                'id' => $otherId,
                'name' => $user->nama_user ?? $user->username,
                'role' => $user->role->nama_role ?? 'User',
                'unread_count' => $unreadCount,
                'last_message' => $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->message, 30) : null,
                'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                'last_message_raw_time' => $lastMessage ? $lastMessage->created_at->timestamp : 0,
                'messages' => $messagesByContact[$otherId] ?? [], // Embed messages directly for instant load
            ];
        });

        // Separate users with chat history and all users
        $chatHistory = $contactsData->whereNotNull('last_message')->sortByDesc('last_message_raw_time')->values();
        $allUsers = $contactsData->sortBy('name')->values();

        return response()->json([
            'success' => true,
            'contacts' => $chatHistory,
            'all_users' => $allUsers
        ]);
    }

    /**
     * Get messages between current user and specified user
     */
    public function getMessages($userId)
    {
        $currentUserId = Auth::id();

        $messages = Message::with(['sender', 'receiver'])
            ->where(function($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
            })
            ->orWhere(function($q) use ($currentUserId, $userId) {
                $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $messagesData = $messages->map(function($msg) use ($currentUserId) {
            return [
                'id' => $msg->id,
                'text' => $msg->message,
                'is_mine' => $msg->sender_id === $currentUserId,
                'time' => $msg->created_at->format('H:i'),
                'is_read' => $msg->is_read
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $messagesData
        ]);
    }

    /**
     * Send a new message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:user,id_user',
            'message' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'is_read' => false
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'text' => $message->message,
                'is_mine' => true,
                'time' => $message->created_at->format('H:i'),
                'is_read' => false
            ]
        ]);
    }

    /**
     * Mark messages from a specific user as read
     */
    public function markAsRead($userId)
    {
        $currentUserId = Auth::id();

        Message::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a specific message
     */
    public function deleteMessage($id)
    {
        $message = Message::where('id', $id)
            ->where(function($q) {
                $q->where('sender_id', Auth::id())
                  ->orWhere('receiver_id', Auth::id());
            })
            ->first();

        if ($message) {
            $message->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Not authorized or not found'], 403);
    }

    /**
     * Delete entire conversation with a specific user
     */
    public function clearChat($userId)
    {
        $currentUserId = Auth::id();
        
        Message::where(function($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $userId);
        })->orWhere(function($q) use ($currentUserId, $userId) {
            $q->where('sender_id', $userId)->where('receiver_id', $currentUserId);
        })->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get total unread messages count for the chat icon badge
     */
    public function getUnreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }
}
