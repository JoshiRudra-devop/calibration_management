-- ============================================================
--  Migration 002: CTM Proving Ring Calibration Standards
--  Run once:
--    mysql -u root shreeji_instruments < 002_ctm_proving_rings.sql
-- ============================================================

USE shreeji_instruments;

CREATE TABLE IF NOT EXISTS ctm_proving_rings (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ring_key         VARCHAR(50)  NOT NULL UNIQUE,
  ring_label       VARCHAR(100) NOT NULL,
  ring_no          VARCHAR(150) NOT NULL,
  load_steps       JSON         NOT NULL,
  deflection_steps JSON         NOT NULL,
  sort_order       TINYINT UNSIGNED DEFAULT 0,
  active           TINYINT(1)   DEFAULT 1,
  updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO ctm_proving_rings (ring_key, ring_label, ring_no, load_steps, deflection_steps, sort_order)
VALUES
  (
    '1000KN',
    '1000 KN',
    '1000KN 065 IS 4169:2014',
    '["100","200","300","400","500","600","700","800","900","1000"]',
    '["79.1","155.2","232.3","308.1","384.4","460.1","536.7","613.4","689.7","766.1"]',
    1
  ),
  (
    '2000KN',
    '2000 KN',
    '2000KN 094 IS 4169:2014',
    '["200","400","600","800","1000","1200","1400","1600","1800","2000"]',
    '["84.1","168.6","254.7","342.1","429.1","513.9","600.9","689.2","776.7","865.8"]',
    2
  ),
  (
    '2000KN new',
    '2000 KN NEW',
    '2000KN 381 IS 4169:2014',
    '["200","400","600","800","1000","1200","1400","1600","1800","2000"]',
    '["73.1","145.1","215.2","284.7","356.1","427.4","498.1","569.4","641.3","714.1"]',
    3
  )
ON DUPLICATE KEY UPDATE
  ring_label       = VALUES(ring_label),
  ring_no          = VALUES(ring_no),
  load_steps       = VALUES(load_steps),
  deflection_steps = VALUES(deflection_steps),
  sort_order       = VALUES(sort_order);
