# Affiliates for WooCommerce

**Contributors**: `DevDiggers`

**Tags**: affiliate, affiliates, affiliate program, affiliate marketing, referral program

**WordPress**
  * `Requires at least: 6.5`
  * `Tested up to: 7.1`

**WooCommerce**
  * `Requires at least: 9.0.0`
  * `Tested up to: 11.0.1`

**Requires PHP**: `7.4`
**Stable tag**: `2.1.3`
**License**: `GPL v3.0`

## Description

[Affiliates for WooCommerce](https://devdiggers.com/product/woocommerce-affiliates/) turns your store into a self hosted affiliate program. People sign up as affiliates, share a referral link, and earn a commission when someone buys through it. You approve the affiliates, set the rate, and pay them when you are ready.

Everything runs inside WordPress. No external affiliate network takes a cut of each sale, and there is no monthly platform fee on top of what you already pay your affiliates.

Affiliates get their own dashboard inside the WooCommerce My Account page, so they can check earnings, build referral links, and enter payout details without emailing you.

### How referral tracking works

1. An affiliate registers through your signup form and you approve them.
2. They generate a referral link for any page or product on your store.
3. A visitor clicks the link and a referral cookie is stored in their browser.
4. If that visitor orders before the cookie expires, the commission is recorded against the affiliate.
5. You review the commission list and record the payout.

## Free version

### Registration and signup

* Affiliate dashboard page with login and registration forms created on activation
* `[ddwcaf_affiliate_registration_form_shortcode]` renders the registration form anywhere
* `[ddwcaf_affiliate_dashboard_shortcode]` renders the affiliate dashboard on any page
* Both shortcode tags can be renamed under Configuration > Shortcodes
* Show the login form beside the signup form, or the signup form alone
* Add affiliate signup fields to the standard WooCommerce registration page
* Restrict which WordPress user roles may become affiliates
* Approve, reject, or ban applications from the admin affiliates list
* Add a new affiliate from the admin with the Add New button

### Referral tracking

* Configurable referral cookie lifetime in days
* Renameable referral URL parameter and cookie name
* Click logging toggle, with destination URL and timestamp on every logged click
* Referral link generator in the affiliate dashboard

### Commissions

* One global commission percentage for all affiliates
* Net of tax setting to exclude tax from the commission base
* Setting to exclude the discounted amount so coupons do not inflate payouts
* Admin commission log with order reference, amount, affiliate, and status
* Filter commissions by status, date, or affiliate

### Payouts

* Bank transfer and PayPal payout methods, each independently switchable
* Affiliates save their own bank or PayPal details from their dashboard
* Create payout records in the admin and mark them paid once sent
* Filter payouts by status for batch processing

### Affiliate dashboard

* Appears as an Affiliates tab in WooCommerce My Account, or standalone via shortcode
* Total earnings, paid earnings, unpaid earnings, and conversions
* Commission list split by status
* Payout history with current status
* Visit log with destination URL and timestamp
* Top products list
* Referral link generator
* Payout details form
* Optional WordPress sidebar widgets

### Admin reporting

* Affiliates list with status control and per affiliate earnings summary
* Single affiliate screen with full profile and commission history
* Commission log across all affiliates with filters
* Visit log with referral source URLs
* Top products report

### Dashboard styling

* Brand primary colour for the affiliate dashboard
* Background, border, text, and value colours for summary cards
* Table header background and text colours
* Show or hide summary card icons and set icon size

### Technical

* Declared compatible with WooCommerce High Performance Order Storage (HPOS)
* Translation ready, `.pot` file included (WPML, Polylang, Loco Translate)
* Object oriented codebase with hooks and filters
* Admin screens adapt to small displays with drawer based navigation

[Free demo](https://demo.devdiggers.com/woocommerce-affiliates-free/)

## Pro version

The free version covers a single global commission rate and manual payouts. Pro adds per product rates, automated payouts, and marketing material for affiliates.

* Commission rates per product, per category, or per user role
* Tiered rates that rise automatically as an affiliate reaches higher lifetime earnings
* Per affiliate commission rate overriding the global rate
* Exclude specific products or categories from earning commission
* Holding period so commissions become payable only after the refund window closes
* Coupon based referrals: assign WooCommerce coupons to affiliates, with assignment emails and a dashboard coupons tab
* Affiliate requested payouts and scheduled monthly automatic payouts
* Minimum payout balance threshold
* Step by step payout wizard for large batches
* [WooCommerce Wallet](https://devdiggers.com/product/woocommerce-wallet-management/) as a payout method
* Creatives: banners and text links affiliates copy from their dashboard
* One click sharing to 10+ networks, Pinterest ready uploads, default share titles and messages
* Custom registration fields (text, select, radio, checkbox, textarea)
* Control which profile fields affiliates may edit, and require terms acceptance at signup
* Self referral blocking
* Visit debounce time, and control over whether a newer referral link overwrites an existing cookie
* Clear the referral cookie after purchase
* CAPTCHA on the signup form via DevDiggers Advanced CAPTCHA
* Automated emails for registration, approval, and commission updates
* Charts for earnings, visits, and conversions with month on month comparison
* Top products report filtered by affiliate
* Custom URL endpoints and titles for each dashboard section and the My Account tab

[Pro demo](https://demo.devdiggers.com/woocommerce-affiliates/) | [Free vs Pro comparison](https://devdiggers.com/product/woocommerce-affiliates/#free-vs-pro/)

## Installation

1. Upload the `affiliates-for-woocommerce` folder to `/wp-content/plugins/`.
2. Activate the plugin from the Plugins menu.
3. Open DevDiggers Plugins > Affiliates > Configuration > General and turn on the affiliate system.
4. Set your commission percentage under Configuration > Commissions.
5. Set the referral cookie lifetime under Configuration > Referrals.

WooCommerce must be installed and active.

## Build from source

The JavaScript and CSS in `assets/` are compiled with webpack and Babel from `src/`, which is excluded from the distributed zip.

```bash
npm install
npm run build
```

## Support

[Documentation](https://docs.devdiggers.com/woocommerce-affiliates/) | [Support forum](https://wordpress.org/support/plugin/affiliates-for-woocommerce/) | [Contact DevDiggers](https://devdiggers.com/contact/)

## Changelog

**= 2.1.3 =**
* Fixed a fatal error that could occur when another DevDiggers plugin was active at the same time.
* Fixed the Documentation link on the Plugins screen, which pointed at a page that no longer existed.
* Tested with WordPress 7.1 and WooCommerce 11.0.1.
* Rewrote the plugin documentation so the free and Pro feature lists match what each version actually does.

**= 2.1.2 =**
* Fixed a fatal error on the plugin dashboard.

**= 2.1.1 =**
* Updated the framework for better speed and stability.

**= 2.1.0 =**
* Added a setup wizard for first time configuration.
* Updated the framework for better speed and stability.
* General performance improvements and code cleanup.

**= 2.0.4 =**
* Fixed the licence issue introduced in the previous release.

**= 2.0.3 =**
* Fixed a compatibility issue with some themes.

**= 2.0.2 =**
* Fixed a conflict with other DevDiggers plugins.

**= 2.0.1 =**
* Fixed a compatibility issue with Affiliates for WooCommerce Pro below v2.0.0.
* Added compatibility with the latest WordPress and WooCommerce releases.

**= 2.0.0 =**
* Added DevDiggers Framework integration for a single admin experience across DevDiggers plugins.
* Redesigned the affiliate dashboard with a responsive layout.
* Rebuilt the admin dashboard for smaller screens.
* Added drawer based mobile navigation for admin settings.
* Refactored the backend to an object oriented structure.
* Added a new SVG icon set for the affiliate dashboard.

**= 1.1.1 =**
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

**= 1.1.0 =**
* Added WooCommerce HPOS compatibility.
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

**= 1.0.1 =**
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

**= 1.0.0 =**
* Initial release.
