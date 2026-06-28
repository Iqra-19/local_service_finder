<?php
require_once __DIR__ . '/config/session.php';

// If user is already logged in and not explicitly requesting landing view, redirect to dashboard
if (isLoggedIn() && (!isset($_GET['view']) || $_GET['view'] !== 'landing')) {
    redirectByRole();
}

$pageTitle = "Local Service Finder - Book Verified Local Professionals Near You";
$isLanding = true;
include __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO SECTION ================= -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="fa-solid fa-shield-halved"></i> 100% Verified & Trained Professionals
                </div>
                <h1 class="hero-title">
                    Find Trusted Local <span class="text-primary">Service Experts</span> Near You
                </h1>
                <p class="hero-subtitle">
                    Connect instantly with certified electricians, plumbers, cleaners, and tutors. Quality doorstep service guaranteed with transparent upfront pricing.
                </p>

                <!-- Search Bar -->
                <form action="/local_service_finder/pages/browse_services.php" method="GET" class="hero-search-box d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center flex-grow-1 me-2">
                        <i class="fa-solid fa-magnifying-glass text-primary me-2 fs-5"></i>
                        <input type="text" name="search" class="hero-search-input" placeholder="What service do you need? (e.g. AC Repair, Cleaning)" required>
                    </div>
                    <div class="search-divider"></div>
                    <div class="d-flex align-items-center flex-grow-1 ms-lg-3 me-2">
                        <i class="fa-solid fa-location-dot text-danger me-2 fs-5"></i>
                        <input type="text" name="location" class="hero-search-input" placeholder="Enter area or city...">
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-3 fw-bold shadow-sm text-nowrap">
                        Search Services
                    </button>
                </form>

                <!-- Action CTAs & Quick Stats -->
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <a href="/local_service_finder/pages/browse_services.php" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold shadow">
                        <i class="fa-solid fa-calendar-check me-2"></i>Book a Service
                    </a>
                    <a href="/local_service_finder/pages/register.php?role=provider" class="btn btn-outline-dark btn-lg rounded-pill px-4 py-3 fw-bold">
                        <i class="fa-solid fa-handshake me-2"></i>Become a Provider
                    </a>
                </div>

                <div class="d-flex align-items-center gap-4 mt-5 pt-3 border-top">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star-half-stroke"></i>
                        </div>
                        <span class="fw-bold text-dark">4.8/5</span>
                    </div>
                    <span class="text-muted">|</span>
                    <div class="text-muted small">Over <strong class="text-dark">45,000+</strong> bookings completed</div>
                </div>
            </div>

            <!-- Hero Graphic Illustration -->
            <div class="col-lg-6">
                <div class="hero-image-wrapper">
                    <!-- Floating Glass Badge 1 -->
                    <div class="floating-badge floating-badge-1">
                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="fa-solid fa-check fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-dark">Background Verified</div>
                            <small class="text-muted">Safe & Security Checked</small>
                        </div>
                    </div>

                    <!-- Hero Visual Graphic -->
                    <div class="p-4 glass-card text-center position-relative overflow-hidden">
                        <div class="bg-primary bg-opacity-10 rounded-4 p-5 mb-3 text-primary">
                            <svg class="w-100 img-fluid" style="max-height: 340px;" viewBox="0 0 600 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Modern SVG Hero Graphic Illustration -->
                                <circle cx="300" cy="200" r="180" fill="#2563eb" fill-opacity="0.08"/>
                                <rect x="120" y="80" width="360" height="240" rx="20" fill="#ffffff" stroke="#2563eb" stroke-width="4" stroke-dasharray="8 8"/>
                                <!-- House icon & Worker symbols -->
                                <path d="M300 120L220 190V280H380V190L300 120Z" fill="#3b82f6" fill-opacity="0.2" stroke="#2563eb" stroke-width="4"/>
                                <circle cx="300" cy="200" r="40" fill="#2563eb"/>
                                <path d="M290 200L298 208L315 190" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="160" y="140" width="80" height="60" rx="10" fill="#eff6ff" stroke="#93c5fd" stroke-width="2"/>
                                <rect x="360" y="140" width="80" height="60" rx="10" fill="#eff6ff" stroke="#93c5fd" stroke-width="2"/>
                                <path d="M180 170H220" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
                                <path d="M380 170H420" stroke="#2563eb" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h4 class="fw-bold mb-1">On-Demand Home Care</h4>
                        <p class="text-muted small mb-0">Professional tools, instant booking, transparent fixed rates.</p>
                    </div>

                    <!-- Floating Glass Badge 2 -->
                    <div class="floating-badge floating-badge-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                            <i class="fa-solid fa-clock fs-6"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-6 text-dark">Fast 30-Min Arrival</div>
                            <small class="text-muted">Express Emergency Service</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= POPULAR CATEGORIES ================= -->
