-- ============================================================
-- Barangay Pili Clearance and Certificate Processing System
-- Database Schema
-- ============================================================


-- -------------------------------------------------------
-- Table: users (system accounts)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','resident') NOT NULL DEFAULT 'resident',
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    resident_id INT NULL,
    photo VARCHAR(255) NULL,
    archived_at DATETIME NULL,
    archived_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: residents
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(20),
    gender ENUM('Male','Female','Other') NOT NULL,
    birthdate DATE NOT NULL,
    civil_status ENUM('Single','Married','Widowed','Separated','Divorced') NOT NULL DEFAULT 'Single',
    nationality VARCHAR(80) DEFAULT 'Filipino',
    religion VARCHAR(100),
    occupation VARCHAR(150),
    contact_number VARCHAR(20),
    email VARCHAR(150),
    address TEXT NOT NULL,
    purok VARCHAR(100),
    voter_status ENUM('Registered','Not Registered') DEFAULT 'Not Registered',
    years_of_residency INT DEFAULT 0,
    photo VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    archived_at DATETIME NULL,
    archived_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: officials
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS officials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    position VARCHAR(150) NOT NULL,
    term_start DATE,
    term_end DATE,
    photo VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    sort_order INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: certificates (certificate types)
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(80) NOT NULL DEFAULT 'General',
    fee DECIMAL(10,2) DEFAULT 0.00,
    processing_days INT DEFAULT 1,
    template_file VARCHAR(255),
    requirements TEXT,
    status ENUM('active','inactive') DEFAULT 'active',
    archived_at DATETIME NULL,
    archived_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------
-- Table: requests
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    resident_id INT NOT NULL,
    certificate_id INT NOT NULL,
    purpose TEXT NOT NULL,
    status ENUM('pending','processing','approved','rejected','released') DEFAULT 'pending',
    remarks TEXT,
    processed_by INT,
    approved_by INT,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    processed_at DATETIME,
    approved_at DATETIME,
    released_at DATETIME,
    archived_at DATETIME NULL,
    archived_by INT NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    FOREIGN KEY (certificate_id) REFERENCES certificates(id),
    FOREIGN KEY (processed_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- -------------------------------------------------------
-- Table: payments
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash','gcash','maya') DEFAULT 'cash',
    payment_status ENUM('unpaid','paid','waived') DEFAULT 'unpaid',
    receipt_number VARCHAR(100),
    proof_of_payment VARCHAR(255) NULL,
    paid_at DATETIME,
    received_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id)
);

-- -------------------------------------------------------
-- Table: activity_logs
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    module VARCHAR(100),
    description TEXT,
    ip_address VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- -------------------------------------------------------
-- Default Data Seeds
-- -------------------------------------------------------

-- Default accounts (password for ALL = "password")
-- Change passwords immediately after first login!
INSERT INTO users (username, email, password, role, status) VALUES
('admin',  'admin@brgy-pili.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',  'active'),
('staff1', 'staff@brgy-pili.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff',  'active');

-- Certificate types
INSERT INTO certificates (name, description, category, fee, processing_days, template_file, requirements) VALUES
('Barangay Clearance', 'General clearance issued by the barangay for various purposes', 'Clearance', 50.00, 1, 'certificate_clearance.php', 'Valid ID, Proof of residency'),
('Certificate of Residency', 'Certifies that the person is a legitimate resident of Barangay Pili', 'Certification', 50.00, 1, 'certificate_residency.php', 'Valid ID'),
('Certificate of Indigency', 'Certifies that the resident belongs to the indigent sector', 'Social Services', 0.00, 1, 'certificate_indigency.php', 'Valid ID, Proof of indigency'),
('Business Clearance', 'Clearance required for business permit applications', 'Business', 100.00, 3, 'certificate_clearance.php', 'Valid ID, Business documents'),
('Certificate of Good Moral Character', 'Certifies good moral standing in the community', 'Certification', 50.00, 1, 'certificate_moral.php', 'Valid ID'),
('First Time Jobseeker Certificate', 'For first-time jobseekers as per RA 11261', 'Employment', 0.00, 1, 'certificate.php', 'Valid ID, Barangay Certificate');

-- Barangay Officials
INSERT INTO officials (name, position, sort_order, status) VALUES
('HON. JUAN DELA CRUZ', 'Barangay Captain', 1, 'active'),
('HON. MARIA SANTOS', 'Barangay Kagawad', 2, 'active'),
('HON. PEDRO REYES', 'Barangay Kagawad', 3, 'active'),
('HON. ANA GARCIA', 'Barangay Kagawad', 4, 'active'),
('HON. JOSE LIM', 'Barangay Kagawad', 5, 'active'),
('HON. LUCIA CRUZ', 'Barangay Kagawad', 6, 'active'),
('HON. ROBERTO TAN', 'Barangay Kagawad', 7, 'active'),
('HON. ELENA MENDOZA', 'Barangay Kagawad', 8, 'active'),
('HON. ANTONIO FLORES', 'SK Chairman', 9, 'active'),
('MS. CARMEN VILLANUEVA', 'Barangay Secretary', 10, 'active'),
('MR. MARCO BAUTISTA', 'Barangay Treasurer', 11, 'active');

-- Sample residents
INSERT INTO residents (first_name, middle_name, last_name, gender, birthdate, civil_status, contact_number, email, address, purok, voter_status, years_of_residency, status) VALUES
('Juan', 'Santos', 'Dela Cruz', 'Male', '1990-03-15', 'Married', '09171234567', 'juan@email.com', '123 Rizal Street, Barangay Pili', 'Purok 1', 'Registered', 10, 'active'),
('Maria', 'Reyes', 'Santos', 'Female', '1995-07-22', 'Single', '09281234567', 'maria@email.com', '456 Mabini Street, Barangay Pili', 'Purok 2', 'Registered', 5, 'active'),
('Pedro', 'Cruz', 'Garcia', 'Male', '1985-11-08', 'Married', '09391234567', 'pedro@email.com', '789 Bonifacio Street, Barangay Pili', 'Purok 3', 'Registered', 15, 'active');
