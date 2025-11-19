# Epoint Custom QR - WordPress Discount Plugin

## Project Overview

This repository contains the source code for **Epoint Custom QR**, a bespoke WordPress plugin developed from scratch for the digital business agency **Gestión de Negocios Digitales S.L. (epoint.es)**. The primary objective of this project was to automate and manage a hyper-localized promotional campaign in Toledo, Spain.

The plugin creates a sophisticated ecosystem for companies and consumers, integrating QR code technology, dynamic discount generation, and multi-layered dashboard management. It replaces a manual approval and tracking process with a seamless, automated, and auditable system. This solution required deep integration with the WordPress user system and two other third-party plugins to create a unified and powerful promotional tool.

## The Business Challenge

The client, epoint.es, was launching a marketing campaign to stimulate the local economy in Toledo. The initial system required manual verification and approval for every participating company and a cumbersome process for tracking user discounts. This created significant administrative overhead and lacked real-time data for analysis.

The challenge was to architect a self-sustaining digital platform that could:
* Differentiate between participating companies and general consumers.
* Automatically issue and manage unique, location-aware discount coupons.
* Provide a secure and intuitive method for companies to validate and redeem these coupons.
* Offer detailed, real-time transaction reporting for users, companies, and a central administrator.

## Core Features & Functionality

### 1. Dual User Role System
The plugin extends the native WordPress user system to create two distinct roles with unique capabilities:
* **Affiliated Companies:** Businesses approved for the campaign gain access to a dedicated control panel.
* **Normal Users:** Consumers within the specified geographical area (Toledo, Castilla-La Mancha) who can receive and use discounts.

### 2. Dynamic & Geo-Targeted Coupon Generation
When a "Normal User" registers and their location is confirmed to be within Toledo, the plugin automatically performs the following actions:
* **Generates a unique €10 discount coupon.**
* **Creates a scannable QR code** and a corresponding numeric code for the coupon.
* **Populates the user's personal dashboard** with their coupon details, including the remaining balance.


### 3. Company Verification & Transaction Panel
Registered companies have access to a secure dashboard designed for in-store use by their employees. This panel is the core of the redemption process:
* **Dual Verification Method:** Employees can either scan a user's QR code using a webcam or smartphone camera, or manually input the numeric coupon code.
* **Intelligent Discount Calculation:** Upon successful verification, the system prompts the employee to enter the customer's total purchase amount. It then automatically calculates and suggests applying 10% of the purchase value against the user's €10 coupon balance.
* **Transaction Confirmation:** The employee confirms the transaction, which instantly updates the user's remaining coupon balance in the database.
<img src="https://github.com/MarkMdvn/qr-discount-wp/blob/main/public/github-readme-images/1-panel-empleados.png" alt="Panel Overview"  width="400"/>


### 4. Multi-Tiered Reporting Dashboards

<img src="https://github.com/MarkMdvn/qr-discount-wp/blob/main/public/github-readme-images/3-transacciones-con-total.png" alt="Panel Overview" />


To ensure complete transparency and data accessibility, the plugin features three distinct dashboards:

* **User Dashboard:**
    * Displays the current remaining balance on their discount coupon.
    * Provides a complete transaction history, showing where and when they used their discount.

* **Company Dashboard:**
    * A comprehensive log of all transactions processed at their establishment.
    * Detailed records include: the specific employee who processed the transaction, the original purchase amount, the discounted amount, the precise date and time.
    * Features a popup modal to display the information of the customer who used the coupon for each transaction.

* **Super Administrator Dashboard:**
    * A global overview of the entire campaign's performance.
    * Centralized view of all transactions from all participating companies.
    * Advanced filtering capabilities to sort and analyze data by date range, specific company, and other metrics.ç
<img src="https://github.com/MarkMdvn/qr-discount-wp/blob/main/public/github-readme-images/2-transacciones-realizadas.png" alt="Panel Overview" width="900"/>


## Technical Implementation

This plugin was built using a standard WordPress development stack, emphasizing security, scalability, and maintainability.

* **Backend:** PHP, Object-Oriented Programming (OOP)
* **Frontend:** JavaScript (for dynamic interactions), HTML5, CSS3
* **Database:** Custom database tables within the WordPress DB schema to store transaction and coupon data efficiently.
* **Core WordPress APIs:** Leveraged hooks and filters from the WordPress Core, including User Roles API, Shortcode API, and database functions (`$wpdb`).
* **Integrations:** The system was designed to work in conjunction with two other plugins, hooking into their functionalities to create a seamless user experience.

## Installation & Usage

1.  Download the plugin files from this repository.
2.  Upload the `epoint-custom-qr` folder to the `/wp-content/plugins/` directory.
3.  Activate the plugin through the 'Plugins' menu in WordPress.
4.  Use the following shortcodes to display the respective panels on your WordPress pages:
    * `[company_dashboard]` - For the affiliated company panel.
    * `[user_coupon_panel]` - For the normal user's discount display.
    * `[superadmin_dashboard]` - For the administrator's global view.

## License

This project is licensed under the MIT License - see the `LICENSE.md` file for details.
