<?php
// ============================================================
//  Audit Log Helper
//  Usage: logCertificateAudit($db, $certId, 'create'|'update'|'delete'|'restore', $userId, $snapshot)
// ============================================================

function logCertificateAudit(PDO $db, int $certId, string $action, ?int $userId, ?array $snapshot = null): void {
    try {
        $db->prepare(
            "INSERT INTO certificate_audit_log (certificate_id, action, changed_by, snapshot)
             VALUES (?, ?, ?, ?)"
        )->execute([
            $certId,
            $action,
            $userId,
            $snapshot ? json_encode($snapshot) : null,
        ]);
    } catch (\Throwable $e) {
        error_log('Audit log failed: ' . $e->getMessage());
    }
}
