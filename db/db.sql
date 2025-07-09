-- Dijalankan sekali untuk membuat tabel-tabel
-- Hapus tabel jika sudah ada untuk pembuatan ulang
DROP TABLE IF EXISTS hse_summary, bsoc_monthly_contribution, bsoc_card_reports, daily_reports;

-- Tabel utama untuk laporan harian
CREATE TABLE daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) DEFAULT NULL,
    report_date DATE NOT NULL,
    zyh_startup_date DATE DEFAULT NULL,
    phm_startup_date DATE DEFAULT NULL,
    no_lti_days_zyh INT DEFAULT NULL,
    no_lti_days_phm INT DEFAULT NULL,
    total_bsoc_cards INT DEFAULT NULL,
    safe_cards INT DEFAULT NULL,
    unsafe_bsoc INT DEFAULT NULL,
    best_bsoc_title VARCHAR(255) DEFAULT NULL,
    best_bsoc_description TEXT DEFAULT NULL,
    total_monthly_bsoc_cards INT DEFAULT NULL,
    doctor VARCHAR(255) DEFAULT NULL,
    prepared_by VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel detail untuk setiap kartu BSOC
CREATE TABLE bsoc_card_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    entry_number INT NOT NULL,
    category VARCHAR(50) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    action_taken TEXT DEFAULT NULL,
    observer VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Open',
    FOREIGN KEY (report_id) REFERENCES daily_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel untuk data kontribusi BSOC bulanan
CREATE TABLE bsoc_monthly_contribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    entity_name VARCHAR(255) NOT NULL,
    hazard INT DEFAULT 0,
    unsafe_act INT DEFAULT 0,
    near_miss INT DEFAULT 0,
    safe_work INT DEFAULT 0,
    total INT DEFAULT 0,
    percentage DECIMAL(10, 9) DEFAULT 0,
    FOREIGN KEY (report_id) REFERENCES daily_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabel untuk ringkasan aktivitas HSE
CREATE TABLE hse_summary (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    activity_description TEXT NOT NULL,
    FOREIGN KEY (report_id) REFERENCES daily_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB;