-- ============================================
-- GYM MANAGEMENT SYSTEM - DATABASE (UPDATED)
-- ============================================


DROP TABLE IF EXISTS BOOKING;
DROP TABLE IF EXISTS PAYMENT;
DROP TABLE IF EXISTS SCHEDULE;
DROP TABLE IF EXISTS MEMBER;
DROP TABLE IF EXISTS TRAINER;
DROP TABLE IF EXISTS PACKAGE;
DROP TABLE IF EXISTS ADMIN;

-- ADMIN
CREATE TABLE ADMIN (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- TRAINER
CREATE TABLE TRAINER (
    trainer_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- PACKAGE
CREATE TABLE PACKAGE (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    duration INT NOT NULL
);

-- MEMBER
CREATE TABLE MEMBER (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    package_id INT DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'inactive',
    join_date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (package_id) REFERENCES PACKAGE(package_id)
);

-- SCHEDULE
CREATE TABLE SCHEDULE (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    time VARCHAR(20) NOT NULL,
    trainer_id INT NOT NULL,
    FOREIGN KEY (trainer_id) REFERENCES TRAINER(trainer_id)
);

-- BOOKING
CREATE TABLE BOOKING (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    schedule_id INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    FOREIGN KEY (member_id) REFERENCES MEMBER(member_id),
    FOREIGN KEY (schedule_id) REFERENCES SCHEDULE(schedule_id)
);

-- PAYMENT
CREATE TABLE PAYMENT (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    package_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount INT NOT NULL,
    FOREIGN KEY (member_id) REFERENCES MEMBER(member_id),
    FOREIGN KEY (package_id) REFERENCES PACKAGE(package_id)
);

-- ============================================
-- SAMPLE DATA
-- ============================================

INSERT INTO ADMIN (name, email, password) VALUES
('Admin GymPro', 'admin@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- password: password

INSERT INTO PACKAGE (package_name, price, duration) VALUES
('Bronze', 150000, 1),
('Silver', 400000, 3),
('Gold', 700000, 6),
('Platinum', 1200000, 12),
('Daily Pass', 50000, 0);

INSERT INTO TRAINER (name, specialization, phone, email, password) VALUES
('Budi Santoso', 'Weight Training', '081234567890', 'budi@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Siti Rahayu', 'Yoga & Pilates', '081234567891', 'siti@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Rudi Hartono', 'Cardio & HIIT', '081234567892', 'rudi@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dewi Kusuma', 'Zumba & Aerobics', '081234567893', 'dewi@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Andi Pratama', 'CrossFit', '081234567894', 'andi@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- all trainer passwords: password

INSERT INTO MEMBER (name, address, phone, email, password, package_id, status, join_date) VALUES
('Ahmad Fauzi', 'Jl. Merdeka No. 10, Jakarta', '082111111111', 'ahmad@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 'active', '2026-04-01'),
('Rina Wati', 'Jl. Sudirman No. 25, Bandung', '082222222222', 'rina@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'active', '2026-04-02'),
('Doni Setiawan', 'Jl. Gatot Subroto No. 5, Surabaya', '082333333333', 'doni@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active', '2026-04-03'),
('Maya Putri', 'Jl. Diponegoro No. 8, Yogyakarta', '082444444444', 'maya@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 4, 'active', '2026-04-05'),
('Hendra Wijaya', 'Jl. Ahmad Yani No. 15, Semarang', '082555555555', 'hendra@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'active', '2026-04-07'),
('Fitri Lestari', 'Jl. Pahlawan No. 3, Medan', '082666666666', 'fitri@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'active', '2026-04-10');
-- all member passwords: password

INSERT INTO SCHEDULE (date, time, trainer_id) VALUES
('2026-05-01', '07:00 - 08:00', 1),
('2026-05-01', '09:00 - 10:00', 2),
('2026-05-02', '08:00 - 09:00', 3),
('2026-05-02', '10:00 - 11:00', 4),
('2026-05-03', '07:00 - 08:00', 5),
('2026-05-03', '15:00 - 16:00', 1),
('2026-05-04', '09:00 - 10:00', 2),
('2026-05-04', '16:00 - 17:00', 3);

INSERT INTO BOOKING (member_id, schedule_id, status) VALUES
(1, 1, 'confirmed'), (2, 1, 'confirmed'), (3, 2, 'confirmed'),
(1, 3, 'pending'), (4, 3, 'confirmed'), (5, 4, 'confirmed'),
(2, 5, 'cancelled'), (6, 6, 'confirmed'), (3, 7, 'pending'), (4, 8, 'confirmed');

INSERT INTO PAYMENT (member_id, package_id, payment_date, amount) VALUES
(1, 3, '2026-04-01', 700000), (2, 2, '2026-04-02', 400000),
(3, 1, '2026-04-03', 150000), (4, 4, '2026-04-05', 1200000),
(5, 2, '2026-04-07', 400000), (6, 1, '2026-04-10', 150000),
(1, 2, '2026-04-15', 400000), (2, 3, '2026-04-16', 700000);

-- ============================================
-- ADD PAYMENT PROOF & EXTEND PACKAGE SUPPORT
-- ============================================
ALTER TABLE PAYMENT ADD COLUMN IF NOT EXISTS proof_file VARCHAR(255) DEFAULT NULL;
ALTER TABLE PAYMENT ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT NULL;
ALTER TABLE PAYMENT ADD COLUMN IF NOT EXISTS verified TINYINT(1) DEFAULT 0;
ALTER TABLE MEMBER ADD COLUMN IF NOT EXISTS package_expiry DATE DEFAULT NULL;

-- Ensure new columns exist (run these if updating existing DB)
-- ALTER TABLE PAYMENT ADD COLUMN proof_file VARCHAR(255) DEFAULT NULL;
-- ALTER TABLE PAYMENT ADD COLUMN payment_method VARCHAR(50) DEFAULT NULL;  
-- ALTER TABLE PAYMENT ADD COLUMN verified TINYINT(1) DEFAULT 0;
-- ALTER TABLE MEMBER ADD COLUMN package_expiry DATE DEFAULT NULL;

-- GYM SETTINGS TABLE
CREATE TABLE IF NOT EXISTS GYM_SETTINGS (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO GYM_SETTINGS (setting_key, setting_value) VALUES
('gym_name', 'GymPro'),
('gym_address', 'Jl. Sudirman No. 88, Tanah Abang, Jakarta Pusat, 10220'),
('gym_phone', '(021) 5555-1234'),
('gym_email', 'info@gympro.id'),
('gym_lat', '-6.2175'),
('gym_lng', '106.8050'),
('bank_name', 'BNI'),
('bank_account', '1924182745'),
('bank_holder', 'GYM PRO'),
('dana_number', '082386210045'),
('dana_holder', 'GYM PRO'),
('gopay_number', '082386210045'),
('gopay_holder', 'GYM PRO')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
