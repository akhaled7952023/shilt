<?php

namespace App\Exceptions\Import;

use RuntimeException;

class ImportHeaderException extends RuntimeException
{
    public function __construct(public readonly string $missingColumn)
    {
        parent::__construct("عمود إلزامي مفقود في الملف: {$missingColumn}");
    }
}
