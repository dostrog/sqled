<?php
declare(strict_types=1);

namespace App\Services;
use App\Helpers\Sqled;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class Seed
{
    public const BUNDLE_TEMPLATE = 'change_bundle_%s.sql';

    private string $changeFolder = '/change';
    private string $baseDir;
    private Collection $catalog;

    public function __construct(string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? getcwd() . '/assets';
        $this->catalog = Sqled::makeCatalog($this->baseDir . $this->changeFolder);
    }

    public function isCatalogEmpty(): bool
    {
        return $this->catalog->isEmpty();
    }

    public function seed(bool $daily = false, $bar = null): void
    {
        if ($this->isCatalogEmpty()) {
            return;
        }

        $quantity = 0;
        $this->catalog->each(function($group) use(&$quantity) {
            $quantity += $group->count();
        });

        if ($daily) {
            $this->seedByDay($quantity, $bar);

            return;
        }

        $this->seedAll($quantity, $bar);
    }

    private function makeBundle($bundle, $group, $daily = true): void
    {
        $destName = $this->baseDir . $this->changeFolder . '/' . $bundle;

        if ($daily) {
            File::delete($destName);
        }

        $group->each(function($file) use ($destName) {
            $startComment = sprintf("-- Start of content %s%s", $file, PHP_EOL);
            $endComment = sprintf("-- End of content %s%s", $file, PHP_EOL);
            $src = fopen($file, 'rb');

            file_put_contents($destName, $startComment, FILE_APPEND);
            file_put_contents($destName, $src, FILE_APPEND);
            file_put_contents($destName, $endComment, FILE_APPEND);

            fclose($src);
        });
    }

    private function seedFromFile(string $bundle, $bar = null): void
    {
        $bundle = $this->baseDir . $this->changeFolder . '/' . $bundle;
        $sql = File::get($bundle);

        // There is DB::transaction($callback) with automatic rollback/commit
        //

        DB::beginTransaction();
        Log::info('Start transaction.', ['bundle' => $bundle]);

        try {
            if ($bar !== null) {
                $bar->advance();
            }

            DB::unprepared($sql);

            Log::info('Run SQL from bundle', ['bundle' => $bundle]);

        } catch (Throwable $throwable) {
            DB::rollBack();

            Log::error('Rollback transaction.', ['bundle' => $bundle]);
            $errorMessage = "Error occurred whilst execute SQL from {$bundle} ";
            Log::error($errorMessage);
            throw new RuntimeException($errorMessage . $throwable->getMessage());
        }
        Log::info('Commit transaction.', ['bundle' => $bundle]);
        DB::commit();
    }

    private function cleanBundle(string $bundle, string $applied, Collection $group): void
    {
        $bundlePath = $this->baseDir . $this->changeFolder . '/' . $bundle;
        $appliedFolder = $this->baseDir . $this->changeFolder . '/applied' . '/' . $applied;

        Log::info('Create applied folder', ['applied' => $applied]);
        File::makeDirectory($appliedFolder, 0755, true);

        try {
            Log::info('Move files to applied.');
            $group->each(fn($file, $key) => File::move(
                $this->baseDir . $this->changeFolder . '/' . $file,
                $appliedFolder . '/' . $file
            ));

            Log::info('Delete applied bundle', ['bundle' => $bundlePath]);
            File::delete($bundlePath);
        } catch (Throwable $throwable) {
            throw new RuntimeException("Error occurred whilst cleaning up bundle {$bundle} : " . $throwable->getMessage());
        }
    }

    private function seedByDay(int $quantity, $bar = null): void
    {
        if ($bar !== null) {
            $bar->setMaxSteps($quantity);
            $bar->start();
        }

        $this->catalog->each(function ($group, $key) use ($bar){
            $bundle = sprintf(self::BUNDLE_TEMPLATE, $key);
            $this->makeBundle($bundle, $group->pluck('pathName'));
            $this->seedFromFile($bundle, $bar);
            $this->cleanBundle($bundle, (string)$key, $group->pluck('fileName'));
        });

        if ($bar !== null) {
            $bar->finish();
        }
    }

    private function seedAll(int $quantity, $bar = null): void
    {
        if ($bar !== null) {
            $bar->setMaxSteps($quantity);
            $bar->start();
        }

        $bundle = sprintf(self::BUNDLE_TEMPLATE, 'all');
        File::delete($this->baseDir . $this->changeFolder . '/' . $bundle);

        $this->catalog->each(fn($group) => $this->makeBundle($bundle, $group->pluck('pathName'), false));

        $this->seedFromFile($bundle, $bar);

        $bundlePath = $this->baseDir . $this->changeFolder . '/' . $bundle;

        try {
            Log::info('Move files to applied.');

            $this->catalog->each(function ($group, $key) {
                $appliedFolder = $this->baseDir . $this->changeFolder . '/applied' . '/' . $key;
                Log::info('Create applied folder', ['applied' => $key]);
                File::makeDirectory($appliedFolder, 0755, true);
                $group->each(function($file) use ($appliedFolder) {
                    File::move($file['pathName'], $appliedFolder . '/' . $file['fileName']);
                });
            });

            Log::info('Delete applied bundle', ['bundle' => $bundlePath]);
            File::delete($bundlePath);
        } catch (Throwable $throwable) {
            throw new RuntimeException("Error occurred whilst cleaning up bundle {$bundle} : " . $throwable->getMessage());
        }

        File::delete($this->baseDir . $this->changeFolder . '/' . $bundle);

        if ($bar !== null) {
            $bar->finish();
        }
    }
}
