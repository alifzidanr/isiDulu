<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;
use App\Models\Laporan;

class LaporanCreatedNotification extends Notification
{
    use Queueable;

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
        $message .= "👤 Pemohon: {$permohonan->nama_pemohon}\n";
        $message .= "🏢 Unit: {$permohonan->unit->nama_unit}\n";
        $message .= "📝 Keluhan: {$keluhan}\n";
        $message .= "🔧 Perangkat: {$this->laporan->jenisPerangkat->nama_perangkat}\n";
        $message .= "🛠 Perawatan: {$this->laporan->jenisPerawatan->nama_perawatan}\n";
        $message .= "📌 Detail: {$this->laporan->detail_perangkat}\n";

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
