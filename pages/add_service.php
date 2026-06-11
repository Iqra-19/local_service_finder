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
    $location = trim($_POST['location'] ?? '');
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
    
    $imageFile = $_FILES['image'] ?? null;
    $imageName = 'default_service.jpg';

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
    
    // Handle Image Upload
    if (empty($errors) && $imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($imageFile['type'], $allowedTypes)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed.';
        } else {
            $ext = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $ext;
            $uploadPath = __DIR__ . '/../uploads/' . $imageName;
            if (!move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO services (provider_id, title, description, price, category, location, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $title, $description, $price, $category, $location, $latitude, $longitude, $imageName]);
            $success = 'Service added successfully!';
            $title = $description = $price = $category = $location = $latitude = $longitude = '';
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
        }
    }
}

// Fetch provider defaults
$providerStmt = $pdo->prepare("SELECT latitude, longitude, location FROM users WHERE id = ?");
$providerStmt->execute([$_SESSION['user_id']]);
$providerInfo = $providerStmt->fetch();
$provLat = $providerInfo['latitude'] ?? '';
$provLng = $providerInfo['longitude'] ?? '';
$provLoc = $providerInfo['location'] ?? '';
?>
<!-- Leaflet.js Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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
        <form method="POST" enctype="multipart/form-data" novalidate id="serviceForm">
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
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="price" name="price" min="0" max="999999.99" step="0.01" value="<?= htmlspecialchars($price ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="location-input" class="form-label">Service Location</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control" id="location-input" name="location" value="<?= htmlspecialchars($location ?? '') ?>" placeholder="e.g. Mumbai, MH" required>
                <button class="btn btn-outline-primary" type="button" id="btn-search-address"><i class="bi bi-search"></i> Find</button>
              </div>
              
              <!-- Checkbox to copy from profile -->
              <?php if ($provLat && $provLng): ?>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" id="sync-profile-loc">
                  <label class="form-check-label small text-muted" for="sync-profile-loc">
                    Use my profile location (<?= htmlspecialchars($provLoc) ?>)
                  </label>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="row mb-4">
            <div class="col-md-6 mb-3 mb-md-0">
              <label class="form-label">Map Pin (Click map to adjust)</label>
              <div id="map" style="height: 250px; border-radius: var(--radius); border: 1px solid var(--medium-gray);"></div>
              <small class="text-muted small">Coordinates: <span id="coords-display">Not set</span></small>
              
              <!-- Hidden coordinate inputs -->
              <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($latitude ?? '') ?>">
              <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($longitude ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label for="image" class="form-label">Service Image (Optional)</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png, image/webp">
            </div>
          </div>
          
          <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Add Service</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initial coordinates
    let initialLat = <?= !empty($latitude) ? $latitude : (!empty($provLat) ? $provLat : '20.5937') ?>;
    let initialLng = <?= !empty($longitude) ? $longitude : (!empty($provLng) ? $provLng : '78.9629') ?>;
    let hasCoords = <?= !empty($latitude) || !empty($provLat) ? 'true' : 'false' ?>;
    let zoomLevel = hasCoords ? 13 : 5;

    // Initialize Leaflet map
    const map = L.map('map').setView([initialLat, initialLng], zoomLevel);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Create marker
    let marker;
    if (hasCoords) {
        marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    }

    // Function to update coordinates
    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        document.getElementById('coords-display').innerText = lat.toFixed(5) + ', ' + lng.toFixed(5);
        
        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
        }

        // Marker drag handler
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoords(position.lat, position.lng);
            reverseGeocode(position.lat, position.lng);
            
            // Uncheck profile sync if dragged
            const checkbox = document.getElementById('sync-profile-loc');
            if (checkbox) checkbox.checked = false;
        });
    }

    // Initialize coordinates updates
    if (hasCoords) {
        updateCoords(initialLat, initialLng);
    }

    // Map click handler to place pin
    map.on('click', function(e) {
        updateCoords(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
        
        // Uncheck profile sync if clicked
        const checkbox = document.getElementById('sync-profile-loc');
        if (checkbox) checkbox.checked = false;
    });

    // Address Search
    document.getElementById('btn-search-address').addEventListener('click', function() {
        const address = document.getElementById('location-input').value.trim();
        if (address === '') return;

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
        
        fetch(url, {
            headers: { 'User-Agent': 'LocalServiceFinder/1.0' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                map.setView([lat, lon], 14);
                updateCoords(lat, lon);
                
                // Uncheck profile sync
                const checkbox = document.getElementById('sync-profile-loc');
                if (checkbox) checkbox.checked = false;
            } else {
                alert('Location not found. Please try a different query or set the pin manually.');
            }
        })
        .catch(err => {
            console.error('Geocoding error:', err);
        });
    });

    // Enter key handler
    document.getElementById('location-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn-search-address').click();
        }
    });

    // Sync from profile checkbox
    const syncCheckbox = document.getElementById('sync-profile-loc');
    if (syncCheckbox) {
        syncCheckbox.addEventListener('change', function() {
            if (this.checked) {
                const profLat = <?= !empty($provLat) ? $provLat : 'null' ?>;
                const profLng = <?= !empty($provLng) ? $provLng : 'null' ?>;
                const profLoc = "<?= addslashes($provLoc) ?>";
                
                if (profLat && profLng) {
                    document.getElementById('location-input').value = profLoc;
                    map.setView([profLat, profLng], 14);
                    updateCoords(profLat, profLng);
                }
            }
        });
    }

    // Reverse Geocoding helper
    function reverseGeocode(lat, lon) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18`;
        fetch(url, {
            headers: { 'User-Agent': 'LocalServiceFinder/1.0' }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                const address = data.address;
                let shortAddress = '';
                if (address.road) shortAddress += address.road + ', ';
                if (address.suburb) shortAddress += address.suburb + ', ';
                if (address.city || address.town || address.village) {
                    shortAddress += (address.city || address.town || address.village) + ', ';
                }
                if (address.state) shortAddress += address.state;
                else if (address.country) shortAddress += address.country;
                
                if (shortAddress === '') shortAddress = data.display_name;
                shortAddress = shortAddress.replace(/,\s*$/, "");
                
                document.getElementById('location-input').value = shortAddress;
            }
        })
        .catch(err => {
            console.error('Reverse geocoding error:', err);
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
