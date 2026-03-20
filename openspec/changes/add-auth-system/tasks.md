# Tasks: Authentication and Authorization System

## Phase 1: Database and Core Auth Logic

1.  [ ] **Update `Query.sql`**:
    -   Add `Email VARCHAR(100) NOT NULL UNIQUE` to the `account` table.
    -   Modify `Password` column to `VARCHAR(255) NOT NULL`.
    -   Add a pre-seeded admin account: `admin@gmail.com` with a hashed password.
2.  [ ] **Create `app/models/AccountModel.php`**:
    -   `findByEmail($email)`: To check for existing users and for login.
    -   `create($username, $email, $fullname, $hashedPassword)`: To register new users.
3.  [ ] **Create `app/controllers/AuthController.php`**:
    -   `login()`: Renders the login view.
    -   `handleLogin()`:
        -   Validates POST data.
        -   Uses `AccountModel->findByEmail()` to fetch user.
        -   Verifies password with `password_verify()`.
        -   Sets `$_SESSION['user']` on success.
        -   Redirects with flash messages for success/failure.
    -   `register()`: Renders the register view.
    -   `handleRegister()`:
        -   Validates POST data (check if email exists).
        -   Hashes password with `password_hash()`.
        -   Calls `AccountModel->create()`.
        -   Redirects with flash message.
    -   `logout()`: Destroys session and redirects.
4.  [ ] **Create Auth Views**:
    -   `app/views/auth/login.php`
    -   `app/views/auth/register.php`
5.  [ ] **Update `index.php` Router**: Add routes for `AuthController` methods.

## Phase 2: Authorization and UI Integration

6.  [ ] **Update `app/views/shares/header.php`**:
    -   Add Login/Register/Logout buttons based on session status.
    -   Conditionally display admin-only links (e.g., "Thêm SP", "Đơn hàng") if `$_SESSION['user']['role'] === 'admin'`.
7.  [ ] **Secure `ProductController.php`**:
    -   Add a `session_start()` and an admin check at the beginning of `add()`, `save()`, `edit()`, `update()`, and `delete()`.
    -   Redirect with a "Permission Denied" flash message if the check fails.
8.  [ ] **Secure `OrderController.php`**:
    -   Add an admin check to `index()` (Order Management list).
    -   The `history()` method should remain public for now.
9.  [ ] **Secure `CategoryController.php`**:
    -   Add an admin check to `add()` and `save()`.
10. [ ] **Update Product List View (`app/views/product/list.php`)**:
    -   Wrap all admin action buttons ("Sửa", "Xóa") in an `if (isAdmin())` block.
11. [ ] **Update Order List View (`app/views/order/list.php`)**:
    -   Ensure this page is only accessible by admins.
12. [ ] **Final Review**: Test all paths for both guest, user, and admin roles.
