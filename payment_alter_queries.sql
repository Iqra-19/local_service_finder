-- ====================================================================
-- PAYMENT MODULE SIMULATION - ALTER QUERIES FOR EXISTING DATABASE
-- Project: Local Service Provider Platform (Urban Company Clone)
-- Execute these queries in phpMyAdmin or MySQL CLI to update your live DB.
-- ====================================================================

-- 1. Modify existing 'bookings' table ENUMs for booking_status and payment_status
ALTER TABLE bookings 
MODIFY COLUMN booking_status ENUM('pending', 'confirmed', 'accepted', 'completed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
MODIFY COLUMN payment_status ENUM('unpaid', 'pending', 'processing', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid';

-- 2. Create the 'payments' table if it does not already exist
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(50) NOT NULL UNIQUE,
    booking_id INT NOT NULL,
    user_id INT NOT NULL,
    provider_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('UPI', 'Credit Card', 'Debit Card', 'Net Banking', 'Wallet') NOT NULL,
    payment_status ENUM('Pending', 'Processing', 'Success', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    payment_details VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Add performance indexes for payments table
CREATE INDEX idx_payments_transaction ON payments(transaction_id);
CREATE INDEX idx_payments_booking ON payments(booking_id);
CREATE INDEX idx_payments_user ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(payment_status);
