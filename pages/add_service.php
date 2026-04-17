<?php
$pageTitle = 'Add Service';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$errors = [];
$success = '';

$categories = ['Plumbing', 'Electrical', 'Cleaning', 'Painting', 'Carpentry', 'Landscaping', 'Moving', 'Tutoring', 'IT Support', 'General'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = trim($_POST['category'] ?? '');

    // Validation
    if (empty($title) || mb_strlen($title) > 150) {
        $errors[] = 'Title is required and must be under 150 characters.';
    }
    if (mb_strlen($description) > 2000) {
        $errors[] = 'Description must be under 2000 characters.';
    }
    if (!is_numeric($price) || $price < 0 || $price > 999999.99) {
        $errors[] = 'Price must be a valid number between 0 and 999999.99.';
    }
    if (empty($category) || !in_array($category, $categories)) {
        $errors[] = 'Please select a valid category.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO services (provider_id, title, description, price, category) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category]);
            $success = 'Service added successfully!';
            $title = $description = $price = $category = '';
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
        }
    }
}
?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <div class="d-flex align-items-center mb-4">
      <a href="manage_services.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Back</a>
      <h5 class="mb-0">Add New Service</h5>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" novalidate>
          <div class="mb-3">
            <label for="title" class="form-label">Service Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" maxlength="150" value="<?= htmlspecialchars($title ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select" id="category" name="category" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= (isset($category) && $category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" maxlength="2000"><?= htmlspecialchars($description ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label for="price" class="form-label">Price ($) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="price" name="price" min="0" max="999999.99" step="0.01" value="<?= htmlspecialchars($price ?? '') ?>" required>
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Service</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
