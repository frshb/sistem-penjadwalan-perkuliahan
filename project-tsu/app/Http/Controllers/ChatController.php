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

        $chatUserIds = \App\Models\Message::where('sender_id', $currentUserId)
            ->orWhere('receiver_id', $currentUserId)
            ->select('sender_id', 'receiver_id')
            ->get()
            ->flatMap(function ($msg) use ($currentUserId) {
                return [$msg->sender_id, $msg->receiver_id];
            })
            ->unique()
            ->reject(fn($id) => $id == $currentUserId);

        // Get users that are relevant for chatting (admin, dekan, dll) ATAU yang sudah punya history chat
        $contacts = User::with('role')
            ->where('id_user', '!=', $currentUserId)
            ->where(function($q) use ($chatUserIds) {
                $q->whereHas('role', function($roleQuery) {
                    $roleQuery->whereIn('nama_role', ['Admin', 'Dekan', 'Kaprodi', 'Sekretaris Prodi']);
                })->orWhereIn('id_user', $chatUserIds);
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

            // Set timezone to Asia/Jakarta
            $msgTime = $msg->created_at->timezone('Asia/Jakarta');
            
            // Format label for the date divider
            if ($msgTime->isToday()) {
                $dateLabel = 'Hari ini';
            } elseif ($msgTime->isYesterday()) {
                $dateLabel = 'Kemarin';
            } else {
                // e.g. 10 Ags 2026
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                $dateLabel = $msgTime->format('d') . ' ' . $months[$msgTime->format('n') - 1] . ' ' . $msgTime->format('Y');
            }

            $isEdited = $msg->updated_at->diffInSeconds($msg->created_at) > 1;

            $messagesByContact[$otherId][] = [
                'id' => $msg->id,
                'text' => $msg->message,
                'is_mine' => $msg->sender_id === $currentUserId,
                'time' => $msgTime->format('H:i'),
                'date' => $msgTime->format('Y-m-d'),
                'date_label' => $dateLabel,
                'is_read' => $msg->is_read,
                'timestamp' => $msg->created_at->timestamp * 1000,
                'is_edited' => $isEdited
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
            $msgTime = $msg->created_at->timezone('Asia/Jakarta');
            if ($msgTime->isToday()) {
                $dateLabel = 'Hari ini';
            } elseif ($msgTime->isYesterday()) {
                $dateLabel = 'Kemarin';
            } else {
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                $dateLabel = $msgTime->format('d') . ' ' . $months[$msgTime->format('n') - 1] . ' ' . $msgTime->format('Y');
            }

            $isEdited = $msg->updated_at->diffInSeconds($msg->created_at) > 1;

            return [
                'id' => $msg->id,
                'text' => $msg->message,
                'is_mine' => $msg->sender_id === $currentUserId,
                'time' => $msgTime->format('H:i'),
                'date' => $msgTime->format('Y-m-d'),
                'date_label' => $dateLabel,
                'is_read' => $msg->is_read,
                'timestamp' => $msg->created_at->timestamp * 1000,
                'is_edited' => $isEdited
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

        \App\Models\Notification::create([
            'user_id'     => $message->receiver_id,
            'role_target' => null,
            'judul'       => '💬 Pesan Baru',
            'pesan'       => (Auth::user()->username ?? 'Seseorang') . ' telah mengirimkan Anda sebuah pesan baru di Obrolan.',
            'tipe'        => 'info',
            'is_read'     => false,
        ]);

        $msgTime = $message->created_at->timezone('Asia/Jakarta');
        if ($msgTime->isToday()) {
            $dateLabel = 'Hari ini';
        } elseif ($msgTime->isYesterday()) {
            $dateLabel = 'Kemarin';
        } else {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            $dateLabel = $msgTime->format('d') . ' ' . $months[$msgTime->format('n') - 1] . ' ' . $msgTime->format('Y');
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'text' => $message->message,
                'is_mine' => true,
                'time' => $msgTime->format('H:i'),
                'date' => $msgTime->format('Y-m-d'),
                'date_label' => $dateLabel,
                'is_read' => false,
                'timestamp' => $message->created_at->timestamp * 1000,
                'is_edited' => false
            ]
        ]);
    }

    /**
     * Update an existing message
     */
    public function updateMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = Message::where('id', $id)->where('sender_id', Auth::id())->first();

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Pesan tidak ditemukan atau Anda tidak berhak mengeditnya'], 403);
        }

        if ($message->created_at->diffInMinutes(now()) > 30) {
            return response()->json(['success' => false, 'message' => 'Batas waktu edit pesan (30 menit) telah habis'], 403);
        }

        $message->update([
            'message' => $request->message,
            'is_read' => false
        ]);

        \App\Models\Notification::create([
            'user_id'     => $message->receiver_id,
            'role_target' => null,
            'judul'       => '💬 Pesan Diperbarui',
            'pesan'       => (Auth::user()->username ?? 'Seseorang') . ' telah mengubah pesan yang dikirimkan kepada Anda di Obrolan.',
            'tipe'        => 'info',
            'is_read'     => false,
        ]);

        return response()->json([
            'success' => true,
            'text' => $message->message
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

        // Also mark corresponding system notifications as read
        $sender = User::find($userId);
        if ($sender) {
            $senderName = $sender->nama_user ?? $sender->username ?? '';
            if ($senderName) {
                \App\Models\Notification::where('user_id', $currentUserId)
                    ->where('is_read', false)
                    ->where(function($q) {
                        $q->where('judul', 'like', '%Pesan%')
                          ->orWhere('judul', 'like', '%Obrolan%');
                    })
                    ->where('pesan', 'like', '%' . $senderName . '%')
                    ->update(['is_read' => true]);
            }
        }

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

        // Also fetch system notifications unread count for the same user
        $currentUser = Auth::user();
        $currentRole = strtolower($currentUser->role->nama_role ?? '');

        $systemCount = \App\Models\Notification::where(function($q) use ($currentUser, $currentRole) {
            $q->where('user_id', $currentUser->id_user)
              ->orWhere('role_target', $currentRole)
              ->orWhereNull('role_target');
        })
        ->where(function($q) use ($currentUser) {
            $q->whereNull('deleted_by')
              ->orWhereJsonDoesntContain('deleted_by', $currentUser->id_user);
        })
        ->where('is_read', false)
        ->count();

        return response()->json([
            'success' => true,
            'count' => $count,
            'system_count' => $systemCount
        ]);
    }
}
