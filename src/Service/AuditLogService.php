<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\AuditLogsTable;

class AuditLogService
{
    public function __construct(
        private AuditLogsTable $auditLogsTable,
    ) {
    }

    public function log(
        ?int $apiTokenId,
        string $entityType,
        ?int $entityId,
        string $action,
        string $result,
        ?array $payload = null,
    ): void {
        $entry = $this->auditLogsTable->newEntity([
            'api_token_id' => $apiTokenId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'result' => $result,
            'payload' => $payload !== null ? json_encode($payload) : null,
        ]);

        $this->auditLogsTable->saveOrFail($entry);
    }
}
