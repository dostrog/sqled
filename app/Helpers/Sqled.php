<?php
declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class Sqled
{
    public static function isCorrectName(string $filename): bool
    {
        $pattern = '/^change_(\d){8}_.\.sql/';
        return preg_match($pattern, $filename) === 1;
    }

    public static function isFileWithErrorSql(string $filename): bool
    {
        $pattern = '/^change_(\d){8}_e\.sql/';
        return preg_match($pattern, $filename) === 1;
    }

    public static function makeCatalog(string $folder): Collection
    {
        $files = File::files($folder);

        $result = collect();

        collect($files)->each(function($file) use (&$result){
            $record = [];
            $fileName = $file->getFilename();
            if (! Sqled::isCorrectName($fileName)) {
                return;
            }


            $record['date'] = Str::substr($fileName, 7, 8);
            $record['fileName'] = $fileName;
            $record['pathName'] = $file->getPathName();
            $record['mTime'] = $file->getMTime();
            $record['withError'] = Sqled::isFileWithErrorSql($fileName);
            $result->push($record);
        });

        $result->sortBy([
            ['mTime', 'asc'],
            ['fileName', 'asc'],
        ]);

        return $result->groupBy('date')->sortKeys();
    }
}
