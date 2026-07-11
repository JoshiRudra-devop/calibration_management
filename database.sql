-- ============================================================
--  SHREEJI INSTRUMENTS  – MySQL Schema
--  Run once on XAMPP: phpMyAdmin > Import > this file
-- ============================================================



-- ── Users ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  phone         VARCHAR(20)  NOT NULL UNIQUE,
  email         VARCHAR(150),
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','operator') NOT NULL DEFAULT 'operator',
  active        TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin  (password = admin123)
-- ON DUPLICATE KEY UPDATE ensures re-importing always resets the hash and marks active
INSERT INTO users (name, phone, email, password_hash, role, active)
VALUES ('Admin', '9999999999', 'admin@shreejiinstruments.com',
        '$2y$12$wPeoQgNSYcB98PXdIF8mJ.bQV4UzQN0dgWbYdKVXkMnLoRji7Kji2', 'admin', 1)
ON DUPLICATE KEY UPDATE
  password_hash = VALUES(password_hash),
  role          = VALUES(role),
  active        = 1;

-- ── Instrument types ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS instrument_types (
  id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug         VARCHAR(60)  NOT NULL UNIQUE,
  label        VARCHAR(120) NOT NULL,
  has_make     TINYINT(1) DEFAULT 1,
  has_serial   TINYINT(1) DEFAULT 1,
  has_model    TINYINT(1) DEFAULT 0,
  has_capacity TINYINT(1) DEFAULT 0,
  has_size     TINYINT(1) DEFAULT 0,
  has_quantity TINYINT(1) DEFAULT 0,
  sort_order   SMALLINT DEFAULT 0
) ENGINE=InnoDB;

-- Seed instrument_types BEFORE certificate_counter so FK inserts succeed
INSERT IGNORE INTO instrument_types
  (slug, label, has_make, has_serial, has_model, has_capacity, has_size, has_quantity, sort_order)
VALUES
  ('autolevel',        'Auto Level',                      1,1,1,0,0,0, 1),
  ('aggregate_impact', 'Aggregate Impact Value App',      1,0,0,0,0,0, 2),
  ('ctm',              'Cube Testing Machine',            1,1,0,1,0,0, 3),
  ('cone_penetro',     'Cone Penetrometer',               1,0,0,0,0,0, 4),
  ('core_cutter',      'Core Cutter',                     0,0,0,0,0,0, 5),
  ('cube_mould',       'Cube Mould',                      0,0,0,0,1,1, 6),
  ('digital_thermo',   'Digital Thermometer',             1,0,0,1,0,0, 7),
  ('elongation',       'Elongation Gauge',                0,0,0,0,0,0, 8),
  ('oven',             'Electrical Hot Air Oven',         1,0,0,1,1,0, 9),
  ('flakness',         'Flakness Gauge',                  0,0,0,0,0,0,10),
  ('general',          'General Format',                  1,1,0,1,0,0,11),
  ('hydrometer',       'Hydrometer',                      1,0,0,1,0,0,12),
  ('isi_cube',         'ISI Cube Mould',                  0,0,0,0,1,1,13),
  ('measuring_cyl',    'Measuring Cylinder',              0,0,0,0,1,0,14),
  ('pycnometer',       'Pycnometer Bottle',               0,0,0,0,1,0,15),
  ('ph_meter',         'PH Meter',                        1,0,0,0,0,0,16),
  ('rapid_moisture',   'Rapid Moisture Meter',            1,1,0,0,0,0,17),
  ('sieves',           'Test Sieves',                     1,0,0,0,1,0,18),
  ('sand_pouring',     'Sand Pouring Cylinder',           0,0,0,0,1,0,19),
  ('slumcone',         'Slumcone',                        1,0,0,0,0,0,20),
  ('total_station',    'Electronic Total Station',        1,1,1,0,0,0,21),
  ('water_bath',       'Water Bath',                      1,0,0,1,0,0,22),
  ('vernier_caliper',  'Vernier Caliper',                 1,0,0,0,1,0,23),
  ('weight_balance',   'Weight Balance',                  1,1,0,1,0,0,24),
  ('weigh_batcher',    'Weigh Batcher',                   1,1,0,1,0,0,25),
  ('full_lab',         'Full Lab Report',                 0,0,0,0,0,0,26);

-- ── Parties (customers) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS parties (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(200) NOT NULL,
  address      TEXT,
  phone        VARCHAR(20),
  email        VARCHAR(150),
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FULLTEXT KEY ft_name (name)
) ENGINE=InnoDB;

