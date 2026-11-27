<?php

namespace App\Support;

use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Who a permohonan belongs to: users whose kampus is the permohonan unit's
 * kampus, whose unit is its unit, or whose sub unit is its sub unit. The same
 * rule decides what those users see (visibleTo) and who gets its Telegram
 * notifications (notify).
 */
class PermohonanAudience
{
    /**
     * Limit a permohonan query to what $user is assigned to. Accounts with no
     * kampus, unit or sub unit see everything (read-only, see User::isReadOnly()).
     */
    public static function visibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNoPlacement() && $user->id_sub_unit === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->whereRaw('1 = 0');
            if ($user->id_kampus !== null) {
                $q->orWhereHas('unit', fn (Builder $u) => $u->where('id_kampus', $user->id_kampus));
            }
            if ($user->id_unit !== null) {
                $q->orWhere('id_unit', $user->id_unit);
            }
            if ($user->id_sub_unit !== null) {
                $q->orWhere('id_sub_unit', $user->id_sub_unit);
            }
        });
    }

    /** Users assigned to this permohonan's kampus, unit or sub unit. */
    public static function usersFor(Permohonan $permohonan): Collection
    {
        $permohonan->loadMissing('unit');
        $kampusId = $permohonan->unit?->id_kampus;

        return User::query()
            ->where(function (Builder $q) use ($permohonan, $kampusId) {
                $q->where('id_unit', $permohonan->id_unit);
                if ($kampusId !== null) {
                    $q->orWhere('id_kampus', $kampusId);
                }
                if ($permohonan->id_sub_unit !== null) {
                    $q->orWhere('id_sub_unit', $permohonan->id_sub_unit);
                }
            })
            ->get();
    }

    /**
     * Send $notification to the Telegram group and to every assigned user
     * with a telegram_id (each chat once).
     */
    public static function notify(Permohonan $permohonan, $notification): void
    {
        $groupChatId = (string) config('services.telegram-bot-api.group_chat_id');
        $chatIds = $groupChatId !== '' ? [$groupChatId] : [];

        try {
            foreach (self::usersFor($permohonan) as $user) {
                $id = trim((string) $user->telegram_id);
                if ($id !== '' && ! in_array($id, $chatIds, true)) {
                    $chatIds[] = $id;
                }
            }
        } catch (Throwable $e) {
            Log::error('Telegram: could not find the users for a permohonan', ['permohonan_id' => $permohonan->id_permohonan, 'error' => $e->getMessage()]);
        }

        foreach ($chatIds as $chatId) {
            try {
                Notification::route('telegram', $chatId)->notify($notification);
            } catch (Throwable $e) {
                Log::error('Telegram notification failed', ['permohonan_id' => $permohonan->id_permohonan, 'chat_id' => $chatId, 'error' => $e->getMessage()]);
            }
        }
    }
}
