# User Authentication Documentation

## Overview

This documentation covers the authentication flow for the OIDIVI Helper platform, including registration, login, email verification, and profile management.

## Authentication Endpoints

### 1. Registration

```typescript
POST / api / v1 / auth / register;

// Request Body
interface RegisterRequest {
  name: string; // Full name
  email: string; // Valid email address
  password: string; // Min 8 characters
  password_confirmation: string;
  accepted_terms: boolean;
  phone: string; // Valid phone number
  address: string;
  zip_code: string;
  latitude: number; // Valid latitude (-90 to 90)
  longitude: number; // Valid longitude (-180 to 180)
}

// Response
interface RegisterResponse {
  success: boolean;
  message: string;
  data: {
    token: string;
    user: User;
  };
}
```

### 2. Login

```typescript
POST / api / v1 / auth / login;

// Request Body
interface LoginRequest {
  email: string;
  password: string;
}

// Response
interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    token: string;
    user: User;
  };
}
```

### 3. Email Verification

```typescript
// Send verification email
POST / api / v1 / email / verification - notification;

// Verify email
GET / api / v1 / email / verify / { id } / { hash };

// Response
interface VerificationResponse {
  success: boolean;
  message: string;
}
```

## Authentication Flow

1. Registration Process :

   - User submits registration form
   - System creates account and sends verification email
   - User receives API token immediately
   - Redirect to dashboard with verification reminder

2. Login Process :

   - User submits credentials
   - System validates email verification status
   - If verified, provides token and user data
   - If unverified, returns 403 with verification message

3. Protected Routes :

   - Include token in Authorization header
   - Format: Bearer {token}
   - Handle 401/403 responses appropriately

## Role-Based Access Control

```typescript
// Available Roles
type UserRole = "admin" | "moderator" | "support" | "user";

// Role Checking Methods
interface RoleChecks {
  hasRole(role: string): boolean;
  hasAnyRole(roles: string[]): boolean;
  hasAllRoles(roles: string[]): boolean;
}
```

## Implementation Example

```typescript
// auth.service.ts
class AuthService {
  async register(data: RegisterRequest): Promise<RegisterResponse> {
    const response = await api.post("/auth/register", data);
    this.setToken(response.data.token);
    return response.data;
  }

  async login(credentials: LoginRequest): Promise<LoginResponse> {
    const response = await api.post("/auth/login", credentials);
    this.setToken(response.data.token);
    return response.data;
  }

  async verifyEmail(id: string, hash: string): Promise<VerificationResponse> {
    return await api.get(`/email/verify/${id}/${hash}`);
  }

  private setToken(token: string): void {
    localStorage.setItem("auth_token", token);
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  }
}
```

## Protected Routes Setup

```typescript
// route-guard.ts
const authGuard = (to: Route, from: Route, next: Function) => {
  const token = localStorage.getItem("auth_token");

  if (!token) {
    return next({ name: "login" });
  }

  const user = store.getters["auth/user"];

  // Role-based access control
  if (to.meta.roles && !hasRequiredRole(user, to.meta.roles)) {
    return next({ name: "unauthorized" });
  }

  next();
};
```

## Error Handling

Handle these specific status codes:

- 401: Unauthorized (invalid/expired token)
- 403: Forbidden (unverified email or insufficient permissions)
- 422: Validation errors
- 429: Too many attempts

```typescript
// error-interceptor.ts
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response.status === 401) {
      // Clear auth state and redirect to login
    }
    if (error.response.status === 403) {
      // Show verification reminder or insufficient permissions message
    }
    return Promise.reject(error);
  }
);
```

## User Profile Management

After successful authentication, implement:

1. Profile data fetching
2. Profile updates
3. Media uploads (photo/video)
4. Role/permission checks for features
   Remember to:

- Implement proper token storage
- Handle token refresh
- Implement logout functionality
- Add loading states
- Show appropriate error messages
- Validate forms before submission
- Handle file uploads properly
- Implement proper route guards
