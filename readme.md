# Epoint Custom QR

Generate and manage custom QR codes with discounts for registered users. Allow employers to verify codes and apply discounts seamlessly.

---

## Features

- Automatically generates a QR code and a unique numeric discount code for new users upon registration.
- Sends QR codes and discount codes via email.
- Enables employers to verify and apply discounts using custom roles and capabilities.
- Includes a customizable discount calculator.
- Provides control panels for businesses and administrators:
    - **Transaction History Panel**: For businesses to view their transaction history.
    - **Central Panel**: For administrators to manage all QR codes and transactions.
- Logs all discount transactions for auditing and reporting.

---

## Installation

1. Upload the plugin files to the `/wp-content/plugins/epoint-custom-qr` directory, or install the plugin through the WordPress Plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Ensure your site meets the following requirements:
    - **PHP**: Version 7.4 or higher.
    - **Composer**: Required for the Endroid QR Code library.

---

## Usage

### For Users

- Upon registration, a QR code and numeric discount code are generated automatically.
- Users can view their QR code and discount details using the `[display_qr_code]` shortcode.
- QR codes and discount details are also sent via email.

### For Employers

- Employers can verify discount codes via a dedicated page using the `[verify_qr_code]` shortcode.
- Employers can apply discounts through an interactive calculator.

### Control Panels

#### 1. **Transaction History Panel**

- **Template File**: `templates/epoint-business-transaction.php`
- **Access Level**: Only accessible to logged-in business users.
- **Functionality**:
    - Businesses can view all transactions associated with their account.
    - A date filter allows businesses to select specific date ranges for viewing transaction history.

#### 2. **Central Panel**

- **Template File**: `templates/epoint-central-panel.php`
- **Access Level**: Only accessible to administrators.
- **Functionality**:
    - Displays all QR codes generated for all users on the site.
    - Shows all transactions performed by all businesses registered on the site.
    - Provides centralized control for managing coupons and tracking activity across the platform.

---

## Shortcodes

- **`[display_qr_code]`**: Displays the user's QR code, numeric code, and remaining discount.
- **`[verify_qr_code]`**: Provides a form for employers to verify and apply discounts.

---

## FAQ

### What happens if a user loses their QR code?

Users can log in to their account and view the QR code via the `[display_qr_code]` shortcode.

### Can employers view transaction history?

Yes, businesses can view transaction history on the Transaction History Panel. Administrators can view all transactions on the Central Panel.

### Is the discount reusable?

Discounts are limited to the remaining balance. Once the balance is fully used, the QR code is marked as "used."

---

## Screenshots

1. **User QR Code Display**: Example of the QR code and discount details shown to users.
2. **Employer Verification Page**: Form for verifying discount codes.
3. **Discount Calculator**: Interactive calculator for applying discounts.
4. **Transaction History Panel**: Date-filtered transaction history for businesses.
5. **Central Panel**: Centralized dashboard for administrators to view all coupons and transactions.

---

## Changelog

### Version 1.1

- Added control panels:
    - **Transaction History Panel** for businesses.
    - **Central Panel** for administrators.
- Enhanced logging for QR code and discount transactions.

### Version 1.0

- Initial release:
    - QR code and numeric discount code generation upon user registration.
    - Email delivery of QR codes.
    - Employer verification and discount application.
    - Transaction logging for audit and reporting.

---

## Development Notes

### Folder Structure

- `assets/`: Plugin assets (CSS, JS, images).
- `includes/`: Core classes and functionality:
    - `class-qr-generator.php`: Handles QR code generation.
    - `class-mailer.php`: Sends QR codes via email.
    - `class-db-handler.php`: Manages database operations and logs transactions.
    - `class-qr-verifier.php`: Provides employer verification features.
- `templates/`: HTML templates for custom outputs:
    - `epoint-business-transaction.php`: Transaction history panel for businesses.
    - `epoint-central-panel.php`: Central management panel for administrators.
- `vendor/`: Third-party dependencies, including the Endroid QR Code library.

### Dependencies

- Install required dependencies using Composer:

  bash

  Copy code

  `composer install`


---

## Future Plans

- Add more filtering options for transaction history.
- Enhance reporting features in the Central Panel.
- Introduce export options for transaction data (CSV, Excel).
- Allow customization of email templates for sending QR codes.

---