<section id="categories" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Explore Categories</span>
            <h2 class="fw-bold fs-1">Popular Services at Your Fingertips</h2>
            <p class="text-muted">Choose from our wide array of professional home services tailored to your everyday needs.</p>
        </div>

        <div class="row g-4">
            <!-- 1. Electrician -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Electrician" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <h5 class="category-title">Electrician</h5>
                    <p class="category-desc">Wiring, socket repairs, fuse fixes, & light installations.</p>
                </a>
            </div>
            <!-- 2. Plumber -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Plumber" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-wrench"></i>
                    </div>
                    <h5 class="category-title">Plumber</h5>
                    <p class="category-desc">Leak repairs, tap fittings, pipe unblocking & drainage.</p>
                </a>
            </div>
            <!-- 3. Carpenter -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Carpenter" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-hammer"></i>
                    </div>
                    <h5 class="category-title">Carpenter</h5>
                    <p class="category-desc">Furniture assembly, door lock fitting & custom woodwork.</p>
                </a>
            </div>
            <!-- 4. Painter -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Painter" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-paint-roller"></i>
                    </div>
                    <h5 class="category-title">Painter</h5>
                    <p class="category-desc">Full home painting, accent walls & waterproof coating.</p>
                </a>
            </div>
            <!-- 5. Home Cleaning -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Home+Cleaning" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-broom"></i>
                    </div>
                    <h5 class="category-title">Home Cleaning</h5>
                    <p class="category-desc">Deep kitchen, bathroom, sofa & full house sanitization.</p>
                </a>
            </div>
            <!-- 6. AC Repair -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=AC+Repair" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-snowflake"></i>
                    </div>
                    <h5 class="category-title">AC Repair</h5>
                    <p class="category-desc">Gas refilling, filter washing & cooling troubleshooting.</p>
                </a>
            </div>
            <!-- 7. Beauty Services -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Beauty" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-spa"></i>
                    </div>
                    <h5 class="category-title">Beauty Services</h5>
                    <p class="category-desc">Salon at home, massages, haircuts & facial treatments.</p>
                </a>
            </div>
            <!-- 8. Appliance Repair -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Appliance+Repair" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-tv"></i>
                    </div>
                    <h5 class="category-title">Appliance Repair</h5>
                    <p class="category-desc">Washing machine, refrigerator, microwave & TV repair.</p>
                </a>
            </div>
            <!-- 9. Tutor -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Tutor" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h5 class="category-title">Tutor</h5>
                    <p class="category-desc">Academic home tutors, language instructors & coding coaches.</p>
                </a>
            </div>
            <!-- 10. Car Wash -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Car+Wash" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <h5 class="category-title">Car Wash</h5>
                    <p class="category-desc">Doorstep eco car washing, interior detailing & polishing.</p>
                </a>
            </div>
            <!-- 11. Pest Control -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Pest+Control" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-bug"></i>
                    </div>
                    <h5 class="category-title">Pest Control</h5>
                    <p class="category-desc">Cockroach, termite, mosquito & bedbug extermination.</p>
                </a>
            </div>
            <!-- 12. Gardening -->
            <div class="col-6 col-md-4 col-lg-3">
                <a href="/local_service_finder/pages/browse_services.php?category=Gardening" class="category-card">
                    <div class="category-icon-box">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <h5 class="category-title">Gardening</h5>
                    <p class="category-desc">Lawn mowing, plant maintenance, balcony garden setup.</p>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section id="how-it-works" class="py-5 bg-light position-relative">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Simple Process</span>
            <h2 class="fw-bold fs-1">How ServiceFinder Works</h2>
            <p class="text-muted">Book any expert service in 4 easy steps from the comfort of your home.</p>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="timeline-card">
                    <div class="step-number">01</div>
                    <div class="step-icon">
                        <i class="fa-solid fa-magnifying-glass-location"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Search Service</h5>
                    <p class="text-muted small mb-0">Browse categories or enter your specific repair requirement and location.</p>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="timeline-card">
                    <div class="step-number">02</div>
                    <div class="step-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Choose Provider</h5>
                    <p class="text-muted small mb-0">Compare ratings, verified reviews, experience, and hourly pricing options.</p>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="timeline-card">
                    <div class="step-number">03</div>
                    <div class="step-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Book Appointment</h5>
                    <p class="text-muted small mb-0">Select your convenient date and time slot with easy online booking.</p>
                </div>
            </div>
            <!-- Step 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="timeline-card">
                    <div class="step-number">04</div>
                    <div class="step-icon">
                        <i class="fa-solid fa-house-chimney-check"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Service at Doorstep</h5>
                    <p class="text-muted small mb-0">Our background-verified professional arrives promptly and fixes the issue.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US ================= -->
