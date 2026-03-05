<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Service for writing audit log entries.
 */
class AuditLogService
{
    use LocatorAwareTrait;

    /**
     * Log an upsert operation.
     *
     * @param int|null $apiTokenId The API token ID.
     * @param string $entityType The entity type (e.g., 'applicant_job').
     * @param int|null $entityId The entity ID.
     * @param string $action The action performed (e.g., 'upsert').
     * @param string $result The result (created, updated, noop).
     * @param array<string, mixed>|null $payload Optional payload data.
     * @return void
     */
    public function log(
        ?int $apiTokenId,
        string $entityType,
        ?int $entityId,
        string $action,
        string $result,
        ?array $payload = null,
    ): void {
        $auditLogsTable = $this->fetchTable('AuditLogs');

        $entry = $auditLogsTable->newEntity([
            'api_token_id' => $apiTokenId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'result' => $result,
            'payload' => $payload !== null ? json_encode($payload) : null,
        ]);

        $auditLogsTable->saveOrFail($entry);
    }
}
