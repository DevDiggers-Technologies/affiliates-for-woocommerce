=== Affiliates for WooCommerce - Affiliate Program, Referral Tracking & Commission Payouts ===
Contributors: DevDiggers
Plugin URI: https://devdiggers.com/product/woocommerce-affiliates/
Author: DevDiggers
Author URI: https://devdiggers.com/
Tags: affiliate, affiliates, affiliate program, affiliate marketing, referral program
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
WC requires at least: 9.0.0
WC tested up to: 11.0.1
Stable tag: 2.1.3
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Run your own affiliate marketing program in WooCommerce. Track referral links, calculate commissions, and pay affiliates from your own store.

== Description ==

Affiliates for WooCommerce is a [WooCommerce affiliate plugin](https://devdiggers.com/product/woocommerce-affiliates/) that turns your store into a self hosted affiliate program. People sign up as affiliates, share a referral link, and earn a commission when someone buys through it. You approve the affiliates, set the rate, and pay them when you are ready.

Everything runs inside WordPress. There is no external affiliate network taking a cut of each sale, and no monthly platform fee on top of what you already pay your affiliates.

Affiliates get their own dashboard inside the WooCommerce My Account page, so they can check earnings, build referral links, and enter payout details without emailing you.

= Quick links =

* [Free demo](https://demo.devdiggers.com/woocommerce-affiliates-free/)
* [Pro demo](https://demo.devdiggers.com/woocommerce-affiliates/)
* [Documentation](https://docs.devdiggers.com/woocommerce-affiliates/)
* [Free vs Pro comparison](https://devdiggers.com/product/woocommerce-affiliates/#free-vs-pro)

= How the referral tracking works =

1. An affiliate registers through your signup form and you approve them.
2. They generate a referral link for any page or product on your store.
3. A visitor clicks the link and a referral cookie is stored in their browser.
4. If that visitor orders before the cookie expires, the commission is recorded against the affiliate.
5. You review the commission list and record the payout.

= Why run the affiliate program on your own store =

Hosted affiliate platforms charge a subscription and usually a percentage of every tracked sale, so you pay twice for the same order. This plugin keeps the affiliate data, the commission rules, and the payout records in your own database.

Commission amounts are calculated from real WooCommerce order totals, so what an affiliate earns matches what the customer actually paid. You decide whether tax and discounts count toward the commission base.

== What the free version includes ==

= Affiliate registration and signup form =

Affiliates apply through a form you can place on any page.

* An affiliate dashboard page with the login and registration forms is created for you on activation
* `[ddwcaf_affiliate_registration_form_shortcode]` renders the affiliate registration form anywhere on your site
* `[ddwcaf_affiliate_dashboard_shortcode]` renders the affiliate dashboard on a page of your choice
* Rename either shortcode from Configuration > Shortcodes if you prefer your own tag
* Show the login form next to the signup form, or show the signup form on its own
* Add the affiliate signup fields to the standard WooCommerce registration page
* Choose which WordPress user roles are allowed to become affiliates
* Approve, reject, or ban applications from the affiliates list in your admin
* Add a new affiliate yourself from the admin using the Add New button on the affiliates screen

= Referral link tracking =

Every click on a referral link is matched to an affiliate through a browser cookie.

* Set the referral cookie lifetime in days
* Rename the referral URL parameter and the cookie name to suit your store
* Turn click logging on or off
* Every logged click stores the destination URL and the time it happened
* Affiliates build their own referral links from the link generator in their dashboard

= Commission calculation =

Commissions are recorded automatically from WooCommerce orders once an order reaches the status you configure.

* One global commission percentage applies to every affiliate
* Exclude tax from the commission base with the net of tax setting
* Exclude the discounted amount so coupons do not inflate what you pay
* Review every commission in the admin with the order reference, amount, affiliate, and status
* Filter the commission list by status, date, or affiliate

= Payout management =

* Bank transfer and PayPal are both available as payout methods, and you can switch either one off
* Affiliates save their own bank or PayPal details from their dashboard, so you do not have to collect them by email
* Create payout records from the admin and mark them paid once the money is sent
* Filter payouts by status so you can work through them in batches
* Each payout record keeps the affiliate, amount, method, and date

= Affiliate dashboard =

Affiliates see their own numbers without any access to your WordPress admin.

* Appears as an Affiliates tab in the WooCommerce My Account page, or on a standalone page through the shortcode
* Total earnings, paid earnings, unpaid earnings, and conversions at the top of the dashboard
* Commission list split by status so affiliates know what is pending and what has been paid
* Payout history with the current status of each request
* Visit log with the destination URL and timestamp of each recorded click
* Top products list showing which of your products earned them the most
* Referral link generator for any page or product
* Payout details form where they choose bank transfer or PayPal
* Optional WordPress sidebar widgets on the dashboard page

= Admin reporting =

* Affiliates list with a status control and an earnings summary for each affiliate
* Single affiliate screen with the full profile and that affiliate's commission history
* Commission log across all affiliates with filters
* Visit log across all affiliates showing referral source URLs
* Top products report showing which products drive the most affiliate revenue

= Dashboard styling =

* Set the brand primary colour used across the affiliate dashboard
* Control background, border, text, and value colours for the summary cards
* Set table header background and text colours
* Show or hide the summary card icons and change the icon size

= Built for WooCommerce stores =

* Declared compatible with WooCommerce High Performance Order Storage (HPOS)
* Translation ready with a `.pot` file, so it works with WPML, Polylang, and Loco Translate
* Works with standard WooCommerce compatible themes
* Object oriented codebase with hooks and filters for custom development
* Admin screens adapt to smaller displays with a drawer based navigation

[Try the free demo](https://demo.devdiggers.com/woocommerce-affiliates-free/)

== What the Pro version adds ==

The free version covers a single global commission rate and manual payouts. Pro is for stores that need per product rates, automated payouts, and marketing material for affiliates.

= Commission rules =

* Commission rates per product, per category, or per user role
* Tiered rates that increase automatically as an affiliate reaches higher lifetime earnings
* A personal commission rate for an individual affiliate that overrides the global rate
* Exclude specific products or categories from earning commission
* A holding period so commissions only become payable after your refund window closes

= Coupon based referrals =

* Assign WooCommerce coupons to specific affiliates
* Affiliates earn commission when a customer checks out with their coupon, with no referral link needed
* Coupon assignment emails sent to the affiliate
* A coupons tab in the affiliate dashboard

= Automated payouts =

* Let affiliates request payouts themselves, or run payouts automatically on a chosen day each month
* Set a minimum balance before a payout can be requested or processed
* A step by step payout wizard for processing large batches
* Pay through bank transfer, PayPal, or [WooCommerce Wallet](https://devdiggers.com/product/woocommerce-wallet-management/)

= Marketing tools for affiliates =

* Upload banners and text links as creatives that affiliates copy from their dashboard
* One click sharing to 10+ networks including Facebook, WhatsApp, LinkedIn, X, and email
* Pinterest ready image uploads
* Default share titles and messages so your branding stays consistent

= Custom registration fields =

* Add your own fields to the affiliate signup form using text, select, radio, checkbox, and textarea inputs
* Choose which profile fields affiliates are allowed to edit
* Require affiliates to accept your program terms during signup

= Fraud controls =

* Block affiliates from earning commission on their own orders
* Set a debounce time so repeated clicks from the same visitor are not counted as separate visits
* Decide whether a newer referral link overwrites an existing affiliate cookie
* Clear the referral cookie after a purchase
* CAPTCHA on the signup form through the [WooCommerce Advanced CAPTCHA](https://devdiggers.com/product/woocommerce-advanced-captcha/) plugin by DevDiggers, sold separately

= Email notifications =

* Automated emails for registration, approval, and commission updates

= Advanced reports =

* Charts for earnings, visits, and conversions with month on month comparison
* Conversion rate and earnings breakdown at a glance
* Top products report filtered by affiliate

= Dashboard endpoints =

* Custom URL endpoints and titles for each affiliate dashboard section
* Custom endpoint slug and title for the My Account affiliate tab

[Try the Pro demo](https://demo.devdiggers.com/woocommerce-affiliates/) or [see the full comparison](https://devdiggers.com/product/woocommerce-affiliates/#free-vs-pro)

== Installation ==

**From your WordPress admin**

1. Go to Plugins > Add New.
2. Search for Affiliates for WooCommerce by DevDiggers.
3. Click Install Now, then Activate.

**Manual upload**

1. Download the zip from WordPress.org.
2. Go to Plugins > Add New > Upload Plugin.
3. Upload the zip, click Install Now, then Activate.

You can also unzip the file and upload the folder to `/wp-content/plugins/` over FTP, then activate it from the Plugins screen.

**First run**

1. Open DevDiggers Plugins > Affiliates > Configuration > General and turn on the affiliate system.
2. Set your commission percentage under Configuration > Commissions.
3. Set the referral cookie lifetime under Configuration > Referrals.
4. Share the affiliate dashboard page URL and start approving signups.

WooCommerce must be installed and active.

== Frequently Asked Questions ==

= How does affiliate referral tracking work? =

A referral link carries the affiliate's ID in a URL parameter. When a visitor opens it, the plugin stores a cookie in their browser. If that visitor places an order before the cookie expires, the commission is recorded against that affiliate. You set the cookie lifetime in days under Configuration > Referrals.

= How many affiliates can I have? =

There is no limit in the free version. The admin lists are filterable, so you can still find a specific affiliate once the program grows.

= How do I pay my affiliates? =

The free version uses manual payouts. Affiliates save their bank transfer or PayPal details in their dashboard, you create a payout record from the admin, send the money through your own bank or PayPal account, and mark the record as paid. The plugin does not move money on its own. Pro adds affiliate requested payouts, scheduled monthly payouts, minimum payout thresholds, and WooCommerce Wallet.

= Can I set a different commission rate per product or per affiliate? =

Not in the free version, which uses one global percentage for everyone. Per product, per category, per user role, per affiliate, and tiered rates are Pro features.

= Can affiliates earn commission from coupon codes instead of links? =

Coupon based referral tracking is a Pro feature. In the free version, commission is tracked through referral links and the referral cookie.

= Can I stop affiliates from earning commission on their own orders? =

Self referral blocking is a Pro feature. The free version records the commission whenever the referral cookie is present at checkout.

= Does this work with WooCommerce HPOS? =

Yes. The plugin declares compatibility with WooCommerce High Performance Order Storage and has been tested with HPOS turned on.

= Do affiliates need access to my WordPress admin? =

No. The affiliate dashboard sits inside the WooCommerce My Account page, or on any page where you place the dashboard shortcode. Affiliates see only their own earnings, commissions, payouts, visits, top products, and link generator.

= Which shortcodes are included? =

`[ddwcaf_affiliate_registration_form_shortcode]` outputs the affiliate registration form, with the login form next to it if you want. `[ddwcaf_affiliate_dashboard_shortcode]` outputs the affiliate dashboard. Both tags can be renamed under Configuration > Shortcodes.

= Do commissions include tax and discounts? =

That is your choice. Configuration > Commissions has a net of tax setting and a setting that excludes the discounted amount, so a coupon does not increase what you owe the affiliate.

= Can I control who is allowed to become an affiliate? =

Yes. Under Configuration > General you pick which WordPress user roles can register as affiliates, and every application still needs your approval before it becomes active.

= Can I change the look of the affiliate dashboard? =

Yes. Configuration > Layout sets the brand colour, card background, border, text, value, and table header colours, plus the icon size on the summary cards.

= Do I need to write any code? =

No. Everything is set through labelled admin fields, and the pages are built with shortcodes. Developers who want to go further will find hooks and filters through the codebase.

= Where do I get support? =

Post in the support forum here on WordPress.org for the free version. The full documentation is at [docs.devdiggers.com/woocommerce-affiliates](https://docs.devdiggers.com/woocommerce-affiliates/). Pro licence holders get direct support from DevDiggers.

== External services ==

This plugin connects to two services run by DevDiggers. Both are used in the WordPress admin only and neither is involved in referral tracking, commissions, or payouts.

**DevDiggers plugin catalogue**

The DevDiggers admin screen loads the list of DevDiggers plugins so it can show which of them you already have installed. The request is sent to `https://devdiggers.com/wp-json/ddwcs/v1/plugins` when that screen is opened. It sends no personal data and no store data, only a standard user agent identifying the plugin framework version.

**DevDiggers newsletter signup**

If you type an email address into the newsletter box on the DevDiggers admin screen and submit it, that email address, a "newsletter" tag, and your site URL are sent to `https://devdiggers.com/`. This only happens when you submit the form yourself.

Terms of service: [https://devdiggers.com/terms-and-conditions/](https://devdiggers.com/terms-and-conditions/)
Privacy policy: [https://devdiggers.com/privacy-policy/](https://devdiggers.com/privacy-policy/)

== Source code and build tools ==

The JavaScript and CSS shipped in `assets/` are compiled with webpack and Babel from the sources in `src/`, which is excluded from the distributed zip to keep it small. The full source, including `src/`, `webpack.config.js`, `babel.config.js`, and `package.json`, is available from the plugin page at [devdiggers.com](https://devdiggers.com/product/woocommerce-affiliates/).

To build from source:

1. `npm install`
2. `npm run build`

== Screenshots ==

1. Admin dashboard with total earnings, paid and unpaid earnings, quick stats, and charts
2. Affiliate dashboard with total earnings, paid earnings, unpaid earnings, and quick stats
3. Affiliate commission list with amounts, order references, dates, and statuses
4. Affiliate payout history with the current status of each request
5. Affiliate visit log with referral source URLs and timestamps
6. Affiliate top products list showing which products earned the most commission
7. Referral link generator where affiliates build links for any page or product
8. Affiliate registration and login page built with the registration shortcode
9. Admin affiliates list with status controls and an earnings summary per affiliate
10. Single affiliate screen with the full profile and commission history
11. Admin commission list with filters for status, date, and affiliate
12. Admin payout list with manual payout creation and status controls
13. Admin top products report across all affiliates
14. Admin visit log with the affiliate, URL, and timestamp of every referral click
15. Registration fields configuration screen

== Changelog ==

= 2.1.3 =
* Fixed a fatal error that could occur when another DevDiggers plugin was active at the same time. Both plugins can now load together without conflict.
* Fixed the Documentation link on the Plugins screen, which pointed at a page that no longer existed.
* Tested with WordPress 7.1 and WooCommerce 11.0.1.
* Rewrote the plugin documentation so the free and Pro feature lists match what each version actually does.

= 2.1.2 =
* Fixed a fatal error on the plugin dashboard.

= 2.1.1 =
* Updated the framework for better speed and stability.

= 2.1.0 =
* Added a setup wizard for first time configuration.
* Updated the framework for better speed and stability.
* General performance improvements and code cleanup.

= 2.0.4 =
* Fixed the licence issue introduced in the previous release.

= 2.0.3 =
* Fixed a compatibility issue with some themes.

= 2.0.2 =
* Fixed a conflict with other DevDiggers plugins.

= 2.0.1 =
* Fixed a compatibility issue with Affiliates for WooCommerce Pro below v2.0.0.
* Added compatibility with the latest WordPress and WooCommerce releases.

= 2.0.0 =
* Added DevDiggers Framework integration for a single admin experience across DevDiggers plugins.
* Redesigned the affiliate dashboard with a responsive layout.
* Rebuilt the admin dashboard for smaller screens.
* Added drawer based mobile navigation for admin settings.
* Refactored the backend to an object oriented structure.
* Added a new SVG icon set for the affiliate dashboard.

= 1.1.1 =
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

= 1.1.0 =
* Added WooCommerce HPOS compatibility.
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

= 1.0.1 =
* Added compatibility with the latest WordPress and WooCommerce releases.
* Fixed security issues.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.1.3 =
Fixes a fatal error when another DevDiggers plugin is active alongside this one. Adds WordPress 7.1 and WooCommerce 11.0.1 compatibility. Recommended for all users.
