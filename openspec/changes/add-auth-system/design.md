# Design: Authentication and Authorization System

## 1. Database Schema Changes

- **Table:** `account`
- **Columns to Add:**
  - `Email`: `VARCHAR(100) NOT NULL UNIQUE`.
- **Columns to Modify:**
  - `Password`: Change from `VARCHAR(20)` to `VARCHAR(255)` to store hashed passwords using `password_hash()`.
- **Seed Data:**
  - An admin account will be pre-seeded into the database:
    - **Email:** `admin@gmail.com`
    - **Password:** `123456` (will be hashed)

## 2. New Components

### `AuthController.php`
- Handles all logic for login, logout, registration, and session management.
- `login()`: Shows the login form.
- `handleLogin()`: Processes POST data, validates credentials, sets session, redirects.
- `register()`: Shows the registration form.
- `handleRegister()`: Processes POST data, validates input, hashes password, creates a new user account.
- `logout()`: Destroys the session and redirects to the homepage.

### Auth Views (`app/views/auth/`)
- `login.php`: A simple form with Email and Password fields.
- `register.php`: A form for Username, Email, Fullname, and Password.

## 3. Authorization & Security Flow

### `app/views/shares/header.php`
- A new function `isAdmin()` will be added to check `$_SESSION['user']['role'] === 'admin'`.
- The visibility of admin-only UI elements (Add/Edit/Delete buttons, Order Management link) will be controlled by this function.

### All Admin-Route Controllers (`ProductController`, `OrderController`, `CategoryController`)
- **Protected Methods:** A check will be placed at the beginning of every method corresponding to an admin action (e.g., `add()`, `save()`, `edit()`, `update()`, `delete()`).
- **Logic:**
  ```php
  session_start();
  if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
      // Set flash message for "Permission Denied"
      // Redirect to login page or homepage
      return;
  }
  ```
- This ensures that even if a user tries to access an admin URL directly, the action is blocked on the server-side.

## 4. UI/UX Flow

- **Accessing Admin Area:** If a non-admin tries to access a protected URL, they will be redirected to the homepage with a "Permission Denied" SweetAlert2 popup.
- **Login/Register:** All success and error messages (e.g., "Wrong Password", "Email already exists", "Registration successful") will use the SweetAlert2 flash message system.