<section id="why-us" class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Why Choose Us</span>
            <h2 class="fw-bold fs-1">Built on Trust, Safety & Quality</h2>
            <p class="text-muted">We eliminate the hassle of finding skilled labor with our rigorous quality standards.</p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-user-shield"></i></div>
                    <h5 class="fw-bold mb-2">Verified Professionals</h5>
                    <p class="text-muted small mb-0">Every expert undergoes thorough police verification and skill checks.</p>
                </div>
            </div>
            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-tags"></i></div>
                    <h5 class="fw-bold mb-2">Affordable Pricing</h5>
                    <p class="text-muted small mb-0">Transparent rate cards with no hidden fees or surprise extra charges.</p>
                </div>
            </div>
            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-lock"></i></div>
                    <h5 class="fw-bold mb-2">Secure Booking</h5>
                    <p class="text-muted small mb-0">Encrypted transactions and safe payment options for your peace of mind.</p>
                </div>
            </div>
            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-bolt-lightning"></i></div>
                    <h5 class="fw-bold mb-2">Fast Response</h5>
                    <p class="text-muted small mb-0">Prompt dispatch with average arrival under 30 minutes in major areas.</p>
                </div>
            </div>
            <!-- Feature 5 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-award"></i></div>
                    <h5 class="fw-bold mb-2">Service Warranty</h5>
                    <p class="text-muted small mb-0">Enjoy a 30-day revisit warranty if any issue recurs after repair.</p>
                </div>
            </div>
            <!-- Feature 6 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <h5 class="fw-bold mb-2">Live Booking Status</h5>
                    <p class="text-muted small mb-0">Track real-time provider arrival and updates directly from your dashboard.</p>
                </div>
            </div>
            <!-- Feature 7 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-headset"></i></div>
                    <h5 class="fw-bold mb-2">24/7 Support</h5>
                    <p class="text-muted small mb-0">Dedicated customer helpline ready to resolve any query or concern instantly.</p>
                </div>
            </div>
            <!-- Feature 8 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-star-half-stroke"></i></div>
                    <h5 class="fw-bold mb-2">Ratings & Reviews</h5>
                    <p class="text-muted small mb-0">100% authentic community reviews from genuine verified customers.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FEATURED PROFESSIONALS ================= -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between mb-5">
            <div>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Top Rated</span>
                <h2 class="fw-bold fs-1 mb-0">Featured Service Experts</h2>
            </div>
            <a href="/local_service_finder/pages/browse_services.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold mt-3 mt-md-0">
                View All Experts <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <!-- Pro 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="pro-card text-center pb-4">
                    <div class="pro-header-bg"></div>
                    <div class="pro-avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1540569014015-19a7be504e3a?auto=format&fit=crop&w=300&q=80" alt="Alex Rivera" class="pro-avatar-img">
                    </div>
                    <h5 class="fw-bold mb-1">Alex Rivera</h5>
                    <p class="text-primary fw-semibold small mb-2"><i class="fa-solid fa-bolt me-1"></i> Master Electrician</p>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-star me-1"></i>4.9</span>
                        <span class="text-muted small">(124 reviews)</span>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-briefcase me-1"></i> 8 Years Exp.
                    </div>
                    <div class="fw-bold text-dark fs-5 mb-3">Starts from <span class="text-primary">$49</span></div>
                    <a href="/local_service_finder/pages/browse_services.php" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Book Now</a>
                </div>
            </div>

            <!-- Pro 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="pro-card text-center pb-4">
                    <div class="pro-header-bg" style="background: linear-gradient(135deg, #0d9488, #14b8a6);"></div>
                    <div class="pro-avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80" alt="Sarah Jenkins" class="pro-avatar-img">
                    </div>
                    <h5 class="fw-bold mb-1">Sarah Jenkins</h5>
                    <p class="text-teal fw-semibold small mb-2" style="color:#0d9488;"><i class="fa-solid fa-broom me-1"></i> Deep Cleaning Lead</p>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-star me-1"></i>5.0</span>
                        <span class="text-muted small">(210 reviews)</span>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-briefcase me-1"></i> 6 Years Exp.
                    </div>
                    <div class="fw-bold text-dark fs-5 mb-3">Starts from <span class="text-primary">$65</span></div>
                    <a href="/local_service_finder/pages/browse_services.php" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Book Now</a>
                </div>
            </div>

            <!-- Pro 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="pro-card text-center pb-4">
                    <div class="pro-header-bg" style="background: linear-gradient(135deg, #b91c1c, #ef4444);"></div>
                    <div class="pro-avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=300&q=80" alt="Marcus Chen" class="pro-avatar-img">
                    </div>
                    <h5 class="fw-bold mb-1">Marcus Chen</h5>
                    <p class="text-danger fw-semibold small mb-2"><i class="fa-solid fa-wrench me-1"></i> Plumbing Specialist</p>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-star me-1"></i>4.8</span>
                        <span class="text-muted small">(98 reviews)</span>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-briefcase me-1"></i> 10 Years Exp.
                    </div>
                    <div class="fw-bold text-dark fs-5 mb-3">Starts from <span class="text-primary">$55</span></div>
                    <a href="/local_service_finder/pages/browse_services.php" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Book Now</a>
                </div>
            </div>

            <!-- Pro 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="pro-card text-center pb-4">
                    <div class="pro-header-bg" style="background: linear-gradient(135deg, #6d28d9, #8b5cf6);"></div>
                    <div class="pro-avatar-wrapper">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=300&q=80" alt="Elena Rostova" class="pro-avatar-img">
                    </div>
                    <h5 class="fw-bold mb-1">Elena Rostova</h5>
                    <p class="text-purple fw-semibold small mb-2" style="color:#6d28d9;"><i class="fa-solid fa-snowflake me-1"></i> AC & HVAC Tech</p>
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fa-solid fa-star me-1"></i>4.9</span>
                        <span class="text-muted small">(156 reviews)</span>
                    </div>
                    <div class="text-muted small mb-3">
                        <i class="fa-solid fa-briefcase me-1"></i> 7 Years Exp.
                    </div>
                    <div class="fw-bold text-dark fs-5 mb-3">Starts from <span class="text-primary">$40</span></div>
                    <a href="/local_service_finder/pages/browse_services.php" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">Book Now</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= PLATFORM STATISTICS ================= -->
<section class="stats-section">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h2 class="counter" data-target="45000">45,000+</h2>
                    <p class="text-white-50 fw-medium mb-0">Happy Customers</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h2 class="counter" data-target="2500">2,500+</h2>
                    <p class="text-white-50 fw-medium mb-0">Verified Professionals</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h2 class="counter" data-target="60000">60,000+</h2>
                    <p class="text-white-50 fw-medium mb-0">Services Completed</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <h2 class="counter" data-target="35">35+</h2>
                    <p class="text-white-50 fw-medium mb-0">Cities Covered</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= CUSTOMER REVIEWS ================= -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Real Feedback</span>
            <h2 class="fw-bold fs-1">Loved by Thousands of Homeowners</h2>
            <p class="text-muted">Read genuine stories from customers who experienced hassle-free service booking.</p>
        </div>

        <div class="row g-4">
            <!-- Review 1 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div>
                        <div class="d-flex text-warning mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-dark mb-4 fst-italic">"The AC repair technician arrived in 25 minutes! Extremely professional, transparent pricing, and cleaned up everything before leaving. Highly recommended!"</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" alt="Emily Watson" class="rounded-circle" style="width:48px; height:48px; object-fit:cover;">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Emily Watson</h6>
                            <small class="text-muted">Homeowner, Downtown</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div>
                        <div class="d-flex text-warning mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-dark mb-4 fst-italic">"Finding a trustworthy plumber used to take days. On ServiceFinder, I booked Marcus in 2 minutes. Outstanding app design and seamless service."</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" alt="David Miller" class="rounded-circle" style="width:48px; height:48px; object-fit:cover;">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">David Miller</h6>
                            <small class="text-muted">Apartment Resident</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div>
                        <div class="d-flex text-warning mb-3">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-dark mb-4 fst-italic">"The deep cleaning team transformed our old house before moving in. Spotless work, friendly personnel, and great pricing package!"</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="Sophia Martinez" class="rounded-circle" style="width:48px; height:48px; object-fit:cover;">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Sophia Martinez</h6>
                            <small class="text-muted">Villa Owner</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= BECOME A PROVIDER ================= -->
