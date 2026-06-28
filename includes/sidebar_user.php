<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <i class="bi bi-geo-alt-fill"></i>
    <span>LocalService</span>
  </div>
  <ul class="sidebar-nav">
    <li>
      <a href="user_dashboard.php" class="<?= $current === 'user_dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
      </a>
    </li>
    <li>
      <a href="user_profile.php" class="<?= $current === 'user_profile.php' ? 'active' : '' ?>">
        <i class="bi bi-person"></i> <span>My Profile</span>
      </a>
    </li>
    <li>
      <a href="browse_services.php" class="<?= $current === 'browse_services.php' ? 'active' : '' ?>">
        <i class="bi bi-search"></i> <span>Browse Services</span>
      </a>
    </li>
    <li>
      <a href="booking_history.php" class="<?= $current === 'booking_history.php' ? 'active' : '' ?>">
        <i class="bi bi-clock-history"></i> <span>Booking History</span>
      </a>
    </li>
    <li>
      <a href="payment_history.php" class="<?= $current === 'payment_history.php' ? 'active' : '' ?>">
        <i class="bi bi-credit-card-2-front"></i> <span>Payment History</span>
      </a>
    </li>
    <li>
      <a href="support_messages.php" class="<?= $current === 'support_messages.php' ? 'active' : '' ?>">
        <i class="bi bi-chat-left-text"></i> <span>Messages</span>
        <span class="badge bg-danger rounded-pill ms-auto d-none" id="unread-chat-count"></span>
      </a>
    </li>
  </ul>
  <div class="sidebar-footer">
    <a href="../pages/logout.php" class="btn btn-outline-danger btn-sm w-100">
      <i class="bi bi-box-arrow-left"></i> <span>Logout</span>
    </a>
  </div>
</aside>
