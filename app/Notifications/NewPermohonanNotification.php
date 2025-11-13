<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Permohonan;
use Illuminate\Support\Str;

class NewPermohonanNotification extends Notification
{
    use Queueable;

    protected $permohonan;

    public function __construct(Permohonan $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $keluhan = $this->limitWords($this->permohonan->keluhan, 20);
        
        $message = "🆕 *Permohonan Baru*\n\n";
        $message .= "📋 ID: #{$this->permohonan->id_permohonan}\n";
        $message .= "👤 Pemohon: {$this->permohonan->nama_pemohon}\n";
        $message .= "🏢 Unit: {$this->permohonan->unit->nama_unit}\n";
        $message .= "📞 Kontak: {$this->permohonan->kontak_pemohon}\n";
        $message .= "📝 Keluhan: {$keluhan}\n";
        $message .= "📅 Tanggal: {$this->permohonan->tanggal->format('d/m/Y H:i')}\n";

        return TelegramMessage::create()
            ->content($message)
            ->disableNotification(false);
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
