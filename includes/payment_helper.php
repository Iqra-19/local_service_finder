<?php
// includes/payment_helper.php

/**
 * Generates a realistic unique Transaction ID
 * Example: TXN-20260627-A98B7C
 */
function generateTransactionId() {
    $prefix = "TXN";
    $date = date("Ymd");
    $random = strtoupper(bin2hex(random_bytes(3)));
    return "{$prefix}-{$date}-{$random}";
}

/**
 * Mask payment details for privacy display
 * e.g., Card **** 4242 or UPI user***@okaxis
 */
function maskPaymentDetails($method, $details) {
    if (empty($details)) return $method;
    
    if ($method === 'Credit Card' || $method === 'Debit Card') {
        $clean = preg_replace('/\D/', '', $details);
        $last4 = substr($clean, -4);
        return "**** **** **** " . ($last4 ?: '4242');
    } elseif ($method === 'UPI') {
        $parts = explode('@', $details);
        if (count($parts) === 2) {
            $name = $parts[0];
            $maskedName = strlen($name) > 3 ? substr($name, 0, 3) . '***' : $name . '***';
            return $maskedName . '@' . $parts[1];
        }
        return $details;
    }
    return htmlspecialchars($details);
}

/**
 * Helper to render Bootstrap badges for Payment Statuses
 */
function getPaymentStatusBadge($status) {
    switch (strtolower($status)) {
        case 'success':
        case 'paid':
            return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Success</span>';
        case 'processing':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Processing</span>';
        case 'pending':
        case 'unpaid':
            return '<span class="badge bg-secondary"><i class="bi bi-clock me-1"></i>Pending</span>';
        case 'failed':
            return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>';
        case 'refunded':
            return '<span class="badge bg-info text-dark"><i class="bi bi-arrow-counterclockwise me-1"></i>Refunded</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}

/**
 * Helper to render Bootstrap badges for Booking Statuses
 */
function getBookingStatusBadge($status) {
    switch (strtolower($status)) {
        case 'confirmed':
            return '<span class="badge bg-primary"><i class="bi bi-shield-check me-1"></i>Confirmed</span>';
        case 'accepted':
            return '<span class="badge bg-info text-dark"><i class="bi bi-hand-thumbs-up me-1"></i>Accepted</span>';
        case 'completed':
            return '<span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Completed</span>';
        case 'pending':
            return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>';
        case 'rejected':
            return '<span class="badge bg-danger"><i class="bi bi-x-lg me-1"></i>Rejected</span>';
        case 'cancelled':
            return '<span class="badge bg-dark"><i class="bi bi-slash-circle me-1"></i>Cancelled</span>';
        default:
            return '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
    }
}
