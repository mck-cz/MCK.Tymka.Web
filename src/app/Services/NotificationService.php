<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Create notification records for one or more users.
     */
    public static function send(string|array $userIds, string $type, string $message, string $channel = 'app'): void
    {
        if (is_string($userIds)) {
            $userIds = [$userIds];
        }

        $now = now();

        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'channel' => $channel,
                'payload' => ['message' => $message],
                'sent_at' => $now,
            ]);
        }
    }
}
