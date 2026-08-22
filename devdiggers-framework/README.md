# DevDiggers Plugin Framework

The **DevDiggers Framework** is the "engine" that powers all premium plugins from DevDiggers. It is a shared library of tools that ensures every plugin works reliably, updates securely, and looks beautiful in the WordPress admin dashboard.

## What it does for you:

*   **Shared Admin Dashboard**: Instead of having ten different menus for ten different plugins, the framework groups everything under one clean "DevDiggers" menu.
*   **Safe Automatic Updates**: It handles the background checks that tell you when a new version of a plugin is available. It makes sure that updates only happen if you have a valid, active license.
*   **Faster, Smarter UI**: The framework provides the "bricks" (layouts, form fields, and icons) that we use to build our plugins. This means less code in individual plugins and a more consistent experience for you.
*   **Security & Verification**: It includes built-in tools to help verify that your license is authentic, protecting you from counterfeit or "nulled" software.

## Key Features for Developers

*   **Plugin Updater**: A robust class to handle remote updates with license validation.
*   **AJAX Helpers**: Clean ways to handle background tasks without slowing down the site.
*   **Global Assets**: Shared CSS and JS to keep the total plugin size small and performance high.
*   **Review Notices**: Built-in system to gently ask users for feedback and ratings.

## How it works

The framework is typically bundled inside our premium plugins. You don't usually need to install it separately. When you activate one of our plugins, the framework starts up automatically to provide the necessary structure and tools.

---
**Looking for support?**
If you have questions about any DevDiggers plugin, please contact us at [support@devdiggers.com](mailto:support@devdiggers.com) or visit our website at [devdiggers.com](https://devdiggers.com).