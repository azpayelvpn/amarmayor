<?php

declare(strict_types=1);

namespace AmarMayor\Controllers\Web;

use AmarMayor\Auth\Auth;
use AmarMayor\Domain\Notifications\NotificationService;
use AmarMayor\Http\Request;
use AmarMayor\Http\Response;

class NotificationWebController
{
    private NotificationService $notificationService;

    public function __construct(?NotificationService $notificationService = null)
    {
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    /**
     * Display the user's notifications center.
     */
    public function index(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $userId = Auth::id();
        $unreadCount = $this->notificationService->getUnreadCount($userId);
        $notifications = $this->notificationService->getUserNotifications($userId, 50);

        if ($request->header('HX-Request') === 'true' && $request->query('partial') === 'dropdown') {
            return view('notifications/partials/dropdown_items', [
                'notifications' => array_slice($notifications, 0, 8),
                'unreadCount' => $unreadCount,
            ]);
        }

        return view('notifications/index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string|int $id = 0): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $id = (int)($id ?: $request->input('id', 0));
        $userId = Auth::id();

        if ($id > 0) {
            $this->notificationService->markAsRead($id, $userId);
        }

        if ($request->header('HX-Request') === 'true' || $request->isJson()) {
            return Response::json([
                'success' => true,
                'unread_count' => $this->notificationService->getUnreadCount($userId),
            ]);
        }

        $referer = $request->server('HTTP_REFERER');
        return Response::redirect($referer ?: '/notifications');
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllRead(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $userId = Auth::id();
        $this->notificationService->markAllAsRead($userId);

        if ($request->header('HX-Request') === 'true' || $request->isJson()) {
            return Response::json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        $referer = $request->server('HTTP_REFERER');
        return Response::redirect($referer ?: '/notifications');
    }

    /**
     * Get live unread count (for HTMX / polling).
     */
    public function unreadCount(Request $request): Response
    {
        if (!Auth::check()) {
            return Response::json(['unread_count' => 0]);
        }

        $count = $this->notificationService->getUnreadCount(Auth::id());
        
        if ($request->header('HX-Request') === 'true' && $request->query('badge') === '1') {
            if ($count > 0) {
                return Response::html('<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $count . '</span>');
            }
            return Response::html('');
        }

        return Response::json(['unread_count' => $count]);
    }
}
