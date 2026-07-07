<?php

namespace App\Exceptions\Import;

use RuntimeException;

class ImportDuplicateOrderException extends RuntimeException
{
    public function __construct(public readonly string $orderId, public readonly int $row)
    {
        parent::__construct("رقم الطلب مكرر داخل الملف: {$orderId} (الصف {$row})");
    }
}