<section id="become-provider" class="py-5 bg-light">
    <div class="container py-4">
        <div class="glass-card p-5 border-0 shadow-lg" style="background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3 fw-semibold">Grow Your Business</span>
                    <h2 class="fw-bold fs-1 text-dark mb-3">Are You a Skilled Service Professional?</h2>
                    <p class="text-muted fs-5 mb-4">Join thousands of electricians, plumbers, and cleaners who earn higher income and manage flexible working hours with ServiceFinder.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                            <span class="fw-semibold text-dark">Zero Joining Fee</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                            <span class="fw-semibold text-dark">Flexible Working Hours</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                            <span class="fw-semibold text-dark">Guaranteed Weekly Pay</span>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-circle-check text-primary fs-5"></i>
                            <span class="fw-semibold text-dark">24/7 Partner Support</span>
                        </div>
                    </div>
                    <a href="/local_service_finder/pages/register.php?role=provider" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow">
                        Register as Provider <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="p-4 bg-white rounded-4 shadow-sm">
                        <i class="fa-solid fa-briefcase text-primary mb-3" style="font-size: 4rem;"></i>
                        <h4 class="fw-bold text-dark">Earn Up To $3,500/mo</h4>
                        <p class="text-muted small mb-3">Take direct bookings from nearby customers without middlemen.</p>
                        <span class="badge bg-success-subtle text-success fw-bold px-3 py-2 rounded-pill"><i class="fa-solid fa-chart-line me-1"></i> High Customer Demand</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= MOBILE APP PROMOTION ================= -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="app-promo-section">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3 fw-semibold">On The Go</span>
                    <h2 class="fw-bold fs-1 text-dark mb-3">Book Services Anytime, Anywhere with Our Mobile App</h2>
                    <p class="text-muted fs-5 mb-4">Get real-time booking tracking, instant chat with providers, exclusive discounts, and automated invoice receipts.</p>
                    
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="#" class="btn btn-dark btn-lg rounded-4 px-4 py-2 d-flex align-items-center gap-3">
                            <i class="fa-brands fa-apple fs-2"></i>
                            <div class="text-start">
                                <small class="d-block text-uppercase text-white-50" style="font-size:0.7rem;">Download on the</small>
                                <span class="fw-bold fs-6">App Store</span>
                            </div>
                        </a>
                        <a href="#" class="btn btn-dark btn-lg rounded-4 px-4 py-2 d-flex align-items-center gap-3">
                            <i class="fa-brands fa-google-play fs-2"></i>
                            <div class="text-start">
                                <small class="d-block text-uppercase text-white-50" style="font-size:0.7rem;">GET IT ON</small>
                                <span class="fw-bold fs-6">Google Play</span>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="phone-mockup-wrapper">
                        <div class="p-4 bg-dark text-white rounded-5 shadow-lg d-inline-block mx-auto" style="max-width: 300px; border: 8px solid #334155;">
                            <div class="d-flex justify-content-between align-items-center mb-3 text-white-50 small">
                                <span>9:41</span>
                                <div class="d-flex gap-1"><i class="fa-solid fa-wifi"></i><i class="fa-solid fa-battery-full"></i></div>
                            </div>
                            <div class="bg-primary p-3 rounded-4 mb-3 text-start">
                                <small class="text-white-50">Active Booking</small>
                                <h6 class="fw-bold mb-0">Electrician Arriving</h6>
                                <small class="text-white">ETA: 12 Minutes</small>
                            </div>
                            <div class="bg-secondary bg-opacity-25 p-3 rounded-4 text-start">
                                <small class="text-white-50">Recent History</small>
                                <div class="fw-semibold">Deep Cleaning Completed</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FAQ SECTION ================= -->
