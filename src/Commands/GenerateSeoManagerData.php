<?php
declare(strict_types = 1);

namespace Krasov\SeoManager\Commands;

use Illuminate\Console\Command;
use Krasov\SeoManager\Traits\SeoManagerTrait;

/**
 * Class GenerateSeoManagerData
 *
 * @package Krasov\SeoManager\Commands
 */
class GenerateSeoManagerData extends Command
{
    use SeoManagerTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo-manager:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill Seo Manager database table with routes data';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Import Started');
        $this->importRoutes();
        $this->info('Import Finished');
    }
}
