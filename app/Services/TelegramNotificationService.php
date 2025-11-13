<?php

namespace App\Services;

use Illuminate\Support\Facades\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    /**
     * Send notification to Telegram group and user if available
     *
     * @param string $message
     * @param \App\Models\User|null $user
     * @param array $additionalData
     * @return void
     */
    public static function send(string $message, $user = null, array $additionalData = [])
    {
        try {
            // Always send to group
            self::sendToGroup($message);

            // Send to user if telegram_id is available
            if ($user && !empty($user->telegram_id)) {
                self::sendToUser($user->telegram_id, $message);
            }
        } catch (\Exception $e) {
            // Silently log error without showing to user
            Log::error('Telegram notification failed', [
                'message' => $message,
                'user_id' => $user?->id_user,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send to Telegram group
     */
    private static function sendToGroup(string $message)
    {
        try {
            $groupChatId = config('services.telegram-bot-api.group_chat_id');
            
            Notification::route('telegram', $groupChatId)
                ->notify(new \App\Notifications\TelegramNotification($message));
        } catch (\Exception $e) {
            Log::error('Failed to send to Telegram group', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send to individual user
     */
    private static function sendToUser(string $telegramId, string $message)
    {
        try {
            Notification::route('telegram', $telegramId)
                ->notify(new \App\Notifications\TelegramNotification($message));
        } catch (\Exception $e) {
            Log::error('Failed to send to Telegram user', [
                'telegram_id' => $telegramId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
