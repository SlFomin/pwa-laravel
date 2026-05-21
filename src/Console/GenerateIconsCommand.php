<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use SlFomin\PwaLaravel\Contracts\IconGenerator;
use SlFomin\PwaLaravel\Events\IconsGenerated;
use Throwable;

final class GenerateIconsCommand extends Command
{
    protected $signature = 'pwa:generate-icons
        {source? : Path to source image (default: from config)}
        {--output= : Output directory (default: from config)}
        {--dry-run : Validate source without writing files}';

    protected $description = 'Generate PWA icon set from source image';

    public function handle(IconGenerator $generator, Dispatcher $events): int
    {
        $source = $this->argument('source') ?? config('pwa.icons.source');
        $output = $this->option('output') ?? config('pwa.icons.output_path');

        if (! is_string($source) || ! is_string($output)) {
            $this->error('Source or output path is not configured.');

            return self::FAILURE;
        }

        $this->line("Source: <info>{$source}</info>");
        $this->line("Output: <info>{$output}</info>");

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no files will be written.');
            try {
                $generator->validateSource($source);
                $this->info('Source image is valid.');
            } catch (Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        try {
            $icons = $generator->generate($source, $output);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $events->dispatch(new IconsGenerated($source, $output, $icons));

        $this->info('Generated '.count($icons).' icons.');
        $this->table(
            ['Size', 'Path', 'Purpose'],
            array_map(
                fn (array $i): array => [$i['sizes'], $i['src'], $i['purpose'] ?? 'any'],
                $icons,
            ),
        );

        $this->newLine();
        $this->line('<fg=yellow>Tip:</> Add generated icons to manifest "icons" array if using dynamic driver,');
        $this->line('or rebuild Vite to pick them up in static mode.');

        return self::SUCCESS;
    }
}
