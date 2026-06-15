# Local Service Finder 🛠️

A modern, robust Local Service Marketplace built with **Core PHP**, **MySQL**, and **Bootstrap 5**. This platform connects local service providers with customers, managing the entire lifecycle of booking requests, peer-to-peer messaging, geolocation-based searches, and service reviews.

---

## 🚀 Recruiter Quick Start (Demo Sandbox)

To make evaluating this application as frictionless as possible, the login screen is pre-configured with a **HERO Provider** account that simulates a mature, 3-month-old service business:

- **Demo URL:** `http://localhost/local_service_finder/pages/login.php`
- **Prefilled Account Credentials:**
  - **Email:** `volt.repair@services.com`
  - **Password:** `Password123`
- **Manual Setup Guide:** To populate the complete interconnected database (reviews, messages, rejections, pending actions, and maps), follow the step-by-step checklist in [demo_creation_guide.md](file:///C:/Users/iqrat/.gemini/antigravity-ide/brain/e5f354b0-4024-4bd5-9057-243e5fdd3863/demo_creation_guide.md).

---

## ✨ Core Features

### 👤 Customer Features

- **Geographical Search & Filters:** Browse services nearby using Leaflet maps. Customers can filter by keyword, category, or distance radius, with results sorted by price, rating, or proximity.
- **Interactive Map Integration:** Real-time distance calculations and map markers showing nearby providers. Hovering over service listings highlights the corresponding map pin.
- **Seamless Service Booking:** Request appointments for future dates with customized requirements and notes.
- **P2P Messaging Interface:** Asynchronous peer-to-peer messaging with providers to negotiate pricing, finalize arrival times, or coordinate details. Designed to run efficiently on shared hosting (like InfinityFree) using resource-friendly adaptive polling.
- **Review & Rating Engine:** Rate completed services from 1 to 5 stars with comment logs to build community trust.

### 💼 Provider Features

- **Interactive Business Dashboard:** Monitor active services, track cumulative earnings, check operational metrics (Acceptance Rate and Completion Rate), and view monthly earnings trend lines.
- **Service Management (CRUD):** Add, edit, or disable services, set customized pricing, upload showcase photos, and pin precise service locations on the map.
- **Real-time Queue Management:** Accept or reject incoming booking requests with automatic customer status updates.
- **Dynamic Notifications:** Red unread message badges alert the provider of incoming messages.

---

## 🔒 Security & Architecture

This application was engineered with a focus on web application security, clean database design, and smooth user interactions:

1. **Prepared SQL Statements (PDO):** Complete defense against SQL Injection (SQLi) vulnerabilities across all read and write queries.
2. **Secure Password Hashing:** User passwords are encrypted using `bcrypt` (default Blowfish algorithm with custom safety parameters) at registration.
3. **Session Integrity & Protection:** Defensive session validations, login rate-limiting, and automatic lockout mechanics to prevent brute-force attacks.
4. **Role-Based Access Control (RBAC):** Strict separation of customer (`user`) and service provider (`provider`) routes. Attempting to access dashboard files without appropriate credentials triggers an instant redirection.
5. **Cascading Relational Integrity:** Built-in MySQL foreign keys ensure that deletions (e.g., deleting a service or user) cascade gracefully without leaving dangling or orphan rows.
6. **Asynchronous UI (Fetch AJAX):** Search filtering, sorting, map pins, and messages update dynamically without page reloads, utilizing an adaptive backoff polling algorithm to conserve hosting resources.

---

## 💻 Tech Stack

- **Backend Engine:** Core PHP 8.x (Object-Oriented Database connection via PDO)
- **Database Layer:** MySQL (Structured Relational Schema)
- **Frontend Design:** HTML5, Modern CSS3, Bootstrap 5.3 (featuring clean glassmorphism accents)
- **Interactivity:** Vanilla JavaScript (ES6+), Fetch API (asynchronous communication)
- **Map Engine:** Leaflet.js & OpenStreetMap API

---

## 🛠️ Local Installation Setup

Follow these steps to run the project locally via standard local environments (e.g. XAMPP, MAMP, WAMP).

### 1. Project Directory Placement

Clone or move the repository into your local server's document root (e.g., `htdocs` for XAMPP, `www` for WAMP):

```bash
cd xamp/htdocs
git clone <repository_url> local_service_finder
```

### 2. Import Database Schema

1. Launch **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a new database named `local_service_finder` with collation `utf8mb4_general_ci`.
3. Import the `database.sql` file located in the root of the project.

### 3. Connection Configuration

The application connects using the credentials defined in `config/db.php`. By default:

- **Host:** `localhost`
- **Database:** `local_service_finder`
- **Username:** `root`
- **Password:** _(empty)_

Update `config/db.php` if your local MySQL settings differ.

### 4. Running the Application

Point your browser to:

```text
http://localhost/local_service_finder
```

---

## 🗄️ Database Schema Relationships

The database is built on 5 relational tables:

- `users` — Stores account details, coordinates (latitude/longitude), and role flags.
- `services` — Maps service titles, categories, pricing, descriptions, images, and locations.
- `bookings` — Bridge table connecting users, providers, and services under 5 status states (`pending`, `accepted`, `completed`, `rejected`, `cancelled`).
- `reviews` — Logs rating scores and customer text feedback linked to completed bookings.
- `messages` — Handles peer-to-peer message logs with a read status indicator.
