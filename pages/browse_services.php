<?php
$pageTitle = 'Browse Services';
require_once __DIR__ . '/../config/session.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$categories = ['Plumbing', 'Electrical', 'Cleaning', 'Painting', 'Carpentry', 'Landscaping', 'Moving', 'Tutoring', 'IT Support', 'General'];

$search = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$searchLat = isset($_GET['lat']) && $_GET['lat'] !== '' ? floatval($_GET['lat']) : null;
$searchLng = isset($_GET['lng']) && $_GET['lng'] !== '' ? floatval($_GET['lng']) : null;
$radius = isset($_GET['radius']) && $_GET['radius'] !== '' ? floatval($_GET['radius']) : null;
$nearLocation = trim($_GET['near_location'] ?? '');

// If coordinates are not set, check if the customer has profile coordinates to show nearby services by default
if ($searchLat === null && $searchLng === null && empty($nearLocation)) {
    $userStmt = $pdo->prepare("SELECT latitude, longitude, location FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['user_id']]);
    $userInfo = $userStmt->fetch();
    if ($userInfo && $userInfo['latitude'] !== null && $userInfo['longitude'] !== null) {
        $searchLat = floatval($userInfo['latitude']);
        $searchLng = floatval($userInfo['longitude']);
        $nearLocation = $userInfo['location'] ?? '';
    }
}

// Build query
$sql = "SELECT s.*, u.name AS provider_name,
               (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) AS avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) AS total_reviews";

$params = [];

if ($searchLat !== null && $searchLng !== null) {
    // Haversine formula in KM (6371)
    $sql .= ", (6371 * acos(cos(radians(:lat1)) * cos(radians(s.latitude)) * cos(radians(s.longitude) - radians(:lng1)) + sin(radians(:lat2)) * sin(radians(s.latitude)))) AS distance";
    $params['lat1'] = $searchLat;
    $params['lng1'] = $searchLng;
    $params['lat2'] = $searchLat;
} else {
    $sql .= ", NULL AS distance";
}

$sql .= " FROM services s 
          JOIN users u ON s.provider_id = u.id 
          WHERE s.status = 'active'";

if ($search !== '') {
    $sql .= " AND (s.title LIKE :search1 OR s.description LIKE :search2)";
    $like = '%' . $search . '%';
    $params['search1'] = $like;
    $params['search2'] = $like;
}
if ($filterCategory !== '' && in_array($filterCategory, $categories)) {
    $sql .= " AND s.category = :category";
    $params['category'] = $filterCategory;
}

if ($searchLat !== null && $searchLng !== null) {
    $sql .= " AND s.latitude IS NOT NULL AND s.longitude IS NOT NULL";
    if ($radius !== null && $radius > 0) {
        $sql .= " HAVING distance <= :radius";
        $params['radius'] = $radius;
    }
}

// Sorting logic
if ($sort === 'price_asc') {
    $sql .= " ORDER BY s.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY s.price DESC";
} elseif ($sort === 'rating_desc') {
    $sql .= " ORDER BY avg_rating DESC";
} elseif ($sort === 'distance_asc' && $searchLat !== null && $searchLng !== null) {
    $sql .= " ORDER BY distance ASC";
} else {
    $sql .= " ORDER BY s.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Card renderer helper to avoid code duplication
function renderServiceCard($service) {
    $avgRating = $service['avg_rating'] ? number_format($service['avg_rating'], 1) : null;
    $ratingHtml = '';
    if ($avgRating) {
        $ratingHtml = '<i class="bi bi-star-fill"></i> ' . $avgRating . ' <span class="text-muted small">(' . $service['total_reviews'] . ')</span>';
    } else {
        $ratingHtml = '<span class="text-muted small"><i class="bi bi-star"></i> No ratings</span>';
    }
    $descriptionHtml = nl2br(htmlspecialchars(mb_strimwidth($service['description'], 0, 110, '...')));
    $priceHtml = number_format($service['price'], 2);
    $catHtml = htmlspecialchars($service['category']);
    $titleHtml = htmlspecialchars($service['title']);
    $provHtml = htmlspecialchars($service['provider_name']);
    $id = $service['id'];
    
    $distanceHtml = '';
    if (isset($service['distance']) && $service['distance'] !== null) {
        $distanceHtml = '<span class="badge bg-info bg-opacity-10 text-info px-2 py-1 ms-1"><i class="bi bi-geo-alt"></i> ' . number_format($service['distance'], 1) . ' km</span>';
    }
    
    $lat = $service['latitude'] ?? '';
    $lng = $service['longitude'] ?? '';

    return <<<HTML
    <div class="col-md-6 service-result-item" data-id="{$id}" data-lat="{$lat}" data-lng="{$lng}" data-title="{$titleHtml}" data-price="{$priceHtml}" data-rating="{$avgRating}" data-reviews="{$service['total_reviews']}" data-category="{$catHtml}" data-provider="{$provHtml}">
        <div class="card h-100 service-card border-0 shadow-sm transition-hover">
            <div class="card-body d-flex flex-column p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{$catHtml}</span>
                        {$distanceHtml}
                    </div>
                    <div class="text-warning">{$ratingHtml}</div>
                </div>
                <h5 class="card-title fw-bold text-dark mb-2">{$titleHtml}</h5>
                <p class="text-muted small fw-medium mb-3">
                    <i class="bi bi-person bg-light rounded-circle px-1 py-1 me-1 text-secondary"></i> {$provHtml}
                </p>
                <p class="card-text text-secondary mb-4 flex-grow-1" style="font-size: 0.95rem;">{$descriptionHtml}</p>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="fw-bold text-primary fs-5">₹{$priceHtml}</span>
                    <div>
                        <a href="service_details.php?id={$id}" class="btn btn-outline-primary btn-sm me-1">Details</a>
                        <a href="book_service.php?id={$id}" class="btn btn-primary btn-sm">Book</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;
}

// Handle AJAX Request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    if (empty($services)) {
        echo '<div class="col-12"><div class="card shadow-sm border-0"><div class="card-body text-center py-5"><i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i><h4 class="text-muted">No Services Found</h4><p class="text-muted">Try adjusting your search query, radius, or removing filters.</p></div></div></div>';
    } else {
        foreach ($services as $service) {
            echo renderServiceCard($service);
        }
    }
    exit;
}

