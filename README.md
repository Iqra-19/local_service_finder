# Local Service Finder: System Architecture & Functionality Overview

The Local Service Finder is a fully operational, production-ready marketplace application engineered using Core PHP, MySQL (PDO), and Bootstrap 5. It facilitates a robust, bi-directional marketplace where Service Providers can post specific offerings and Customers can seamlessly search, book, and review those services.

---

## 1. Core Technology Stack
- **Backend:** Core PHP (Procedural & Structural)
- **Database:** MySQL relational database utilizing strict `PDO` queries for 100% SQL Injection protection.
- **Frontend UI/UX:** HTML5, Bootstrap 5, custom CSS (Glassmorphism), Vanilla JavaScript.
- **Assets Integration:** Bootstrap Icons for modern iconography.

---

## 2. Database Schema
The foundation of the platform relies on four highly normalized relational structures:

1. **`users` Table:** Stores identity metadata. Distinguishes accounts exclusively via a strict ENUM `role` parameter (`'user'`, `'provider'`).
2. **`services` Table:** The primary product listing table. Hard-linked to the provider who created it via `provider_id`. Supports categorical filtering natively.
3. **`bookings` Table:** The absolute backbone of the transaction lifecycle. Binds `service_id`, `user_id`, and `provider_id`. Controlled strictly by its ENUM status: `pending`, `accepted`, `completed`, `rejected`, or `cancelled`.
4. **`reviews` Table:** The core metric aggregation table. Tracks `rating` (1-5 scale) mapped explicitly against completed bookings.

---

## 3. Security & Authentication Layer
A premium SaaS-grade authentication bridge was engineered:
- **Visuals Isolated:** Auth layouts (`login.php`, `register.php`) are structurally disconnected from the dashboard utilizing custom `auth.css` projecting a breathtaking Light Glassmorphism UI.
- **Form Constraints:** Employs strict HTML5 boundaries alongside DOM-blocking matching criteria. Backend PHP relies on regex parameter bounding (assuring name integrity, minimum 8 characters spanning numbers and letters).
- **Session Hardening:** Eliminates session-fixation hacking by generating `session_regenerate_id(true)`. Implements server-side bruteforce detection algorithms explicitly dropping 15-minute lockouts upon 5 consecutive failed login attempts.

---

## 4. The Customer (User) Workflow
The "Demand Side" UX empowers the customer to locate precisely what they need safely:

* **Customer Dashboard:** A highly intelligent entry point highlighting "Top Rated Services" algorithmically, listing active/upcoming events, and rendering a chronological history allowing rapid "Book Again" execution on finished tasks.
* **Browse Services System:** Engineered as a dual-method setup. Visually maps items dynamically computing `AVG(rating)`. Supported explicitly by an **AJAX Live Search** engine. Users can bounce constraints through "Dropdown Sorts" (Price, Rating, Date) while dynamically filtering keystrokes instantly without hard UI refreshes.
* **Booking & Cancel Flow:** Bookings inherently lock into a `pending` state upon generation. A specialized protection algorithm ensures Customers can inherently "Cancel" incorrect bookings securely *before* a Provider accepts it.
* **Review Verification:** Customers are firmly blocked from submitting numeric or text rating payloads until the backend explicitly validates their isolated booking equals `status = 'completed'`.

---

## 5. The Provider Workflow
The "Supply Side" is designed as a rigorous operational workspace minimizing friction:

* **Health Dashboard:** Instead of generating flat datasets natively, the Provider Dashboard is an analytical workspace. Native code equations determine the Provider's overarching **Acceptance Rate** and **Completion Rate** projecting them across sleek UI Progress Bars alongside hard **Earnings Math System** `(SUM(s.price))`. 
* **Service Authority:** Providers hold 100% CRUD (Create, Read, Update, Delete) capability over their specific fleet of `services`, bound by strict backend constraints preventing them from interacting with competitors' tables. 
* **Operational Control Room:** Pending Booking Requests natively materialize straight onto the central dashboard layout. Direct action buttons hook seamlessly into `update_booking.php` routing instantaneous `Accept`/`Reject` behaviors dynamically transitioning database workflows seamlessly.
* **Dedicated Analytics:** `provider_reviews.php` establishes an exclusive sidebar tab dedicated entirely to aggregating external voice-of-customer ratings scaling dynamically as a centralized reporting mechanic.

---

## 6. The User Experience (UX) Framework
The entire platform prioritizes feeling "Alive":
* All physical currency occurrences are universally tied tightly natively to the Indian Rupee (`₹`). 
* Notifications never disrupt HTML flow organically; instead, the system heavily incorporates native Vanilla JS auto-dismissing framework mapping Bootstrap **Toast components**. System events correctly manifest as smooth, glowing popovers dropping into the bottom-right UI before softly exiting.
* Deep usage of `.rounded-4` borders, hover elevations, and explicit "Empty State" UI layouts ensuring an absolutely immaculate SaaS appearance.
