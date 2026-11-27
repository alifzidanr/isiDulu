<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Laporan;

use App\Notifications\Concerns\EscapesTelegramMarkdown;

class LaporanCreatedNotification extends Notification
{
    use Queueable, EscapesTelegramMarkdown;

    protected $laporan;

    public function __construct(Laporan $laporan)
    {
        $this->laporan = $laporan;
    }

    public function via($notifiable)
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable)
    {
        $permohonan = $this->laporan->permohonan;
        $keluhan = $this->limitWords($permohonan->keluhan, 20);
        
        $message = "📄 *Laporan Dibuat*\n\n";
        $message .= "📋 ID Permohonan: #{$permohonan->id_permohonan}\n";
        $message .= "👤 Pemohon: {$this->md($permohonan->nama_pemohon)}\n";
        $message .= "🏢 Unit: {$this->md($permohonan->unit?->nama_unit)}\n";
        $message .= "📝 Keluhan: {$this->md($keluhan)}\n";
        $message .= "🔧 Perangkat: {$this->md($this->laporan->jenisPerangkat?->nama_perangkat)}\n";
        $message .= "🛠 Perawatan: {$this->md($this->laporan->jenisPerawatan?->nama_perawatan)}\n";
        $message .= "📌 Detail: {$this->md($this->laporan->detail_perangkat)}\n";

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
