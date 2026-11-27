<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Rows per page for every paginated table, picked with the "Baris" select at
 * the end of the pagination footer (?per_page=). Defaults to 25.
 */
class PerPage
{
    public const OPTIONS = [10, 25, 50, 100];

    public const DEFAULT = 25;

    public static function get(?Request $request = null): int
    {
        $value = (int) ($request ?? request())->query('per_page', self::DEFAULT);

        return in_array($value, self::OPTIONS, true) ? $value : self::DEFAULT;
    }
}
