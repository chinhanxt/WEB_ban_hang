# Proposal: Add Authentication and Authorization System

## What is this change?

This change introduces a comprehensive authentication (Login/Register) and authorization (Admin/User roles) system into the web application. It will secure the admin functionalities while preparing for future user-specific features.

## Why is this change being made?

Currently, all administrative functions (add, edit, delete products/orders) are publicly accessible. This is a significant security risk. This system will:
- **Secure Admin Routes:** Protect all administrative actions from unauthorized access.
- **Establish User Roles:** Create a clear distinction between 'admin' and 'user' roles, laying the foundation for personalized user experiences (e.g., viewing order history).
- **Improve Professionalism:** Use modern, user-friendly notifications (SweetAlert2) for all auth-related feedback, enhancing the overall user experience.
- **Future-Proofing:** Although guest checkout is still supported, this system is a prerequisite for requiring login for purchases or enabling user-specific features later on.
