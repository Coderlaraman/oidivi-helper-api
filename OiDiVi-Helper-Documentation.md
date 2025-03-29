# 🌐 OiDiVi Helper Documentation


## 1️⃣ Introduction
This document unifies the business rules, roles and permissions, and technical specifications of the OiDiVi Helper platform, covering both web and mobile applications.

---

## 2️⃣ System Architecture and Technologies

### 🔹 2.1 Backend Infrastructure
- **Core Framework**: 
  - PHP with Laravel
- **Authentication**: 
  - Laravel Sanctum for secure sessions
  - OAuth 2.0 for social media integration
- **Real-time Communication**: 
  - Socket.io for chat and instant notifications
- **Payment Processing**: 
  - Stripe/PayPal integration
  - Secure payment retention system

### 🔹 2.2 Frontend Technologies
- **Web Platform**:
  - Next.js (React) with SSR
  - Tailwind CSS for responsive design
  - Google Maps/Azure Maps API integration
- **Mobile Application**:
  - Flutter for cross-platform development
  - Alternative: React Native
  - Firebase Cloud Messaging for push notifications
  - Native geolocation services

### 🔹 2.3 Data Management
- **Relational Database**: MySQL/PostgreSQL
- **Caching**: Redis
- **File Storage**: Amazon S3/Google Cloud Storage
- **DevOps**:
  - Docker containerization
  - Kubernetes orchestration
  - CI/CD through GitHub Actions/Jenkins

---

## 3️⃣ Roles and Permissions System

### 🔹 3.1 User Roles
- **Admin**: Full platform control
- **Moderator**: Content and interaction supervision
- **Support**: Technical assistance
- **User**: Regular service seekers/providers
  - Sub-types:
    - Common Users: Service seekers
    - Helpers: Service providers

### 🔹 3.2 Permission Structure
#### General Permissions (All Users)
- `view_service_requests`
- `create_service_request`
- `update_own_profile`
- `send_messages`
- `make_payments`
- `rate_users`
- `view_user_profiles`

#### Role-Specific Permissions
- **Admin Permissions**
  - `manage_users`
  - `assign_roles`
  - `view_reports`
  - `delete_any_service_request`
  - `manage_payments`
  - `access_admin_dashboard`

- **Moderator Permissions**
  - `view_reports`
  - `suspend_users`
  - `edit_any_service_request`
  - `resolve_disputes`

- **Support Permissions**
  - `assist_users`
  - `resolve_tickets`
  - `moderate_content`

- **User Permissions**
  - `accept_service_request`
  - `cancel_service_request`
  - `update_own_service_request`
  - `track_service_status`

---

## 4️⃣ Core Business Rules

### 🔹 4.1 User Management
- Mandatory identity verification (email/phone)
- ZIP code-based location system
- Profile creation and management
- Manual verification for helpers

### 🔹 4.2 Service Management
- Detailed service request system
- Location-based matching
- Dynamic search radius
- Privacy controls for offers

### 🔹 4.3 Communication System
- Real-time messaging
- Push notifications
- File attachment support
- Location sharing

### 🔹 4.4 Payment and Billing
- Secure payment processing
- Electronic invoice generation
- Payment retention system
- Multi-payment gateway support

### 🔹 4.5 Rating and Reputation
- 5-star rating system
- Written reviews
- Reputation calculation algorithm
- Quality control measures

---

## 5️⃣ Platform-Specific Features

### 🔹 5.1 Web Platform
- Admin dashboard
- Document management
- Multi-language support
- Advanced search interface

### 🔹 5.2 Mobile Application
- Real-time location tracking
- Offline mode capabilities
- Push notification system
- Mobile-optimized UI

---

## 6️⃣ Security and Compliance
- International data protection standards
- Robust access control
- Data encryption
- Regular security audits

---

## 7️⃣ System Interactions
🔗 Service Publication ↔ Advanced Search
🔗 Contracting ↔ Secure Payments
🔗 Messaging ↔ Geolocation
🔗 Rating System ↔ User Reputation
🔗 Identity Verification ↔ Security

---
## 8️⃣ Specific Rules for Web Platform
### 🔹 8.1 Key Features
- **Intuitive web interface** optimized for user experience.
- Integration with **OiDiVi Skills** to display helper profiles with:
  - Documents (PDFs, resume).
  - Images (certifications, work samples).
  - Videos (skill demonstrations).
- **Multilingual support** for global expansion.
- **Administration panel** for user, payment, and content management.

---

## 9️⃣ Specific Rules for Mobile Application
### 🔹 9.1 Registration and Authentication
- Email registration with mandatory verification.
- Authentication through **JWT**.
- Social media login option (optional).

### 🔹 9.2 Service Request Publication
- Location defined through **LocationIQ API**.
- Inclusion of description, base price, and multimedia (photos/videos).
- Geocoding and coordinate storage.
- Cancellation option before helper acceptance.

### 🔹 9.3 Helper Assignment and Management
- Real-time visualization of active requests on map.
- Sending counteroffers with adjusted prices.
- Restriction of multiple offers by the same helper on a request.
- Blocking new offers once a helper is assigned.

### 🔹 9.4 Real-Time Tracking
- Real-time geolocation when accepting a service.
- Helper location view on the map.
- Notification when helper is near destination.
- Alert if helper disables geolocation before arrival.

### 🔹 9.5 Completion and Evaluation
- Mark request as **Completed** when finished.
- Rating and comments from both parties.
- Temporary suspension of helpers with repeated negative ratings.

### 🔹 9.6 Restrictions and Security
- A user cannot accept their own request.
- Restriction preventing rejected users from bidding again.
- Location verification to prevent false data.
- Security code to confirm service initiation in person.

---
