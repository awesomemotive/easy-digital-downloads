=== Easy Digital Downloads ===
Author URI: http://pippinsplugins.com
Plugin URI: http://easydigitaldownloads.com
Contributors: mordauk
Donate link: http://pippinsplugins.com/support-the-site
Tags: download, downloads, e-store, eshop, digital downloads, e-downloads, ecommerce, e commerce, e-commerce, selling, wp-ecommerce, wp ecommerce, mordauk, Pippin Williamson, pippinsplugins
Requires at least: 3.2
Tested up to: 3.3.2
Stable Tag: 1.0.7

Sell digital downloads through WordPress with this complete digital downloads management plugin

== Description ==

Selling digital downloads is something that not a single one of the large WordPress ecommerce plugins has ever gotten really right. This plugin aims to fix that. Instead of focusing on providing every single feature under the sun, Easy Digital Downloads trys to provide only the ones that you really need. It aims to make selling digital downloads through WordPress easy, and complete.

**Follow this plugin on [Git Hub](https://github.com/pippinsplugins/Easy-Digital-Downloads)**

Features of the plugin include:

* Cart system for purchasing multiple downloads at once
* Complete promotional code system
* Many payment gateways. PayPal and Manual are included by default with Stripe, PayPal Pro, PayPal Express, and others available as add-ons
* Complete payment history
* User purchase history and ability to redownload files
* Multiple files per downloadable product
* Variable prices for multiple price optiosn per product
* Customizable purchase receipts
* Earnings and sales charts
* Detailed purchase and file download logs
* Extensible with many [add-ons](http://easydigitaldownloads.com/extensions/)
* Developer friendly with dozens of actions and filters

More information at [Easy Digital Downloads.com](http://easydigitaldownloads.com/).

[youtube http://www.youtube.com/watch?v=SjOeSZ08_IA]

**Languages**

Easy Digital Downloads as been translated into the following languages:

1. English
2. German
3. Spanish
4. French
5. Italian
6. Dutch
7. European Portuguese

Would you like to help translate the plugin into more langauges? [Contact Pippin](http://easydigitaldownloads.com/contact-developer/).

== Installation ==

1. Activate the plugin
2. Go to Downloads > Settings and configure the options
3. Create Downloadable products from the Downloads page
4. Insert purchase buttons for any download via the "Insert Download" button next the Upload Media buttons
5. For detailed setup instructions, vist the official [Documentation](http://easydigitaldownloads.com/documentation/) page.

== Frequently Asked Questions ==

= How do I Show My Shopping Cart? =

There are three ways you can show the downloads shopping cart:

1. Use the short code and simply place ]download_cart] on a page or within a text widget.

2. Use the included widget. Go to Appearance > Widgets and place the "Downloads Cart" widget into any widget area available.

3. Use the template tag and place the following the template file of your choosing:

`echo edd_shopping_cart();`

= Getting a 404 error? =

To get rid of the 404 error when viewing a download, you need to resave your permalink structure. Go to Settings > Permalinks and click "Save Changes".

== Screenshots ==

1. Screenshot 1
2. Screenshot 2
3. Screenshot 3
4. Screenshot 4
5. Screenshot 5
6. Screenshot 6
7. Screenshot 7
8. Screenshot 8
9. Screenshot 9


== Changelog ==

= 1.0.7 =

* Added new edd_has_variable_prices() function
* Improved the edd_price() function to take into account products with variable prices
* Added an $id parameter to the edd_cart_item filter
* Updated French language files
* Added missing "required" classes to the checkout login form
* Added the ability to update the email address associated with payments
* Added a new [edd_login] short code for showing a basic login form
* Added new Dutch language translation files


= 1.0.6 =

* NOTE: if you have modified the checkout_cart.php template via your theme, please consider updating it with the new version as many things have changed.
* Fixed a bug with the empty cart message not being displayed on the checkout page
* When purchasing a product with variable prices, the selected price option name is now shown on the checkout page
* Fixed a bug with the in-checkout registration /login form
* Improved the layout of the in-checkout register / login forms
* Fixed a bug in the "Edit Payment" page caused by the variable price system
* Fixed a bug with plugin pages being duplicate on reactivation of EDD
* Variable price descriptions can now contain HTMl
* Added new a new filter that allows for the jQuery validation rules to be modified for the checkout page
* Payments in the Payment History page can now be sorted by ID, Status, and Date.
* Fix a bug that allowed for the same download to be added to the cart twice.
* Added missing element classes to the cart widget, checkout cart, and more
* Added the edd_price() function for use in themes
* Updated the edd_payment_meta filter with a second parameter for $payment_data
* Updated the "Insert Download" icon in the "Insert Media" section to match the main post type icon
* Added filters that allow for post type and taxonomy labels to be modified via the theme
* Added filters that allow for the post type "supports" attributes to be modified
* Added extra mimetypes to the function that processes file downloads
* Dramatically improved the CSS of the checkout page.

= 1.0.5 =

* New variable pricing option for downloads
* Added new {price} template tag for emails
* Fixed an improperly named filter for "edd_payment_meta"
* Updated some advanced query URLs to be more efficient
* Updated the German language files
* Updated default.po/mo
* Added a check for whether the current theme supports post thumbnails
* Fixed a few undefined index errors
* Updated Spanish language files
* Added support for free downloads
* Fixed some bugs with the email formatting
* Fixed a small bug with the ajax add to cart system
* Improved the download metabox layout
* Updated the French language files
* Added a new icon to the Downloads post type


= 1.0.4.1 =

* New download post type icon
* Fixed missing add-ons.php file

= 1.0.4 =

* Added a new "Add Ons" page for viewing all available add-ons for the plugin
* Added two new filters for currencies that allow developers to add their own currencies
* Improved meta box field loading that allows add-ons to add / remove fields
* Added language files for Spanish
* Improvements to the "empty cart" message. It can now be customized via a filter

= 1.0.3 =

* Added first and last name fields to the checkout registration form.
* Improved country list formatting. 
* Improved the price input field to make it more clear and help prevent improper price formats.
* Added backwards compatibility for WP versions < 3.3. The rich editors in the settings pages could not be rendered in < 3.3.
* Added option to include an "Agree to terms" to the checkout.
* Added an option for the checkout cart template to be customized via the theme.
* Fixed a potential bug with file downloads.
* Added .epub files to accepted mime types.
* Fixed a bug with a missing email field when using add-on gateways.

= 1.0.2 =

* Added an option to delete payments
* Added featured thumbnails to checkout cart
* Moved payment action links to beneath the payment email to better match WordPress core
* Improved checkout CSS to help prevent conflicts
* "Already purchased" message now shows option to checkout when purchasing again.
* Forced file downloads and hidden file URLs
* Fixed a bug with duplicate purchase receipts
* Updated language files
* Fixed a bug with the discount code system

= 1.0.1.4 = 

* Fixed a bug with the "Add New" button for download source files.
* Added the Italian language files, thanks to Marco.

= 1.0.1.3 =

* Fixed a bug with the checkout login / register forms

= 1.0.1.2 =

* Fixed a bug with the manual payment gateway. 
* Fixed a bug where sales / earnings counts were increased before a purchase was confirmed. 
* Fixed a bug with the checkout registration / login forms.
* Added a German translation, thanks to David Decker.
* Added a partial European Portuguese translation, thanks to Takssista.

= 1.0.1.1 = 

* Minor updates including inclusion of INR as an available currency.
* Updates to the default.po file for missing strings.

= 1.0 =

* First offical release!

== Upgrade Notice ==

= 1.0.7 =

* Added new edd_has_variable_prices() function
* Improved the edd_price() function to take into account products with variable prices
* Added an $id parameter to the edd_cart_item filter
* Updated French language files
* Added missing "required" classes to the checkout login form
* Added the ability to update the email address associated with payments
* Added a new [edd_login] short code for showing a basic login form
* Added new Dutch language translation files

= 1.0.6 =

* NOTE: if you have modified the checkout_cart.php template via your theme, please consider updating it with the new version as many things have changed.
* Fixed a bug with the empty cart message not being displayed on the checkout page
* When purchasing a product with variable prices, the selected price option name is now shown on the checkout page
* Fixed a bug with the in-checkout registration /login form
* Improved the layout of the in-checkout register / login forms
* Fixed a bug in the "Edit Payment" page caused by the variable price system
* Fixed a bug with plugin pages being duplicate on reactivation of EDD
* Variable price descriptions can now contain HTMl
* Added new a new filter that allows for the jQuery validation rules to be modified for the checkout page
* Payments in the Payment History page can now be sorted by ID, Status, and Date.
* Fix a bug that allowed for the same download to be added to the cart twice.
* Added missing element classes to the cart widget, checkout cart, and more
* Added the edd_price() function for use in themes
* Updated the edd_payment_meta filter with a second parameter for $payment_data
* Updated the "Insert Download" icon in the "Insert Media" section to match the main post type icon
* Added filters that allow for post type and taxonomy labels to be modified via the theme
* Added filters that allow for the post type "supports" attributes to be modified
* Added extra mimetypes to the function that processes file downloads
* Dramatically improved the CSS of the checkout page.

= 1.0.5 =

* New variable pricing option for downloads
* Added new {price} template tag for emails
* Fixed an improperly named filter for "edd_payment_meta"
* Updated some advanced query URLs to be more efficient
* Updated the German language files
* Updated default.po/mo
* Added a check for whether the current theme supports post thumbnails
* Fixed a few undefined index errors
* Updated Spanish language files
* Added support for free downloads
* Fixed some bugs with the email formatting
* Fixed a small bug with the ajax add to cart system
* Improved the download metabox layout
* Updated the French language files
* Added a new icon to the Downloads post type

= 1.0.4.1 =

* New download post type icon
* Fixed missing add-ons.php file

= 1.0.4 =

* Added a new "Add Ons" page for viewing all available add-ons for the plugin
* Added two new filters for currencies that allow developers to add their own currencies
* Improved meta box field loading that allows add-ons to add / remove fields
* Added language files for Spanish
* Improvements to the "empty cart" message. It can now be customized via a filter

= 1.0.3 =

* Added first and last name fields to the checkout registration form.
* Improved country list formatting. 
* Improved the price input field to make it more clear and help prevent improper price formats.
* Added backwards compatibility for WP versions < 3.3. The rich editors in the settings pages could not be rendered in < 3.3.
* Added option to include an "Agree to terms" to the checkout.
* Added an option for the checkout cart template to be customized via the theme.
* Fixed a potential bug with file downloads.
* Added .epub files to accepted mime types.
* Fixed a bug with a missing email field when using add-on gateways.

= 1.0.2 =

* Added an option to delete payments
* Added featured thumbnails to checkout cart
* Moved payment action links to beneath the payment email to better match WordPress core
* Improved checkout CSS to help prevent conflicts
* "Already purchased" message now shows option to checkout when purchasing again.
* Forced file downloads and hidden file URLs
* Fixed a bug with duplicate purchase receipts
* Updated language files
* Fixed a bug with the discount code system

= 1.0.1.4 = 

* Fixed a bug with the "Add New" button for download source files.
* Added the Italian language files, thanks to Marco.

= 1.0.1.3 =

* Fixed a bug with the checkout login / register forms

= 1.0.1.2 =

Fixed a bug with the manual payment gateway. 
Fixed a bug where sales / earnings counts were increased before a purchase was confirmed. 
Fixed a bug with the checkout registration / login forms.
Added a German translation, thanks to David Decker.
Added a partial European Portuguese translation, thanks to Takssista.

= 1.0.1.1 = 

* Minor updates including inclusion of INR as an available currency.
* Updates to the default.po file for missing strings.

= 1.0 =

* First offical release!