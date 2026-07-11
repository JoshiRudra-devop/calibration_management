-- ============================================================
--  Migration 001: Soft Delete + Audit Log
--  Run once in phpMyAdmin > Import, or via mysql CLI:
--    mysql -u root shreeji_instruments < 001_soft_delete_audit_log.sql
-- ============================================================

USE shreeji_instruments;

-- ── 1. Add soft-delete column to certificates ─────────────
ALTER TABLE certificates
  ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL,
  ADD INDEX IF NOT EXISTS idx_cert_deleted (deleted_at);

-- ── 2. Certificate audit log ──────────────────────────────
CREATE TABLE IF NOT EXISTS certificate_audit_log (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  certificate_id INT UNSIGNED NOT NULL,
  action        ENUM('create','update','delete','restore') NOT NULL,
  changed_by    INT UNSIGNED,
  changed_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  snapshot      LONGTEXT,                         -- JSON snapshot of cert row before change
  FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE,
  FOREIGN KEY (changed_by)     REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_cert (certificate_id),
  INDEX idx_audit_time (changed_at)
) ENGINE=InnoDB;
