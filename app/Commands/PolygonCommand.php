<?php
declare(strict_types=1);

namespace App\Commands;

use App\Services\Polygon;
use LaravelZero\Framework\Commands\Command;
use Throwable;

class PolygonCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'polygon
{--onlyCorrect : Use correct SQL scripts only ("./assets/change/")}
{--baseDir= : Base working directory, "./assets" if not specified}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create set of SQL-script files for testing ("./assets/change")';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->newline();
        $this->info($this->description . '...');
        $this->newline();

        try {
            $polygon = new Polygon($this->option('baseDir'));

            $bar = $this->output->createProgressBar();

            $quantity = $polygon->populate($this->option('onlyCorrect'), $bar);

            $this->info("File populated: {$quantity}");

            $this->newLine();
        } catch (Throwable $th) {
            $this->error($th->getMessage());

            return 1;
        }

        $this->newLine();

        return 0;
    }
}
