=== Order Delivery Date for WooCommerce Day of Week Discount ===
Contributors: David Leigh
Author URI: https://david.leighweb.com/
Plugin URI: https://github.com/dleigh/order-delivery-date-for-woocommerce-dow-discount
Tags: delivery date, order delivery date, woocommerce delivery date, delivery, order delivery, day of week, discount
Requires PHP: 5.6
WC requires at least: 3.0.0
WC tested up to: 4.9.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin extends the Order Delivery Date Lite/Pro for WooCommerce plugin to 
enable the store owner to give discounts by the day of the 
week of the date selected using the Order Delivery Date plugin

== Description ==

This plugin extends the Order Delivery Date Lite/Pro for WooCommerce plugin 
(https://wordpress.org/plugins/order-delivery-date-for-woocommerce/) 
to enable the store owner to give discounts by the day of the week of the date selected 
using the Order Delivery Date plugin. As such it depends on the Order Delivery Date Lite 
plugin (free) or the Order Delivery Date Pro plugin (paid). 

### This plugin allows you to do the following

* For a given day of the week, within a date range or exclusive of date range, specify a flat amount of discount on shipping
	* Specify specific text, with an amount placeholder (@amt@), that is placed next to the shipping amount. (e.g. "($10 off)") 
* For a given day of the week, within a date range or exclusive of date range, specify a discount percentage on shipping
	* Specify specific text, with an amount placeholder (@amt@), that is placed next to the shipping amount. (e.g. "(30% discount)") 
* When the discount makes the shipping free, you can add text of your choice to indicate that. (e.g. "(Free Shipping)")
* 1.1.0 You can specify a list of product ids and then whether to "include" or "exclude" those ids from the rest of the shipping 
    discount rules
* 1.1.0 You can specify a list of catagories and then whether to "include" or "exclude" those catagories from the rest of the 
    shipping discount rules

== Installation ==


== Frequently Asked Questions ==

= ? =

********

= ? =

********

== Changelog ==

= 1.3.7 (2021-04-13) =
* This release simply added the compatibility with the parent Pro plugin version 9.24.0

= 1.3.6 (2021-02-16) =
* This release simply added the compatibility with the parent Pro plugin version 9.23.0

= 1.3.5 (2021-02-08) =
* Make the plugin work with "Github Updater"

= 1.3.4 (2021-02-08) =
* This release simply added the compatibility with the parent Pro plugin version 9.22.0

= 1.3.3 (2020-10-22) =
* This release simply added the compatibility with the parent Pro plugin version 9.20.1

= 1.3.2 (2020-08-28) =
* This release simply added the compatibility with the parent Pro plugin version 9.19.1

= 1.3.1 (2020-03-09) =
* This release simply added the compatibility with the parent Pro plugin version 9.14

= 1.3.0 (2019-12-30) =
* Added an option that allows or disallows the use of coupons in the same order with a day of week delivery discount.
* Fixed a problem where the enabling of the discount delivery was not being checked.
* A few minor code cleanups.

= 1.2.3 (2019-12-26) =
* Added the ability for this plugin to depend on the Pro version of the parent plugin as well as the free version.

= 1.2.2 (2019-11-15) =
* Fixed a bug in the version compatibility code message display that messed up the WordPress admin

= 1.2.1 (2019-05-14) =
* Refined the iteration logic to work more faithfully in different installations
* Added the ability for product/category inclusion/exclusion to work on products that have variations as well

= 1.2.0 (2019-05-13) =
* The parent plugin has evolved and changed the way that the date was stored.  They decided to store it locally on the client machine.  So This
  update was a major reworking of how that date gets to this plugin.  Ajax was added to accomplish this as well as transients to store data
  between our ajax call and the call to Woo's ajax to update the cart or checkout pages.  Additionally, much logic was added to account for the
  strange way that Woo behaves when invoking its shipping filter hook.
* The settings on the admin side were visually tidy-ed up a bit
* Some dead code was removed

= 1.1.0 (2019-03-18) =
* Added the ability to include/exclude specific posts and specific categories

= 1.0.0 (2019-03-07) =
* Initial release.

