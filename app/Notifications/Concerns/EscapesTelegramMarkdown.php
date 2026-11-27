<?php

namespace App\Notifications\Concerns;

/**
 * Messages are sent with Telegram's (legacy) Markdown. Anything typed by a
 * user goes through md(): otherwise a stray *, _, ` or [ makes Telegram
 * reject the whole message, and [text](url) would plant a disguised link.
 */
trait EscapesTelegramMarkdown
{
    protected function md(mixed $text): string
    {
        return str_replace(['\\', '_', '*', '`', '['], ['\\\\', '\\_', '\\*', '\\`', '\\['], (string) $text);
    }
}
