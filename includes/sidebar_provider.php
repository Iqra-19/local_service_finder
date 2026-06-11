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
      <a href="provider_dashboard.php" class="<?= $current === 'provider_dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
      </a>
    </li>
    <li>
      <a href="provider_profile.php" class="<?= $current === 'provider_profile.php' ? 'active' : '' ?>">
        <i class="bi bi-person"></i> <span>My Profile</span>
      </a>
    </li>
    <li>
      <a href="manage_services.php" class="<?= in_array($current, ['manage_services.php', 'add_service.php', 'edit_service.php']) ? 'active' : '' ?>">
        <i class="bi bi-tools"></i> <span>Manage Services</span>
      </a>
    </li>
    <li>
      <a href="booking_requests.php" class="<?= $current === 'booking_requests.php' ? 'active' : '' ?>">
        <i class="bi bi-journal-check"></i> <span>Booking Requests</span>
      </a>
    </li>
    <li>
      <a href="provider_reviews.php" class="<?= $current === 'provider_reviews.php' ? 'active' : '' ?>">
        <i class="bi bi-star"></i> <span>My Reviews</span>
      </a>
    </li>
    <li>
      <a href="chat.php" class="<?= $current === 'chat.php' ? 'active' : '' ?>">
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
