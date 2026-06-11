<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/dashboard_header.php';
requireRole('user');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/sidebar_user.php';

$userId = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $latitude = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
    $longitude = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;

    if (empty($name) || empty($email)) {
        $error = 'Name and email are required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, location = ?, latitude = ?, longitude = ? WHERE id = ?");
            $stmt->execute([$name, $email, $location, $latitude, $longitude, $userId]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully.';
        } catch (PDOException $e) {
            $error = 'Email already in use.';
        }
    }
}

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!-- Leaflet.js Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header"><i class="bi bi-person-circle"></i> Profile Information</div>
          <div class="card-body">
            <?php if ($success): ?>
              <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" id="profileForm">
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Location (City, Area)</label>
                <div class="input-group mb-2">
                  <input type="text" name="location" id="location-input" class="form-control" value="<?= htmlspecialchars($user['location'] ?? '') ?>" placeholder="e.g. Mumbai, MH" required>
                  <button class="btn btn-outline-primary" type="button" id="btn-search-address"><i class="bi bi-search"></i> Find</button>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-muted small">Coordinates: <span id="coords-display"><?= $user['latitude'] ? number_format($user['latitude'], 5) . ', ' . number_format($user['longitude'], 5) : 'Not set' ?></span></span>
                  <button type="button" id="btn-detect-loc" class="btn btn-link text-primary p-0 btn-sm text-decoration-none"><i class="bi bi-geo-alt-fill"></i> Detect My Location</button>
                </div>

                <!-- Hidden inputs for coordinates -->
                <input type="hidden" name="latitude" id="latitude" value="<?= htmlspecialchars($user['latitude'] ?? '') ?>">
                <input type="hidden" name="longitude" id="longitude" value="<?= htmlspecialchars($user['longitude'] ?? '') ?>">

                <!-- Leaflet map container -->
                <div id="map" style="height: 250px; border-radius: var(--radius); border: 1px solid var(--medium-gray);"></div>
              </div>

              <div class="mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" disabled>
              </div>
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initial coordinates
    let initialLat = <?= $user['latitude'] ? $user['latitude'] : '20.5937' ?>;
    let initialLng = <?= $user['longitude'] ? $user['longitude'] : '78.9629' ?>;
    let hasCoords = <?= $user['latitude'] ? 'true' : 'false' ?>;
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

    // Function to update coordinates on form and display
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
            // Reverse geocode on drag
            reverseGeocode(position.lat, position.lng);
        });
    }

    // Initialize coordinate updates
    if (hasCoords) {
        updateCoords(initialLat, initialLng);
    }

    // Map click handler to place pin
    map.on('click', function(e) {
        updateCoords(e.latlng.lat, e.latlng.lng);
        reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    // Nominatim geocoding
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
            } else {
                alert('Location not found. Please try a different query or set the pin manually.');
            }
        })
        .catch(err => {
            console.error('Geocoding error:', err);
        });
    });

    // Handle pressing enter on location input
    document.getElementById('location-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btn-search-address').click();
        }
    });

    // GPS Geolocation Detector
    document.getElementById('btn-detect-loc').addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Detecting...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                map.setView([lat, lon], 15);
                updateCoords(lat, lon);
                reverseGeocode(lat, lon);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Detect My Location';
            },
            function(err) {
                alert('Unable to retrieve location. Make sure GPS/location services are enabled.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Detect My Location';
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    });

    // Nominatim Reverse Geocoding helper
    function reverseGeocode(lat, lon) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18`;
        fetch(url, {
            headers: { 'User-Agent': 'LocalServiceFinder/1.0' }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                // Get a shorter address if possible (city/town, road, state/country)
                const address = data.address;
                let shortAddress = '';
                if (address.road) shortAddress += address.road + ', ';
                if (address.suburb) shortAddress += address.suburb + ', ';
                if (address.city || address.town || address.village) {
                    shortAddress += (address.city || address.town || address.village) + ', ';
                }
                if (address.state) shortAddress += address.state;
                else if (address.country) shortAddress += address.country;
                
                // Fallback to display name if short address is empty
                if (shortAddress === '') shortAddress = data.display_name;
                
                // Remove trailing comma or space
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

