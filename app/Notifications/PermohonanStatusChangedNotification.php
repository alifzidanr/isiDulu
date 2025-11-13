<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Permohonan;

class PermohonanStatusChangedNotification extends Notification
{
    use Queueable;

    protected $permohonan;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(Permohonan $permohonan, $oldStatus, $newStatus)
    {
        $this->permohonan = $permohonan;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $statusEmoji = $this->getStatusEmoji($this->newStatus);
        $oldStatusText = $this->getStatusText($this->oldStatus);
        $newStatusText = $this->getStatusText($this->newStatus);
        $keluhan = $this->limitWords($this->permohonan->keluhan, 20);

        $message = "{$statusEmoji} *Status Permohonan Diperbarui*\n\n";
        $message .= "📋 ID: #{$this->permohonan->id_permohonan}\n";
        $message .= "👤 Pemohon: {$this->permohonan->nama_pemohon}\n";
        $message .= "🏢 Unit: {$this->permohonan->unit->nama_unit}\n";
        $message .= "📝 Keluhan: {$keluhan}\n";
        $message .= "📊 Status Lama: *{$oldStatusText}*\n";
        $message .= "📊 Status Baru: *{$newStatusText}*\n";
        $message .= "📅 Diperbarui: " . now()->format('d/m/Y H:i') . "\n";

        return TelegramMessage::create()
            ->content($message)
            ->disableNotification(false);
    }

    /**
     * Get status emoji
     */
    private function getStatusEmoji($status)
    {
        return match((int)$status) {
            0 => '📝',
            1 => '🔧',
            2 => '✅',
            3 => '📦',
            4 => '✔️',
            5 => '❌',
            default => '❓'
        };
    }

    /**
     * Get status text
     */
    private function getStatusText($status)
    {
        return match((int)$status) {
            0 => 'Permohonan',
            1 => 'Dikerjakan',
            2 => 'Selesai',
            3 => 'Diarsipkan',
            4 => 'Disahkan',
            5 => 'Dibatalkan',
            default => 'Unknown'
        };
    }

    /**
     * Limit text to specified number of words
     */
    private function limitWords($text, $limit = 20)
    {
        $words = explode(' ', $text);
        
        if (count($words) > $limit) {
            return implode(' ', array_slice($words, 0, $limit)) . '...';
        }
        
        return $text;
    }
}
