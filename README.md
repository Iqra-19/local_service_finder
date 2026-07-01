# Local Service Finder 🛠️

A modern, robust Local Service Marketplace built with **Core PHP**, **MySQL**, and **Bootstrap 5**. This platform connects local service providers with customers, managing the entire lifecycle of booking requests, peer-to-peer messaging, geolocation-based searches, secure payments, and service reviews.

---

## 🚀 Recruiter Quick Start (Demo Sandbox)

To make evaluating this application as frictionless as possible, the login screen is pre-configured with demo credentials for quick access:

- **Demo URL:** `http://localhost/local_service_finder/pages/login.php`
- **Prefilled Account Credentials:**
  - **Email:** `volt.repair@services.com`
  - **Password:** `Password123`

---

## ✨ Core Features

### 👤 Customer Features

- **Geographical Search & Filters:** Browse services nearby using Leaflet maps. Customers can filter by keyword, category, or distance radius, with results sorted by price, rating, or proximity.
- **Interactive Map Integration:** Real-time distance calculations and map markers showing nearby providers. Hovering over service listings highlights the corresponding map pin.
- **Seamless Service Booking:** Request appointments for future dates with customized requirements and notes.
- **P2P Messaging Interface:** Asynchronous peer-to-peer messaging with providers to negotiate pricing, finalize arrival times, or coordinate details. Designed to run efficiently on shared hosting using resource-friendly adaptive polling.
- **Simulated Payment Gateway:** Pay for accepted services securely with simulated UPI, Credit/Debit Card, Net Banking, or Wallet options with instant invoice receipt generation.
- **Review & Rating Engine:** Rate completed services from 1 to 5 stars with comment logs to build community trust.
- **Google OAuth Integration:** Secure single sign-on option via Google Account. Prompts new users to select their role (`user` or `provider`) during initial signup, or links with existing local accounts via email.
- **Self-Serve Password Recovery:** Robust token-based forgot/reset password system with a built-in local recovery link generator.

### 💼 Provider Features

- **Interactive Business Dashboard:** Monitor active services, track cumulative earnings, check operational metrics (Acceptance Rate and Completion Rate), and view monthly earnings trend lines.
- **Service Earnings & Payout Tracker:** Complete detailed table summarizing booking reference, service titles, customer info, booking date, status, and client payment details.
- **Service Management (CRUD):** Add, edit, or disable services, set customized pricing, upload showcase photos, and pin precise service locations on the map.
- **Real-time Queue Management:** Accept or reject incoming booking requests with automatic customer status updates.
- **Real-Time Unread Notification Badge:** Topbar notification bell dynamically alerts users to new unread system messages and updates.

### 🔑 Admin Features

- **Financial Analytics Panel:** Overview of platform financials tracking total collected revenue, count of successful transactions, and the pending pipeline.
- **Transaction Logs Management:** Centralized dashboard for administrators to view, filter (by payment status and method), and search through all transactions by transaction ID, customer/provider name, or service title.
- **Invoice & Receipt Access:** Instant retrieval of customer receipts directly from the admin panel.

---

## 🔒 Security & Architecture

This application was engineered with a focus on web application security, clean database design, and smooth user interactions:

1. **Prepared SQL Statements (PDO):** Complete defense against SQL Injection (SQLi) vulnerabilities across all read and write queries.
2. **Cross-Site Request Forgery (CSRF) Defenses:** Hidden CSRF token verification (`csrfInput()`, `verifyCsrfToken()`) implemented across state-changing POST requests.
3. **Session Hardening & Cookie Protection:** Enforced `httponly = true`, `SameSite = Lax`, and defensive session regeneration to protect against session hijacking and XSS cookie theft.
4. **Secure Password Hashing:** User passwords encrypted using `bcrypt` (default Blowfish algorithm with custom safety parameters).
5. **Role-Based Access Control (RBAC):** Strict separation of customer (`user`), service provider (`provider`), and administrative (`admin`) routes.
6. **Google OAuth Token Verification:** Integrates with Google's secure `tokeninfo` endpoint to verify authentication credentials securely on the backend.
7. **Double-Submission Protection & Loading UX:** Automatic JavaScript button disabling and spinner states (`Processing...`) on form submissions to prevent accidental duplicate entries.
8. **Cascading Relational Integrity:** Built-in MySQL foreign keys ensure that deletions cascade gracefully without orphan rows.

---

## 💻 Tech Stack

- **Backend Engine:** Core PHP 8.x (Object-Oriented Database connection via PDO)
- **Database Layer:** MySQL (Structured Relational Schema with InnoDB engine)
- **Frontend Design:** HTML5, Modern CSS3, Bootstrap 5.3 (featuring glassmorphism accents)
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
4. *(Optional)* If upgrading an existing installation, execute the queries in `auth_alter_queries.sql` to add Google OAuth and Password Reset support.

### 3. Connection Configuration

The application connects using the credentials defined in `config/db.php`. By default:

- **Host:** `localhost`
- **Database:** `local_service_finder`
- **Username:** `root`
- **Password:** _(empty)_

Update `config/db.php` if your local MySQL settings differ.

#### Google OAuth Setup
If you want to use Google Sign-in:
1. Obtain client credentials from the [Google Cloud Console](https://console.cloud.google.com/).
2. Add your **Google Client ID** in `config/google_config.php`.

### 4. Running the Application

Point your browser to:

```text
http://localhost/local_service_finder
```

---

## 🗄️ Database Schema Relationships

The database is built on relational tables indexed for high-performance querying:

- `users` — Stores account details, coordinates (latitude/longitude), Google OAuth IDs, password reset tokens, expiration dates, and role flags (`user`, `provider`, `admin`).
- `services` — Maps service titles, categories, pricing, descriptions, images, and geolocation points.
- `bookings` — Bridge table connecting users, providers, and services under lifecycle status states (`pending`, `accepted`, `completed`, `rejected`, `cancelled`).
- `payments` — Records payment transactions, amounts, methods (UPI, Card, NetBanking, Wallet), and unique transaction IDs.
- `reviews` — Logs rating scores (1-5 stars) and customer text feedback linked to completed bookings.
- `messages` — Handles peer-to-peer message logs between customers and service providers.
- `favorites` — Maps bookmark selections linking customer `users` to favorited `services`.
- `notifications` — Tracks system alerts and unread notifications per user.