<section id="faq" class="py-5 bg-light">
    <div class="container py-4 max-w-900">
        <div class="text-center mx-auto mb-5">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-2 fw-semibold">Got Questions?</span>
            <h2 class="fw-bold fs-1">Frequently Asked Questions</h2>
            <p class="text-muted">Everything you need to know about our services, safety, and payment methods.</p>
        </div>

        <div class="accordion" id="landingFaqAccordion">
            <!-- FAQ 1 -->
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                        How are service providers verified on ServiceFinder?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#landingFaqAccordion">
                    <div class="accordion-body">
                        All providers undergo a thorough background verification process including identity check, criminal record verification, and hands-on skill assessment tests before taking orders.
                    </div>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                        What if I am not satisfied with the completed service?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#landingFaqAccordion">
                    <div class="accordion-body">
                        We offer a 30-day service warranty on all bookings. If any issue persists or you are unsatisfied, contact our support team and we will send an expert to re-inspect and fix it free of cost.
                    </div>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                        How do payments work? Are there hidden fees?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#landingFaqAccordion">
                    <div class="accordion-body">
                        Pricing is transparent and shown upfront during booking. You can pay securely online via credit card, mobile wallet, or cash after the service is completed to your satisfaction.
                    </div>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                        Can I reschedule or cancel my booking?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#landingFaqAccordion">
                    <div class="accordion-body">
                        Yes! You can reschedule or cancel your appointment directly through your user dashboard up to 2 hours prior to the scheduled time without any cancellation fees.
                    </div>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="accordion-item shadow-sm">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false" aria-controls="faq5">
                        How do I sign up as a service provider?
                    </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#landingFaqAccordion">
                    <div class="accordion-body">
                        Click on the "Become a Provider" button in the navigation bar, fill in your profile details, select your skilled service category, and upload your verification documents to start receiving bookings!
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FINAL CALL TO ACTION ================= -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="cta-banner text-center position-relative overflow-hidden">
            <h2 class="fw-bold fs-1 mb-3">Ready to Experience Professional Doorstep Service?</h2>
            <p class="fs-5 mb-4 text-white-50 max-w-700 mx-auto">Book your first service today and get instant expert solutions at affordable prices.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/local_service_finder/pages/browse_services.php" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary shadow">
                    Book Your Service Now <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="landing-footer">
    <div class="container">
        <div class="row g-5 mb-5">
            <!-- Brand Info -->
            <div class="col-lg-4">
                <a class="d-flex align-items-center gap-2 mb-3 text-decoration-none" href="/local_service_finder/">
                    <div class="brand-icon-box">
                        <i class="fa-solid fa-wrench"></i>
                    </div>
                    <span class="brand-text text-white">Service<span class="text-primary">Finder</span></span>
                </a>
                <p class="text-muted mb-4">Connecting customers with verified local service professionals for fast, reliable doorstep repair, cleaning, and maintenance services.</p>
                <div class="d-flex gap-2">
                    <a href="#" class="social-icon-btn"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-icon-btn"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-6 col-md-3 col-lg-2">
                <h5>Quick Links</h5>
                <ul class="list-unstyled d-flex flex-direction-column gap-2 mb-0">
                    <li><a href="/local_service_finder/">Home</a></li>
                    <li><a href="/local_service_finder/pages/browse_services.php">Browse Services</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                    <li><a href="#why-us">Why Choose Us</a></li>
                    <li><a href="#faq">FAQs</a></li>
                </ul>
            </div>

            <!-- Service Categories -->
            <div class="col-6 col-md-3 col-lg-3">
                <h5>Popular Categories</h5>
                <ul class="list-unstyled d-flex flex-direction-column gap-2 mb-0">
                    <li><a href="/local_service_finder/pages/browse_services.php?category=Electrician">Electrician Services</a></li>
                    <li><a href="/local_service_finder/pages/browse_services.php?category=Plumber">Plumbing & Repairs</a></li>
                    <li><a href="/local_service_finder/pages/browse_services.php?category=Home+Cleaning">Home Deep Cleaning</a></li>
                    <li><a href="/local_service_finder/pages/browse_services.php?category=AC+Repair">AC Service & Repair</a></li>
                    <li><a href="/local_service_finder/pages/browse_services.php?category=Appliance+Repair">Appliance Maintenance</a></li>
                </ul>
            </div>

            <!-- Newsletter & Contact -->
            <div class="col-lg-3">
                <h5>Stay Updated</h5>
                <p class="text-muted small mb-3">Subscribe to our newsletter for exclusive discounts and home maintenance tips.</p>
                <form onsubmit="event.preventDefault(); alert('Thank you for subscribing!');" class="mb-3">
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark text-white border-secondary rounded-start-pill px-3" placeholder="Enter email..." required>
                        <button class="btn btn-primary rounded-end-pill px-3" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </form>
                <div class="text-muted small">
                    <i class="fa-solid fa-envelope me-2 text-primary"></i>support@servicefinder.com<br>
                    <i class="fa-solid fa-phone me-2 text-primary"></i>+1 (800) 123-4567
                </div>
            </div>
        </div>

        <div class="border-top border-secondary border-opacity-25 pt-4 text-center small text-muted">
            <p class="mb-0">&copy; <?= date('Y') ?> Local Service Finder Inc. All rights reserved. Built for engineering excellence.</p>
        </div>
    </div>
</footer>

<?php include __DIR__ . '/includes/footer.php'; ?>
