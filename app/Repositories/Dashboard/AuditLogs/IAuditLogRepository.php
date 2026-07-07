<?php

namespace App\Repositories\Dashboard\AuditLogs;

interface IAuditLogRepository
{
    public function getAll(array $filters = []);

    public function getById($id);

    public function create(array $data);

    public function getForModel(string $modelType, int $modelId);
}
