<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/payment_helper.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please login first.");
}

$txnId = trim($_GET['txn'] ?? '');

if (empty($txnId)) {
    die("Invalid invoice request.");
}

// Fetch transaction and booking details
$stmt = $pdo->prepare("
    SELECT p.*, b.booking_date, b.notes, 
           s.title AS service_title, s.category, s.price AS base_price,
           u_cust.name AS customer_name, u_cust.email AS customer_email, u_cust.location AS customer_location,
           u_prov.name AS provider_name, u_prov.email AS provider_email
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    JOIN users u_cust ON p.user_id = u_cust.id
    JOIN users u_prov ON p.provider_id = u_prov.id
    WHERE p.transaction_id = ? AND (p.user_id = ? OR p.provider_id = ? OR ? = 'admin')
");
$role = $_SESSION['user_role'] ?? 'user';
$stmt->execute([$txnId, $_SESSION['user_id'], $_SESSION['user_id'], $role]);
$inv = $stmt->fetch();

if (!$inv) {
    die("Invoice not found or access restricted.");
}

$basePrice = (float)$inv['base_price'];
$convenienceFee = round($basePrice * 0.05, 2);
$taxAmount = round(($basePrice + $convenienceFee) * 0.18, 2);
$totalAmount = $inv['amount'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Service Provider | Invoice #<?= htmlspecialchars($inv['transaction_id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .invoice-box { max-width: 850px; margin: 30px auto; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .invoice-logo { font-size: 24px; font-weight: bold; color: #0d6efd; }
        @media print {
            body { background: #fff; }
            .invoice-box { box-shadow: none; margin: 0; max-width: 100%; padding: 20px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center my-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4"><i class="bi bi-printer me-2"></i> Print / Download PDF</button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-4 ms-2">Close</button>
    </div>

    <div class="invoice-box">
        <!-- Header -->
        <div class="row align-items-center mb-4 pb-3 border-bottom">
            <div class="col-sm-6">
                <div class="invoice-logo mb-1">🔧 Local Service Finder</div>
                <div class="text-muted small">Official Service Payment Receipt</div>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h4 class="fw-bold text-uppercase text-muted mb-0">TAX INVOICE</h4>
                <div class="text-primary fw-bold">#<?= htmlspecialchars($inv['transaction_id']) ?></div>
                <div class="text-muted small">Date: <?= date('d M Y, h:i A', strtotime($inv['created_at'])) ?></div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="text-uppercase text-muted fw-bold small">Billed To (Customer):</h6>
                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($inv['customer_name']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($inv['customer_email']) ?></div>
                <?php if(!empty($inv['customer_location'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars($inv['customer_location']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6 text-sm-end">
                <h6 class="text-uppercase text-muted fw-bold small">Service Provider:</h6>
                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($inv['provider_name']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($inv['provider_email']) ?></div>
            </div>
        </div>

        <!-- Itemized Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Service Description</th>
                        <th>Category</th>
                        <th class="text-center">Scheduled Date</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong class="text-dark"><?= htmlspecialchars($inv['service_title']) ?></strong>
                            <div class="text-muted small">Booking Ref: #BK-<?= str_pad($inv['booking_id'], 5, '0', STR_PAD_LEFT) ?></div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($inv['category']) ?></span></td>
                        <td class="text-center"><?= date('d M Y', strtotime($inv['booking_date'])) ?></td>
                        <td class="text-end fw-semibold">₹<?= number_format($basePrice, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Calculation Breakdown -->
        <div class="row justify-content-end">
            <div class="col-md-6 col-lg-5">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted">Base Service Charge:</td>
                        <td class="text-end fw-semibold">₹<?= number_format($basePrice, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Platform & Booking Fee (5%):</td>
                        <td class="text-end fw-semibold">₹<?= number_format($convenienceFee, 2) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">GST & Taxes (18%):</td>
                        <td class="text-end fw-semibold">₹<?= number_format($taxAmount, 2) ?></td>
                    </tr>
                    <tr class="border-top border-2">
                        <td class="fw-bold fs-5 text-dark">Total Paid:</td>
                        <td class="text-end fw-bold fs-4 text-primary">₹<?= number_format($totalAmount, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Info Footer -->
        <div class="mt-4 pt-3 border-top bg-light p-3 rounded">
            <div class="row text-muted small">
                <div class="col-sm-6">
                    <strong>Payment Method:</strong> <?= htmlspecialchars($inv['payment_method']) ?><br>
                    <strong>Payment Details:</strong> <?= maskPaymentDetails($inv['payment_method'], $inv['payment_details']) ?>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <strong>Payment Status:</strong> <span class="badge bg-success">Success / Paid</span><br>
                    <em>Simulated Transaction Engine</em>
                </div>
            </div>
        </div>

        <div class="text-center text-muted small mt-4">
            Thank you for using Local Service Finder! For support queries, contact support@localservicefinder.com.
        </div>
    </div>
</div>

</body>
</html>