// Normal Page Load
require_once __DIR__ . '/../includes/dashboard_header.php';
require_once __DIR__ . '/../includes/sidebar_user.php';
?>
<!-- Leaflet.js Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<div class="dashboard-main">
  <?php require_once __DIR__ . '/../includes/topbar.php'; ?>
  <div class="dashboard-content">
    <h5 class="mb-4 fw-bold">Browse Services</h5>

    <!-- Search & Filter Bar -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
      <div class="card-body">
        <form method="GET" id="searchForm" class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="search" class="form-label fw-semibold text-muted small text-uppercase">Keyword Search</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
              <input type="text" class="form-control border-start-0 ps-0 bg-light" id="search" name="search" placeholder="What do you need?" value="<?= htmlspecialchars($search) ?>">
            </div>
          </div>
          
          <div class="col-md-3">
            <label for="near_location" class="form-label fw-semibold text-muted small text-uppercase">Near Location</label>
            <div class="input-group">
              <input type="text" class="form-control bg-light border-end-0" id="near_location" name="near_location" placeholder="e.g. Mumbai, MH" value="<?= htmlspecialchars($nearLocation) ?>">
              <button class="btn btn-outline-secondary border-start-0 bg-light text-secondary px-2" type="button" id="btn-gps-search" title="Use current GPS location"><i class="bi bi-geo-alt-fill text-primary"></i></button>
            </div>
          </div>
          
          <div class="col-md-2">
            <label for="radius" class="form-label fw-semibold text-muted small text-uppercase">Distance Radius</label>
            <select class="form-select bg-light" id="radius" name="radius">
              <option value="" <?= $radius === null ? 'selected' : '' ?>>Any Distance</option>
              <option value="5" <?= $radius === 5.0 ? 'selected' : '' ?>>Within 5 km</option>
              <option value="10" <?= $radius === 10.0 ? 'selected' : '' ?>>Within 10 km</option>
              <option value="25" <?= $radius === 25.0 ? 'selected' : '' ?>>Within 25 km</option>
              <option value="50" <?= $radius === 50.0 ? 'selected' : '' ?>>Within 50 km</option>
            </select>
          </div>

          <div class="col-md-2">
            <label for="category" class="form-label fw-semibold text-muted small text-uppercase">Category</label>
            <select class="form-select bg-light" id="category" name="category">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($filterCategory === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="col-md-2">
            <label for="sort" class="form-label fw-semibold text-muted small text-uppercase">Sort By</label>
            <select class="form-select bg-light" id="sort" name="sort">
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Top Rated</option>
              <option value="distance_asc" <?= $sort === 'distance_asc' ? 'selected' : '' ?>>Distance: Nearest First</option>
            </select>
          </div>

          <!-- Hidden coordinate inputs -->
          <input type="hidden" id="search-lat" name="lat" value="<?= htmlspecialchars($searchLat ?? '') ?>">
          <input type="hidden" id="search-lng" name="lng" value="<?= htmlspecialchars($searchLng ?? '') ?>">
        </form>
      </div>
    </div>

    <!-- Spinner -->
    <div id="loadingIndicator" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <!-- Main Side-by-Side Results & Map View -->
    <div class="row g-4">
      <!-- List Column -->
      <div class="col-lg-7">
        <div class="row g-4 transition-fade" id="resultsContainer">
          <?php if (empty($services)): ?>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                        <h4 class="text-muted">No Services Found</h4>
                        <p class="text-muted">Try adjusting your search query, radius, or removing filters.</p>
                    </div>
                </div>
            </div>
          <?php else: ?>
            <?php foreach ($services as $service): ?>
              <?= renderServiceCard($service) ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Map Column -->
      <div class="col-lg-5">
        <div class="sticky-lg-top" style="top: 85px; z-index: 10;">
          <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
              <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-map text-primary me-2"></i>Interactive Map</h6>
              <button type="button" id="btn-recenter-map" class="btn btn-outline-primary btn-sm px-2 py-1" style="font-size: 0.8rem;"><i class="bi bi-crosshair me-1"></i> Recenter</button>
            </div>
            <div class="card-body p-0">
              <div id="search-map" style="height: 480px; border-radius: 0 0 var(--radius) var(--radius);"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.transition-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.transition-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.transition-fade { transition: opacity 0.3s ease; }
.service-card { border-left: 4px solid var(--primary); }
.service-card:hover { border-left-color: var(--primary-dark); }
.card-highlight { border-left-color: #ffc107 !important; transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    const locationInput = document.getElementById('near_location');
    const radiusSelect = document.getElementById('radius');
    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');
    const resultsContainer = document.getElementById('resultsContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchLatInput = document.getElementById('search-lat');
    const searchLngInput = document.getElementById('search-lng');

    let debounceTimer;
    let map;
    let markersGroup;
    let activeResultMarkers = {};

    // Initial Map Coordinates Setup
    let initialLat = <?= $searchLat !== null ? $searchLat : '20.5937' ?>;
    let initialLng = <?= $searchLng !== null ? $searchLng : '78.9629' ?>;
    let hasSearchCoords = <?= $searchLat !== null ? 'true' : 'false' ?>;

    // Initialize Map
    map = L.map('search-map').setView([initialLat, initialLng], hasSearchCoords ? 12 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    markersGroup = L.layerGroup().addTo(map);

    // Function to parse result cards and update map markers
    const updateMapMarkers = () => {
        markersGroup.clearLayers();
        activeResultMarkers = {};
        
        const cards = document.querySelectorAll('.service-result-item');
        const points = [];

        cards.forEach(card => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            const id = card.dataset.id;
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const title = card.dataset.title;
                const price = card.dataset.price;
                const category = card.dataset.category;
                const provider = card.dataset.provider;
                const ratingVal = card.dataset.rating;
                const reviews = card.dataset.reviews;
                
                const stars = ratingVal ? `<i class="bi bi-star-fill text-warning"></i> ${ratingVal} (${reviews})` : '<span class="text-muted"><i class="bi bi-star"></i> No ratings</span>';

                // Custom Leaflet popup layout
                const popupContent = `
                    <div style="min-width: 200px;">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1">${category}</span>
                        <h6 class="fw-bold mb-1">${title}</h6>
                        <p class="text-muted small mb-2"><i class="bi bi-person"></i> ${provider}</p>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <span class="fw-bold text-primary">₹${price}</span>
                            <a href="service_details.php?id=${id}" class="btn btn-primary btn-sm px-2 py-0" style="font-size: 0.75rem;">View</a>
                        </div>
                    </div>
                `;

                const marker = L.marker([lat, lng]);
                marker.bindPopup(popupContent);
                markersGroup.addLayer(marker);
                
                activeResultMarkers[id] = marker;
                points.push([lat, lng]);

                // Hover link logic: list card triggers map marker open
                card.addEventListener('mouseenter', () => {
                    marker.openPopup();
                    card.querySelector('.card').classList.add('card-highlight');
                });
                card.addEventListener('mouseleave', () => {
                    // Only close popup if we want to, let's keep it open or close it
                    card.querySelector('.card').classList.remove('card-highlight');
                });
            }
        });

        // Also add a user search center pin if we have search coords
        const sLat = parseFloat(searchLatInput.value);
        const sLng = parseFloat(searchLngInput.value);
        if (!isNaN(sLat) && !isNaN(sLng)) {
            const centerIcon = L.divIcon({
                html: '<i class="bi bi-geo-alt-fill text-danger fs-3" style="text-shadow: 0 0 4px white;"></i>',
                className: 'custom-center-pin',
                iconSize: [24, 24],
                iconAnchor: [12, 24]
            });
            const centerMarker = L.marker([sLat, sLng], { icon: centerIcon }).addTo(markersGroup);
            centerMarker.bindPopup('<strong class="text-danger">Search Center</strong>');
            points.push([sLat, sLng]);
        }

        // Fit map bounds to show all markers beautifully
        if (points.length > 0) {
            const bounds = L.latLngBounds(points);
            map.fitBounds(bounds, { padding: [40, 40] });
        }
    };

    // Trigger markers update on initial load
    updateMapMarkers();

    // Fetch new results from PHP asynchronously
    const fetchResults = () => {
        resultsContainer.style.opacity = '0.3';
        loadingIndicator.classList.remove('d-none');
        
        const params = new URLSearchParams(new FormData(searchForm)).toString();
        
        fetch('browse_services.php?' + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            resultsContainer.innerHTML = html;
            setTimeout(() => {
                loadingIndicator.classList.add('d-none');
                resultsContainer.style.opacity = '1';
                updateMapMarkers(); // Redraw map markers with new search results
            }, 150);
            
            window.history.replaceState({}, '', 'browse_services.php?' + params);
        })
        .catch(err => {
            console.error('Fetch error:', err);
            resultsContainer.style.opacity = '1';
            loadingIndicator.classList.add('d-none');
        });
    };

    // Geocode Location Address client-side before fetching
    const geocodeAndFetch = () => {
        const address = locationInput.value.trim();
        
        if (address === '') {
            searchLatInput.value = '';
            searchLngInput.value = '';
            fetchResults();
            return;
        }

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
        
        fetch(url, {
            headers: { 'User-Agent': 'LocalServiceFinder/1.0' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                searchLatInput.value = parseFloat(data[0].lat);
                searchLngInput.value = parseFloat(data[0].lon);
            } else {
                searchLatInput.value = '';
                searchLngInput.value = '';
            }
            fetchResults();
        })
        .catch(err => {
            console.error('Geocoding error:', err);
            fetchResults();
        });
    };

    // Debounce for keyword search input
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchResults, 400);
    });

    // Handle address input blur or enter to geocode
    locationInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            geocodeAndFetch();
        }
    });

    locationInput.addEventListener('blur', () => {
        // Trigger geocode only if coordinates values mismatch (user edited address text manually)
        geocodeAndFetch();
    });

    // Dropdown filters fetch instantly
    categorySelect.addEventListener('change', fetchResults);
    radiusSelect.addEventListener('change', fetchResults);
    sortSelect.addEventListener('change', fetchResults);

    // Prevents default form submit
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        geocodeAndFetch();
    });

    // GPS Button handler
    document.getElementById('btn-gps-search').addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                searchLatInput.value = lat;
                searchLngInput.value = lon;
                
                // Reverse geocode to get a clean label
                const reverseUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18`;
                fetch(reverseUrl, {
                    headers: { 'User-Agent': 'LocalServiceFinder/1.0' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        const addr = data.address;
                        let label = '';
                        if (addr.city || addr.town || addr.village) {
                            label = (addr.city || addr.town || addr.village);
                        } else if (addr.state) {
                            label = addr.state;
                        } else {
                            label = data.display_name.split(',')[0];
                        }
                        locationInput.value = label + " (Current Location)";
                    } else {
                        locationInput.value = lat.toFixed(4) + ", " + lon.toFixed(4);
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-geo-alt-fill text-primary"></i>';
                    fetchResults();
                })
                .catch(() => {
                    locationInput.value = lat.toFixed(4) + ", " + lon.toFixed(4);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-geo-alt-fill text-primary"></i>';
                    fetchResults();
                });
            },
            function(err) {
                alert('Unable to retrieve coordinates from GPS.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-geo-alt-fill text-primary"></i>';
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    });

    // Recenter Map button
    document.getElementById('btn-recenter-map').addEventListener('click', () => {
        const points = [];
        const cards = document.querySelectorAll('.service-result-item');
        cards.forEach(card => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                points.push([lat, lng]);
            }
        });
        const sLat = parseFloat(searchLatInput.value);
        const sLng = parseFloat(searchLngInput.value);
        if (!isNaN(sLat) && !isNaN(sLng)) {
            points.push([sLat, sLng]);
        }

        if (points.length > 0) {
            const bounds = L.latLngBounds(points);
            map.fitBounds(bounds, { padding: [40, 40] });
        } else {
            map.setView([initialLat, initialLng], 5);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
