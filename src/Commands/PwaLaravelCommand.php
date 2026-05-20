<?php

namespace SlFomin\PwaLaravel\Commands;

use Illuminate\Console\Command;

class PwaLaravelCommand extends Command
{
    public $signature = 'pwa-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
