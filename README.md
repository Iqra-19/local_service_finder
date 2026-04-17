# Local Service Finder 🛠️

A modern, robust Local Service Marketplace built with **Core PHP**, **MySQL**, and **Bootstrap 5**. This platform seamlessly connects local service providers with customers looking for various services (plumbing, cleaning, IT support, etc.), providing end-to-end workflows from booking creation to service review.

![Glassmorphism UI](https://img.shields.io/badge/UI-Glassmorphism-blue?style=flat-square) ![PHP](https://img.shields.io/badge/PHP-Core-777BB4?style=flat-square&logo=php) ![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)

---

## ✨ Key Features

### 👤 Customer Features
* **Live Service Browsing:** Instantly search and filter through available services using asynchronous AJAX — without reloading the page.
* **Service Booking system:** Book services smoothly with custom notes.
* **Booking Management:** View booking history, update pending bookings, and cancel requests directly from the user dashboard.
* **Review & Rating System:** Leave detailed 1-5 star reviews on completed services to help the community.

### 💼 Provider Features
* **Service Management Engine:** Fully featured CRUD functionality to add, edit, and delete their service offerings.
* **Request Handling:** Manage incoming bookings (Accept/Reject) visually from a tailored Provider Dashboard.
* **Performance Dashboard:** Monitor active services, track earnings, and view cumulative community ratings and reviews.

### 🛡️ System & Architecture
* **Role-Based Authentication:** Distinct and heavily guarded routing separating `user` and `provider` access.
* **Advanced UI & Glassmorphism:** Features a highly modern auth UI demonstrating frosted glass aesthetics built with vanilla CSS.
* **Automated Flash Messaging:** User actions return contextual feedback across pages using a custom session-based flash messaging utility.
* **Secure Database Layer:** Complete use of PHP PDO (prepared statements) to prevent SQL Injection, alongside relational DB rules enforcing cascading integrity.

---

## 💻 Tech Stack

* **Backend:** PHP 8.x (Core)
* **Database:** MySQL
* **Frontend Design:** HTML5, CSS3, Bootstrap 5.3
* **Interactivity:** Vanilla JavaScript, AJAX (fetch API)

---

## 🚀 Installation & Local Setup

Follow these steps to run the project locally via XAMPP/MAMP/WAMP.

### 1. Prerequisites
- A local web server stack like [XAMPP](https://www.apachefriends.org/index.html) or [MAMP](https://www.mamp.info/).
- PHP 8.0+ and MySQL.

### 2. Clone the Repository
Inside your local `htdocs` (XAMPP) or `www` (WAMP) folder, clone the directory:
```bash
git clone <repository_url> local_service_finder
cd local_service_finder
```

### 3. Database Setup
1. Open **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
2. Create a new database named `local_service_finder`.
3. Import the exact schema and tables by uploading the `database.sql` file located in the root of the project.

### 4. Configuration Check
By default, the database connection assumes a default XAMPP configuration:
* **Host:** `localhost`
* **Dataset Name:** `local_service_finder`
* **Username:** `root`
* **Password:** *(empty)*

If your MySQL credentials differ, update them directly in `config/db.php`.

### 5. Launch the Application
Open your web browser and navigate to:
```text
http://localhost/local_service_finder
```

---

## 🗄️ Database Architecture

The system operates across 4 tightly coupled tables mapping relational interactions:
1. `users` — Tracks accounts, passwords, and user `role` (user/provider).
2. `services` — Belongs to a provider; holds classification, description, and pricing data.
3. `bookings` — Bridge table managing states (`pending`, `accepted`, `completed`, `cancelled`) between a user, provider, and service.
4. `reviews` — Links feedback to specific completed bookings.

---

## 🔮 Future Enhancements Roadmap
- [ ] **Email Notifications:** Integrate PHPMailer to send alerts on booking status changes.
- [ ] **Payment Gateway:** Implement Stripe/PayPal for upfront booking deposits.
- [ ] **Interactive Analytics:** Use Chart.js on the Provider Dashboard to visualize monthly booking trends.
- [ ] **Admin Panel:** Introduce a 3rd `admin` role for total platform moderation (dispute handling, user bans).

---

*Architected and developed with modern Core PHP best-practices in mind.*
