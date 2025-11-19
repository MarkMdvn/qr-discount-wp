# Epoint Custom QR Plugin Context

This document provides a summary of the Epoint Custom QR WordPress plugin's functionality, structure, and key components.

## 1. Project Overview

The Epoint Custom QR plugin is designed to generate unique QR codes and numeric discount codes for registered users on a WordPress and WooCommerce site. These codes can be used for exclusive discounts in physical stores.

### Core Features:

- **QR Code Generation**: Automatically generates a QR code and a unique numeric discount code for new users upon registration.
- **Email Notification**: Sends the QR code and discount code to the user via email.
- **Verification**: Allows store employees to verify the authenticity of the QR codes and apply discounts.
- **Control Panels**: Provides a transaction history panel for businesses and a central panel for administrators to manage all QR codes and transactions.
- **Transaction Logging**: Logs all discount transactions for auditing and reporting purposes.
- **Limited Availability**: The plugin is configured to generate a maximum of 1500 QR codes.
- **Postal Code Restriction**: The generation of QR codes is restricted to users with a postal code between 45000 and 45009.

## 2. File Structure

```
/root/bonocomercio/
├───composer.json
├───composer.lock
├───epoint-custom-qr.php
├───readme.md
├───assets/
│   └───css/
├───includes/
│   ├───class-db-handler.php
│   ├───class-mailer.php
│   ├───class-qr-generator.php
│   └───class-qr-verifier.php
├───templates/
│   ├───epoint-business-transactions.php
│   └───epoint-central-panel.php
└───vendor/
    ├───autoload.php
    ├───bacon/
    ├───composer/
    ├───dasprid/
    └───endroid/
```

## 3. Key Files and Components

### `epoint-custom-qr.php` (Main Plugin File)

- **Activation/Deactivation**: Handles plugin activation and deactivation hooks, including flushing rewrite rules and setting up the database table.
- **Includes**: Loads the necessary classes from the `includes` directory.
- **AJAX Handlers**:
    - `handle_qr_code_generation`: Handles the AJAX request to generate a QR code.
    - `filter_transactions_function`: Filters transactions based on a date range for the business panel.
    - `fetch_coupons_function`: Fetches coupons for the central panel.
    - `fetch_transactions_function`: Fetches transactions for the central panel.
- **Rewrite Rules**: Adds custom rewrite rules for the QR code verification URL.
- **Template Redirection**: Redirects to custom templates for the transaction history and central panel pages.

### `includes/class-qr-generator.php`

- **`QR_Generator` class**:
    - `generate_qr_code($user_id)`:
        - Checks if the QR code generation limit (1500) has been reached.
        - Sets an initial discount of €10.
        - Generates a 6-character unique alphanumeric code.
        - Uses the `endroid/qr-code` library to create a QR code image.
        - The QR code contains the URL to the verification page: `[site_url]/verify-qr/?user_id=[user_id]`.
        - Saves the QR code image to the `wp-content/uploads/qr-codes/` directory.
        - Stores QR code data in the database using the `DB_Handler` class.
- **Shortcodes**:
    - `[display_qr_code]`: Displays the user's QR code, numeric code, and remaining discount.
    - `[qr_code_availability]`: Shows the number of available QR codes.
    - `[generate_qr_code_button]`: Renders a button for eligible users to generate their QR code.

### `includes/class-db-handler.php`

- Manages all database interactions, including creating and managing the custom tables for QR codes and transactions.

### `includes/class-mailer.php`

- Handles sending emails to users with their QR code and discount code.

### `includes/class-qr-verifier.php`

- Provides the functionality for employers to verify QR codes and apply discounts.

### `templates/`

- **`epoint-business-transactions.php`**: Template for the business transaction history panel.
- **`epoint-central-panel.php`**: Template for the administrator's central management panel.

## 4. Dependencies

The plugin has one main dependency, which is managed via Composer:

- **`endroid/qr-code`**: A PHP library for generating QR codes.

## 5. Shortcodes

- **`[display_qr_code]`**: Displays the QR code, numeric code, and remaining discount for the logged-in user.
- **`[verify_qr_code]`**: Provides a form for employers to verify and apply discounts.
- **`[qr_code_availability]`**: Displays the number of QR codes still available.
- **`[generate_qr_code_button]`**: Shows a button to generate a QR code for users who meet the criteria (logged in, within the specified postal code range, and have not yet generated a code).
