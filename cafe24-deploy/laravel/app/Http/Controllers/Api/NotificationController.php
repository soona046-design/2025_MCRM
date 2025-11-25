<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * 현재 사용자의 알림 목록을 반환합니다.
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())
            ->with(['ticket'])
            ->orderBy('created_at', 'desc');

        // 읽지 않은 알림만 필터링
        if ($request->boolean('unread_only', false)) {
            $query->whereNull('read_at');
        }

        // 페이지네이션
        $notifications = $query->paginate($request->input('per_page', 15));

        return response()->json($notifications);
    }

    /**
     * 알림을 읽음 처리합니다.
     */
    public function markAsRead(string $notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => '알림이 읽음 처리되었습니다.']);
    }

    /**
     * 모든 알림을 읽음 처리합니다.
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => '모든 알림이 읽음 처리되었습니다.']);
    }

    /**
     * 알림을 삭제합니다.
     */
    public function destroy(string $notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $notificationId)
            ->firstOrFail();

        $notification->delete();

        return response()->json(['message' => '알림이 삭제되었습니다.']);
    }

    /**
     * 읽지 않은 알림 개수를 반환합니다.
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