-- ── Certificate counter ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS certificate_counter (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  instrument_type_id SMALLINT UNSIGNED NOT NULL UNIQUE,
  prefix             VARCHAR(20) NOT NULL DEFAULT 'SI',
  current_no         INT UNSIGNED NOT NULL DEFAULT 0,
  current_year       INT UNSIGNED DEFAULT NULL,
  current_month      INT UNSIGNED DEFAULT NULL,
  FOREIGN KEY (instrument_type_id) REFERENCES instrument_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- UNIQUE on instrument_type_id lets INSERT IGNORE skip rows on re-import safely
INSERT IGNORE INTO certificate_counter (instrument_type_id, prefix, current_no, current_year, current_month) VALUES
  (1, 'AL', 0, 2026, 6),
  (2, 'AI', 0, 2026, 6),
  (3, 'CTM', 0, 2026, 6),
  (4, 'CP', 0, 2026, 6),
  (5, 'CC', 0, 2026, 6),
  (6, 'CM', 0, 2026, 6),
  (7, 'DT', 0, 2026, 6),
  (8, 'EG', 0, 2026, 6),
  (9, 'HO', 0, 2026, 6),
  (10, 'FG', 0, 2026, 6),
  (11, 'GEN', 0, 2026, 6),
  (12, 'HY', 0, 2026, 6),
  (13, 'ICM', 0, 2026, 6),
  (14, 'MC', 0, 2026, 6),
  (15, 'PC', 0, 2026, 6),
  (16, 'PH', 0, 2026, 6),
  (17, 'RM', 0, 2026, 6),
  (18, 'SA', 0, 2026, 6),
  (19, 'SPC', 0, 2026, 6),
  (20, 'SC', 0, 2026, 6),
  (21, 'TS', 0, 2026, 6),
  (22, 'WBT', 0, 2026, 6),
  (23, 'VC', 0, 2026, 6),
  (24, 'WB', 0, 2026, 6),
  (25, 'VBC', 0, 2026, 6),
  (26, 'FL', 1, 2026, 6);

-- ── Certificates (master) ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS certificates (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cert_number         VARCHAR(30)  NOT NULL UNIQUE,
  instrument_type_id  SMALLINT UNSIGNED NOT NULL,
  party_id            INT UNSIGNED,
  party_name          VARCHAR(200) NOT NULL,
  site_location       VARCHAR(300),
  calibration_date    DATE NOT NULL,
  next_due_date       DATE NOT NULL,
  -- instrument details
  make                VARCHAR(100),
  model_no            VARCHAR(100),
  serial_no           VARCHAR(100),
  capacity            VARCHAR(80),
  size_val            VARCHAR(100),
  quantity            SMALLINT UNSIGNED,
  operated_type       VARCHAR(60),
  ring_type           VARCHAR(30),
  -- Cloudinary
  pdf_public_id       VARCHAR(255),
  pdf_url             TEXT,
  form_data           LONGTEXT,
  -- meta
  created_by          INT UNSIGNED,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (instrument_type_id) REFERENCES instrument_types(id),
  FOREIGN KEY (party_id)           REFERENCES parties(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by)         REFERENCES users(id)   ON DELETE SET NULL,
  INDEX idx_cert_date   (calibration_date),
  INDEX idx_cert_party  (party_name(50)),
  INDEX idx_cert_type   (instrument_type_id),
  INDEX idx_cert_due    (next_due_date)
) ENGINE=InnoDB;

-- ── CTM readings ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ctm_readings (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  certificate_id  INT UNSIGNED NOT NULL,
  ring_type       VARCHAR(30) NOT NULL,
  load_kn         SMALLINT UNSIGNED NOT NULL,
  deflection      DECIMAL(8,2),
  reading_set1    DECIMAL(8,2),
  reading_set2    DECIMAL(8,2),
  reading_set3    DECIMAL(8,2),
  average_kn      DECIMAL(8,2),
  FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Cube mould serials ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cube_serials (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  certificate_id  INT UNSIGNED NOT NULL,
  sr_no           SMALLINT UNSIGNED NOT NULL,
  serial_no       VARCHAR(60),
  FOREIGN KEY (certificate_id) REFERENCES certificates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Contact messages ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150),
  subject    VARCHAR(200),
  message    TEXT NOT NULL,
  is_read    TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;