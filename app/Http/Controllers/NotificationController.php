<?php

namespace App\Http\Controllers;

use App\Notifications\TaskCommented;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Latest bell items + unread count (polled by the bell). */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->notifications()
            ->where('type', TaskCommented::class)
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($n) => [
                'id'    => $n->id,
                'title' => $n->data['title'] ?? 'New supervisor comment',
                'body'  => $n->data['body'] ?? '',
                'time'  => $n->created_at?->diffForHumans(),
                'read'  => $n->read_at !== null,
                'url'   => route('notifications.open', $n->id),
            ])
            ->values();

        $unread = $user->unreadNotifications()
            ->where('type', TaskCommented::class)
            ->count();

        return response()->json(['items' => $items, 'unread' => $unread]);
    }

    /** Mark one notification read, then send the user to the task it points at. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('staff.tasks'));
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()
            ->where('type', TaskCommented::class)
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
