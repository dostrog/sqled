<?php
declare(strict_types=1);

namespace App\Commands;

use App\Services\Seed;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class SeedCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'seed
    {--daily : Bundles will be created by day}
    {--baseDir= : Base working directory, "./assets" if not specified}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Execute SQL scripts from files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $descrAdd =  $this->option('daily') ? ' Daily bundled in advance.' : '';

        $this->newline();
        $this->info($this->description . $descrAdd);
        $this->newline();

        try {
            $seeder = new Seed($this->option('baseDir'));

            if ($seeder->isCatalogEmpty()) {

                $info = "There is no files to work with.";
                Log::info($info);
                $this->info($info);
                $this->newline();

                return 2;
            }

            $bar = $this->output->createProgressBar();

            $seeder->seed($this->option('daily'), $bar);

        } catch (Throwable $throwable) {
            $message = $throwable->getMessage();
            Log::error($message);
            $this->error($message);
        }
        $this->newline(2);
        return 0;
    }
}
