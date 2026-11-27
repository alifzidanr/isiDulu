<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Permohonan;
use Illuminate\Support\Str;

use App\Notifications\Concerns\EscapesTelegramMarkdown;

class NewPermohonanNotification extends Notification
{
    use Queueable, EscapesTelegramMarkdown;

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
        $message .= "👤 Pemohon: {$this->md($this->permohonan->nama_pemohon)}\n";
        $message .= "🏢 Unit: {$this->md($this->permohonan->unit?->nama_unit)}\n";
        $message .= "📞 Kontak: {$this->md($this->permohonan->kontak_pemohon)}\n";
        $message .= "📝 Keluhan: {$this->md($keluhan)}\n";
        // tanggal is a DATE column (no time, so it always read 00:00);
        // created_at holds when the request actually came in.
        $masuk = $this->permohonan->created_at ?? now();
        $message .= "📅 Tanggal: {$masuk->format('d/m/Y H:i')}\n";

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
