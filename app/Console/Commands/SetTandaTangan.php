<?php

namespace App\Console\Commands;

use App\Support\Penandatangan;
use Illuminate\Console\Command;

/** Set the signature printed on permohonan from a PNG file. */
class SetTandaTangan extends Command
{
    protected $signature = 'penandatangan:tanda-tangan {file : Path to a PNG (max 1 MB)}';

    protected $description = 'Set the signature printed bottom right on permohonan';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! is_file($file) || ! Penandatangan::storeImage((string) file_get_contents($file))) {
            $this->error('Not a PNG file of at most 1 MB.');

            return self::FAILURE;
        }

        $this->info('Signature saved for '.(Penandatangan::current()['nama'] ?? '-').'.');

        return self::SUCCESS;
    }
}
