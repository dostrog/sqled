<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use App\Helpers\Sqled;
use Phar;
use RuntimeException;

class Polygon
{
    private string $changeFolder = '/change';
    private string $examplesFolder = '/examples-sql';
    private string $baseDir;

    public function __construct(string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? getcwd() . '/assets';
    }

    public function populate(bool $onlyCorrect, $bar = null): int
    {
        if (config('database.default') === 'sqlite') {
            if (!File::exists(config('database.connections.sqlite.database'))) {
                File::makeDirectory($this->baseDir, 0755, true, true);
                File::put(config('database.connections.sqlite.database'), '');
            }
            File::put(config('database.connections.sqlite.database'), '');
        }

        File::deleteDirectory($this->baseDir . $this->changeFolder);
        File::makeDirectory($this->baseDir . $this->changeFolder . '/applied', 0755, true);

        $exampleCatalog = Sqled::makeCatalog(Phar::running()
                ? 'phar://' . getcwd() . '/sqled/database' . $this->examplesFolder
                : getcwd() . '/database' . $this->examplesFolder
        );

        if ($exampleCatalog->isEmpty()) {
            throw new RuntimeException('No any templates SQL files in ' . $this->baseDir . $this->examplesFolder);
        }

        return $this->populateFiles($exampleCatalog, $onlyCorrect, $bar);
    }

    public function populateFiles(Collection $catalog, bool $onlyCorrect, $bar = null): int
    {
        $quantity = 0;

        $destDir = $this->baseDir . $this->changeFolder;

        $catalog->each(function($group) use ($onlyCorrect, $destDir, &$quantity) {
            $group->each(function ($file) use ($onlyCorrect, $destDir, &$quantity) {
                if ($onlyCorrect && $file['withError']) {
                    return;
                }
                File::copy($file['pathName'], $destDir . '/' . $file['fileName']);
                $quantity++;
            });
        });

        return $quantity;
    }
}
