<?php
$pageTitle = 'Edit Service';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('provider');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_provider.php';

$errors = [];
$success = '';
$categories = ['Plumbing', 'Electrical', 'Cleaning', 'Painting', 'Carpentry', 'Landscaping', 'Moving', 'Tutoring', 'IT Support', 'General'];

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$serviceId) {
    header('Location: manage_services.php');
    exit;
}

// Fetch service — must belong to this provider
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND provider_id = ?");
$stmt->execute([$serviceId, $_SESSION['user_id']]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: manage_services.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    $imageFile = $_FILES['image'] ?? null;
    $imageName = $service['image']; // Keep old image by default

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
    
    // Handle Image Upload
    if (empty($errors) && $imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
        } else {
            $ext = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $newImageName = uniqid() . '.' . $ext;
            $uploadPath = __DIR__ . '/../uploads/' . $newImageName;
            if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
                // Delete old image if not default
                if ($imageName !== 'default_service.jpg' && file_exists(__DIR__ . '/../uploads/' . $imageName)) {
                    @unlink(__DIR__ . '/../uploads/' . $imageName);
                }
                $imageName = $newImageName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, price = ?, category = ?, location = ?, image = ?, status = ? WHERE id = ? AND provider_id = ?");
            $stmt->execute([$title, $description, $price, $category, $location, $imageName, $status, $serviceId, $_SESSION['user_id']]);
            $success = 'Service updated successfully!';
            // Refresh data
            $service = array_merge($service, compact('title', 'description', 'price', 'category', 'location', 'imageName', 'status'));
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
        }
    }
    $title = $service['title'];
    $description = $service['description'];
    $price = $service['price'];
    $category = $service['category'];
    $location = $service['location'];
    $imageName = $service['image'];
    $status = $service['status'];
}
?>
<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <div class="d-flex align-items-center mb-4">
      <a href="manage_services.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Back</a>
      <h5 class="mb-0">Edit Service</h5>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $success ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" novalidate>
          <div class="mb-3">
            <label for="title" class="form-label">Service Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" maxlength="150" value="<?= htmlspecialchars($title) ?>" required>
          </div>
          <div class="mb-3">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <select class="form-select" id="category" name="category" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($category === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4" maxlength="2000"><?= htmlspecialchars($description) ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="price" name="price" min="0" max="999999.99" step="0.01" value="<?= htmlspecialchars($price) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="location" class="form-label">Service Location</label>
              <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location ?? '') ?>" placeholder="e.g. Mumbai, MH">
            </div>
          </div>
          <div class="row">
              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
              <div class="col-md-6 mb-4">
                <label for="image" class="form-label">Update Image (Optional)</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/webp">
                <?php if($imageName !== 'default_service.jpg'): ?>
                    <small class="text-muted mt-1 d-block"><i class="bi bi-image"></i> Current: <?= htmlspecialchars($imageName) ?></small>
                <?php endif; ?>
              </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
