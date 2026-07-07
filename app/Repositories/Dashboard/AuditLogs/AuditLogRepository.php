<?php

namespace App\Repositories\Dashboard\AuditLogs;

use App\Models\AuditLog;

class AuditLogRepository implements IAuditLogRepository
{
    public function getAll(array $filters = [])
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getById($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function create(array $data)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getForModel(string $modelType, int $modelId)
    {
        throw new \RuntimeException('Not implemented');
    }
}
