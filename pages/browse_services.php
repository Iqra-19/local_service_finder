<?php
$pageTitle = 'Browse Services';
require_once __DIR__ . '/../config/session.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$categories = ['Plumbing', 'Electrical', 'Cleaning', 'Painting', 'Carpentry', 'Landscaping', 'Moving', 'Tutoring', 'IT Support', 'General'];

$search = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');

$sql = "SELECT s.*, u.name AS provider_name,
               (SELECT AVG(rating) FROM reviews WHERE service_id = s.id) AS avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE service_id = s.id) AS total_reviews
        FROM services s 
        JOIN users u ON s.provider_id = u.id 
        WHERE s.status = 'active'";
$params = [];

if ($search !== '') {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($filterCategory !== '' && in_array($filterCategory, $categories)) {
    $sql .= " AND s.category = ?";
    $params[] = $filterCategory;
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY s.price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY s.price DESC";
} elseif ($sort === 'rating_desc') {
    $sql .= " ORDER BY avg_rating DESC";
} else {
    $sql .= " ORDER BY s.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Handle AJAX Request perfectly by echoing just the results block
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    if (empty($services)) {
        echo '<div class="col-12"><div class="card shadow-sm border-0"><div class="card-body text-center py-5"><i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i><h4 class="text-muted">No Services Found</h4><p class="text-muted">Try adjusting your search query or removing filters.</p></div></div></div>';
    } else {
        foreach ($services as $service) {
            $avgRating = $service['avg_rating'] ? number_format($service['avg_rating'], 1) : null;
            $ratingHtml = '';
            if ($avgRating) {
                $ratingHtml = '<i class="bi bi-star-fill"></i> ' . $avgRating . ' <span class="text-muted small">(' . $service['total_reviews'] . ')</span>';
            } else {
                $ratingHtml = '<span class="text-muted small"><i class="bi bi-star"></i> No ratings</span>';
            }
            $descriptionHtml = nl2br(htmlspecialchars(mb_strimwidth($service['description'], 0, 120, '...')));
            $priceHtml = number_format($service['price'], 2);
            $catHtml = htmlspecialchars($service['category']);
            $titleHtml = htmlspecialchars($service['title']);
            $provHtml = htmlspecialchars($service['provider_name']);
            $id = $service['id'];

            echo <<<HTML
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 service-card border-0 shadow-sm transition-hover">
                    <div class="card-body d-flex flex-column p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{$catHtml}</span>
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
    }
    exit;
}

// Normal Page Load
require_once __DIR__ . '/../includes/dashboard_header.php';
require_once __DIR__ . '/../includes/sidebar_user.php';
?>
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
            <label for="category" class="form-label fw-semibold text-muted small text-uppercase">Category</label>
            <select class="form-select bg-light" id="category" name="category">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>" <?= ($filterCategory === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="sort" class="form-label fw-semibold text-muted small text-uppercase">Sort By</label>
            <select class="form-select bg-light" id="sort" name="sort">
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="rating_desc" <?= $sort === 'rating_desc' ? 'selected' : '' ?>>Top Rated</option>
            </select>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100 fw-medium shadow-sm"><i class="bi bi-funnel me-1"></i> Apply Filters</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Spinner -->
    <div id="loadingIndicator" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>

    <!-- Results Block -->
    <div class="row g-4 transition-fade" id="resultsContainer">
      <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3 d-block"></i>
                    <h4 class="text-muted">No Services Found</h4>
                    <p class="text-muted">Try adjusting your search query or removing filters.</p>
                </div>
            </div>
        </div>
      <?php else: ?>
        <?php foreach ($services as $service): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 service-card border-0 shadow-sm transition-hover">
              <div class="card-body d-flex flex-column p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1"><?= htmlspecialchars($service['category']) ?></span>
                  <div class="text-warning">
                      <?php if ($service['avg_rating']): ?>
                          <i class="bi bi-star-fill"></i> <?= number_format($service['avg_rating'], 1) ?> 
                          <span class="text-muted small">(<?= $service['total_reviews'] ?>)</span>
                      <?php else: ?>
                          <span class="text-muted small"><i class="bi bi-star"></i> No ratings</span>
                      <?php endif; ?>
                  </div>
                </div>
                <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($service['title']) ?></h5>
                <p class="text-muted small fw-medium mb-3">
                  <i class="bi bi-person bg-light rounded-circle px-1 py-1 me-1 text-secondary"></i> <?= htmlspecialchars($service['provider_name']) ?>
                </p>
                <p class="card-text text-secondary mb-4 flex-grow-1" style="font-size: 0.95rem;">
                  <?= nl2br(htmlspecialchars(mb_strimwidth($service['description'], 0, 120, '...'))) ?>
                </p>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                  <span class="fw-bold text-primary fs-5">₹<?= number_format($service['price'], 2) ?></span>
                  <div>
                      <a href="service_details.php?id=<?= $service['id'] ?>" class="btn btn-outline-primary btn-sm me-1">Details</a>
                      <a href="book_service.php?id=<?= $service['id'] ?>" class="btn btn-primary btn-sm">Book</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<style>
.transition-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.transition-hover:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
.transition-fade { transition: opacity 0.3s ease; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');
    const resultsContainer = document.getElementById('resultsContainer');
    const loadingIndicator = document.getElementById('loadingIndicator');

    let debounceTimer;

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
            }, 150);
            
            // Update URL quietly without refreshing
            window.history.replaceState({}, '', 'browse_services.php?' + params);
        })
        .catch(() => {
            resultsContainer.style.opacity = '1';
            loadingIndicator.classList.add('d-none');
        });
    };

    // Live search typing
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchResults, 400); // 400ms debounce
    });

    // Handle dropdown changes instantly
    categorySelect.addEventListener('change', fetchResults);
    sortSelect.addEventListener('change', fetchResults);

    // Prevent default form submit visually loading page
    searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchResults();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/dashboard_footer.php'; ?>
