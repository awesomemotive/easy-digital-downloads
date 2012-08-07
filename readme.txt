=== Easy Digital Downloads ===
Author URI: http://pippinsplugins.com
Plugin URI: http://easydigitaldownloads.com
Contributors: mordauk, sksmatt
Donate link: http://pippinsplugins.com/support-the-site
Tags: download, downloads, e-store, eshop, digital downloads, e-downloads, ecommerce, e commerce, e-commerce, selling, wp-ecommerce, wp ecommerce, mordauk, Pippin Williamson, pippinsplugins
Requires at least: 3.2
Tested up to: 3.4.1
Stable Tag: 1.1.4.1


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
* Variable prices for multiple price options per product
* Customizable purchase receipts
* Earnings and sales charts
* Detailed purchase and file download logs
* Extensible with many [add-ons](http://easydigitaldownloads.com/extensions/)
* Developer friendly with dozens of actions and filters

More information at [Easy Digital Downloads.com](http://easydigitaldownloads.com/).

[youtube http://www.youtube.com/watch?v=SjOeSZ08_IA]

**Add an Affiliate System to Your Store**

Easy Digital Downloads has an [integration pack for the awesome Affiliates Pro plugin](http://easydigitaldownloads.com/extension/affiliates-pro-integration-pack/), which gives you everything you need to build a complete affiliate system and dramatically boost your traffic and sales.

**Build Up Your Email Subscribers**

With add-ons for [Mail Chimp](http://easydigitaldownloads.com/extension/mail-chimp/), [Campaign Monitor](http://easydigitaldownloads.com/extension/campaign-monitor/), and [AWeber](http://easydigitaldownloads.com/extension/aweber/), Easy Digital Downloads can easily grow your email subscription lists while making you money at the same time.

**Languages**

Easy Digital Downloads as been translated into the following languages:

1. English
2. German
3. Spanish
4. French
5. Italian
6. Dutch
7. European Portuguese
8. Turkish

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

1. Use the short code and simply place [download_cart] on a page or within a text widget.

2. Use the included widget. Go to Appearance > Widgets and place the "Downloads Cart" widget into any widget area available.

3. Use the template tag and place the following the template file of your choosing:

`echo edd_shopping_cart();`

= Getting a 404 error? =

To get rid of the 404 error when viewing a download, you need to resave your permalink structure. Go to Settings > Permalinks and click "Save Changes".

= How do I Show the User's Purchase History? =

Place the [purchase_history] short code on any page.

If you want to just show a list of the files the user has purchased, use the [download_history] short code instead.

= Can I Setup an Affiliate System? =

Yes! EDD has an add-on that provides a complete affiliate system that you can use to award commissions to your affiliate marketers.

[Checkout Affiliates Pro + EDD Integration Pack](http://easydigitaldownloads.com/extension/affiliates-pro-integration-pack/)

= Can Users Purchase Products without Using PayPal? =

Yes, through the addition of one or more of the add-on payment gateways, you can accept payments in many different ways. The add-on gateways currently available:

* [Stripe](http://easydigitaldownloads.com/extension/stripe-payment-gateway/)
* [Recurly](http://easydigitaldownloads.com/extension/recurly-com-checkout/)
* [Authorize.net](http://easydigitaldownloads.com/extension/authorize-net-gateway/)
* [Moneybookers / Skrill](http://easydigitaldownloads.com/extension/moneybookers-skrill-payment-gateway/)
* [2Checkout](http://easydigitaldownloads.com/extension/2checkout-gateway/)
* [PayPal Pro / Express](http://easydigitaldownloads.com/extension/paypal-pro-express/)
* [Mijireh Checkout](http://easydigitaldownloads.com/extension/mijireh-checkout/)
* [MercadoPago](http://easydigitaldownloads.com/extension/mercadopago/)
* More coming soon

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

= 1.1.4.1 =

* Fixed a bug with the source file download processing
* Added support for .mobi files
* Removed deprecated get_magic_quotes_runtime()
* Fixed an error notice warning
* Added "order" and "orderby" parameters to the [downloads] short code
* Fixed some errors with aposthrophe encoding
* Removed a conditional check for the jQuery library as it was causing problems with jQuery not loading
* Add a "No Download Found" message to the PDF reports for when there are no products
* Fixed a rendering issue with purchase receipts in Outlook

= 1.1.4.0 =

* Fixed a bug with the purchase receipt templates
* Updated default language files with a lot of new strings
* Added new edd_cart_contents filter
* Replaced Thickbox with Colorbox for email template previews
* Fixed a bug with + signs in email addresses
* Fixed a bug with prices not saving when set to 0
* Added a new PDF Report generation feature for Sales and Earnings, thanks to SunnyRatilal
* Added a new [edd_price] short code
* Fixed a miss spelled FOR attribute on a checkout label
* Added Quick Edit ability to the Download Price option
* Fixed a bug with the discount code field on the checkout page
* Fixed an encoded bug with the purchase receipts
* Updated the charset from ISO-8859-1 to utf-8 for the purchase receipts
* Fixed a bug with the way the currency sign was displayed in the meta box price field
* Fixed a bug where flat rate discounts could result in negative checkout values
* Update the date in purchase receipts to reflect the date_format setting in WordPress
* Added a check to existing jQuery libraries before enqueing
* Added is_array() check to the price options name function to fix a potential error
* Improved the formatting of country names
* Added an php_ini check for safe mode
* Fixed a missing currency sign in the email {price} template tag

= 1.1.3.2 =

* Fixed a minor bug with the PayPal IPN listener
* Fixed a minor bug with the function that checks for a valid discount
* Added two new action hooks to the reports page

= 1.1.3.1 =

* Fixed a bug that caused complete CC fields to show when only one gateway was enabled

= 1.1.3 =

* Fixed a bug with free downloads that happened when no payment gateway was selected
* Separated First and Last name fields in the payment's CSV export
* Fixed a bug that prevented large files from being able to be downloaded
* Improved the countries drop down field in the default CC form for payment gateways
* Fixed an error that showed up when purchasing a download without any downloadable files
* Added a new filter to the PayPal redirect arguments array
* Fixed a bug with the PayPal Standard gateway that was present when allow_url_fopen wasn't enabled
* Removed the edd_payment post type from the WP Nav Menus
* Added a check to the download processing function to ensure the purchase has been marked as complete
* Fixed a padding bug on the checkout form

= 1.1.2 =

* Fixed a bug with the ajax function that adds items to the cart - it did not show the price option name until page was refreshed
* Fixed a bug in the purchase receipt that caused it to include all source file links, not just the ones set to the price option purchase
* Added a new "class" parameter to the [purchas_link} short code
* Moved the discount code fieldset inside of the user info fieldset on the checkout form
* Added a legend to the user info fieldset 
* Improved the markup of the default CC fields
* Added new edd_is_checkout() conditional function
* Updated Spanish language files
* Added new payment export system, thanks to MadeByMike

= 1.1.1 =

* Added a couple of new filters to the file download processing function
* Fixed a couple of undefined index errors
* Fixed a bug with the "All" filter in the Payment History page
* Fixed an amount comparision error in the PayPal IPN processer
* Added Japanese language files

= 1.1.0 =

* Added new French translation files, thanks for Boddhi
* Updated default language files
* Fixed the width of the "Email" column in the payment history page
* Added payment "status" filters to the payment history page
* Added an option to filter the payment history page by user/buyer
* Added a "Price" column to the Downloads page
* Fixed a bug with duplicate "Settings Updated" notices
* Added a missing text domain to the Settings Updated notice
* Fixed a bug with the add-ons cache that caused them to never refresh
* Added new {receipt_id} template tag for purchase receipts
* Improved CSS for the checkout page
* Improved CSS for the payment method icons
* Added a new "upload" callback for settings field types
* Added a new hook, edd_process_verified_download, to the download processing function
* Minor improvements to the email templating system
* Minor improvements to the View Order Details pop up
* Updated edd_sert_payment() to apply the date of the payment to the post_date field

= 1.0.9 =

* Updated the purchase/download history short codes to only show files for the price options the user has purchased
* Fixed a bug with the file upload meta box fields
* Added the ability to register custom payment method icons
* Added unique IDs to P tags on the checkout form
* Added an option to disable the PayPal IPN verification
* Added a new feature that allows source files to be restricted to specific price options
* Updated the "View Purchase Details" modal to include the price option purchased, if any
* Added labels above the file name and file URL fields to help users using browsers without placeholder support
* Made improvements to the checkout registration form layout
* Added an option in Settings > Misc to define the expiration length for download links - default is 24 hours
* Updated the [purchase_link] short code in the Download Configuration meta box to reflect the chosen button color
* Updated the "Short Code" column in the list table to include the correct button color option
* Added a new filter, edd_download_file_url_args,  for changing the arguments passed to the function that generages download URLs
* Fixed a bug with the EDD_READ_FILE_MODE constant
* Added a new filter to allow developers to change the redirect URL for the edd_login form
* Improved some file / function organization

= 1.0.8.5 =

* Added {payment_method} to the list of email template tags for showing the method of payment used for a purchase
* Removed the menu_position attribute from the "download" post type to help prevent menu conflicts
* Fixed a bug with the page options in settings
* Updated the edd_read_file() function to convert local URLs to absolute file paths
* Fixed a bug with the [downloads] short code
* Enhanced the function for checking if a user has purchased a download to add support for checking for specific price options
* Fixed a bug with the function that checks if a user has purchased a specific download
* Fixed a potential bug with the "settings updated" notice that could have caused duplicate messages to be shown

= 1.0.8.4 =

* Fixed a bug with download sale/earning stats going negative when reversing purchases
* Removed some blank form action attributes that caused the HTML to invalidate
* Added "Settings Updated" notification when saving plugin settings
* Made some improvements to the default purchase receipt email template 
* Renamed the "Manual Payment" gateway to "Test"
* Added options for linking the download titles in the [downloads] short code
* Removed the "You have already purchased this" message from the purchase link short code / template
* Added a "price" parameter to the [downloads] short code
* Improved the CSS on the variable price option forms
* Add a parameter to the [downloads] short code for showing the complete content
* Fixed a bug with free downloads
* Moved the function that triggers the purchase receipt to its own function/hook so that it can be modified more easily
* Added a few new action hooks
* Updated Spanish language files

= 1.0.8.3 =

* Added a default purchase receipt email that is used if no custom email has been defined
* Fixed a bug with the discount codes and their usage counts not getting recorded correctly
* Fixed a bug with the install script
* Fixed a problem with apostrophe encoding in the purchase summary sent to PayPal
* Added pagination to the download/sale log on download Edit screens
* Added new "edd_default_downloads_name" filter for changing the default singular and plural "download" labels used globally throughout the plugin
* Adding new span.edd-cart-item-separator to the cart widget and short code
* Added more support for the [downloads] short code, used to display a list or grid of digital products
* Moved load_plugin_textdomain to an "init" hook in order to work better with translation plugins
* Fixed a couple of undefined index errors
* Added option to send purchase receipt when manually marked a payment as complete
* Added new "edd_success_page_redirect" filter to the function that redirects a buyer to the success page
* Changed the default charset in the PayPal standard gateway to that of the website
* Added "Payment Method" to the "View Order Details" popup
* Made ajax enabled by default
* Reorganized the edd_complete_purchase() function to be more extensible
* Added new constant EDD_READ_FILE_MODE for defining how download files are delivered
* Added auto creation for .htaccess files in the uploads directory for EDD to help protect unauthorized file downloads
* Added Turkish language files
* Added detection for php.ini variables important to PayPal payment verification
* Added a new short code for showing a list of active discounts: [download_discounts]

= 1.0.8.2 =

* Added a number_format() check to the PayPal standard gateway
* Added the Turkish Lira to supported currencies
* Dramatically improved the default PayPal gateway, which should help prevent payments not getting verified
* Added edd_get_ip() and updated the user IP detection. It previously failed if the server was running SSL
* Added missing class name to the download history table
* Fixed a misnamed class in the purchase history table
* Updated purchase and download history to now show download links if redownload is disabled
* Added a new conditional called edd_no_redownload() that theme devs can use to check if redownloading of files is permitted
* Fixed problem with improper encoding of apostrphes in purchase receipt emails
* Added new edd_hook_callback() function for settings field type callbacks
* Updated default language files with new strings for translation

= 1.0.8.1 =

* Updated es_ES translation files
* A lots of code documentation improvements
* Completely rewrote the purchase processing functions to fix a couple of bugs and make the entire thing easier to debug and improve
* Fixed a problem with user emails not being recorded for guest purchases
* Improved the performance of the add-ons page with transients
* Reorganized some functions into more appropriate files
* Fixed translation domains on the login forms
* Added a new option for marking a payment as "refunded". The refund process must be done through the payment gateway still. When payments are marked as "refunded", the sales and earnings stats will be adjusted accordingly.
* Added an alert message to the "Delete Payment" link
* Updated French language files
* Added get_post_class() to the payments history page so that payment rows can be styled based on their status, post type, etc.
* Updated admin CSS to add custom background color to refunded payments
* Added new filter called "edd_payment_statuses", which can be used to register custom statuses

= 1.0.8 =

* Added the [purchase_history] shortcode for showing a detailed list of user's purchases
* Improved the names of the widgets
* Fixed a CSS bug with the Add Ons page
* Added the edd_get_checkout_uri() function for use by themes
* Fixed a couple of bugs with the login/register checkout forms
* Dramatically improved code documentation
* Fixed an incorrectly named parameter in the edd_after_download_content hook

= 1.0.7.2 =

* Added a new EDD Categories / Tags widget
* Removed duplicated code from payments history page
* Fixed a major bug that made it impossible to safely update orders
* Added user's IP address to payment meta
* Added localization to the default page titles created during install
* Removed old stripe.js code that is no longer used
* Added an enhancement to the cart widget that causes the "Purchase" button to reset when removing an item from the cart

= 1.0.7.1 =

* Added a second instance do_action('edd_purchase_form_user_info') to the checkout registration form
* Updated the edd_purchase_link() function to automatically detect chosen link styles and colors
* Fixed a bug with the discount code form on checkout. It now only shows if there is at least one active discount. Props to Sksmatt
* Fixed a bug with the Media Uploader when adding media to the content of a Download. Props to Sksmatt
* Added a wrapper div.edd-cart-ajax-alert around the message that shows when an item has been added to the cart
* Fixed a small error notice present in the checkout form short code. Props to Sksmatt 
* Fixed a small bug wit the edd_remove_item_url() present when on a 404 error page. Props to Sksmatt

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

= 1.1.4.1 =

* Fixed a bug with the source file download processing
* Added support for .mobi files
* Removed deprecated get_magic_quotes_runtime()
* Fixed an error notice warning
* Added "order" and "orderby" parameters to the [downloads] short code
* Fixed some errors with aposthrophe encoding
* Removed a conditional check for the jQuery library as it was causing problems with jQuery not loading
* Add a "No Download Found" message to the PDF reports for when there are no products
* Fixed a rendering issue with purchase receipts in Outlook

= 1.1.4.0 =

* Fixed a bug with the purchase receipt templates
* Updated default language files with a lot of new strings
* Added new edd_cart_contents filter
* Replaced Thickbox with Colorbox for email template previews
* Fixed a bug with + signs in email addresses
* Fixed a bug with prices not saving when set to 0
* Added a new PDF Report generation feature for Sales and Earnings, thanks to SunnyRatilal
* Added a new [edd_price] short code
* Fixed a miss spelled FOR attribute on a checkout label
* Added Quick Edit ability to the Download Price option
* Fixed a bug with the discount code field on the checkout page
* Fixed an encoded bug with the purchase receipts
* Updated the charset from ISO-8859-1 to utf-8 for the purchase receipts
* Fixed a bug with the way the currency sign was displayed in the meta box price field
* Fixed a bug where flat rate discounts could result in negative checkout values
* Update the date in purchase receipts to reflect the date_format setting in WordPress
* Added a check to existing jQuery libraries before enqueing
* Added is_array() check to the price options name function to fix a potential error
* Improved the formatting of country names
* Added an php_ini check for safe mode
* Fixed a missing currency sign in the email {price} template tag

= 1.1.3.2 =

* Fixed a minor bug with the PayPal IPN listener
* Fixed a minor bug with the function that checks for a valid discount
* Added two new action hooks to the reports page

= 1.1.3.1 =

* Fixed a bug that caused complete CC fields to show when only one gateway was enabled

= 1.1.3 =

* Fixed a bug with free downloads that happened when no payment gateway was selected
* Separated First and Last name fields in the payment's CSV export
* Fixed a bug that prevented large files from being able to be downloaded
* Improved the countries drop down field in the default CC form for payment gateways
* Fixed an error that showed up when purchasing a download without any downloadable files
* Added a new filter to the PayPal redirect arguments array
* Fixed a bug with the PayPal Standard gateway that was present when allow_url_fopen wasn't enabled
* Removed the edd_payment post type from the WP Nav Menus
* Added a check to the download processing function to ensure the purchase has been marked as complete
* Fixed a padding bug on the checkout form

= 1.1.2 =

* Fixed a bug with the ajax function that adds items to the cart - it did not show the price option name until page was refreshed
* Fixed a bug in the purchase receipt that caused it to include all source file links, not just the ones set to the price option purchase
* Added a new "class" parameter to the [purchas_link} short code
* Moved the discount code fieldset inside of the user info fieldset on the checkout form
* Added a legend to the user info fieldset 
* Improved the markup of the default CC fields
* Added new edd_is_checkout() conditional function
* Updated Spanish language files
* Added new payment export system, thanks to MadeByMike

= 1.1.1 =

* Added a couple of new filters to the file download processing function
* Fixed a couple of undefined index errors
* Fixed a bug with the "All" filter in the Payment History page
* Fixed an amount comparision error in the PayPal IPN processer
* Added Japanese language files


= 1.1.0 =

* Updated French translation files, thanks for Boddhi
* Updated default language files
* Fixed the width of the "Email" column in the payment history page
* Added payment "status" filters to the payment history page
* Added an option to filter the payment history page by user/buyer
* Added a "Price" column to the Downloads page
* Fixed a bug with duplicate "Settings Updated" notices
* Added a missing text domain to the Settings Updated notice
* Fixed a bug with the add-ons cache that caused them to never refresh
* Added new {receipt_id} template tag for purchase receipts
* Improved CSS for the checkout page
* Improved CSS for the payment method icons
* Added a new "upload" callback for settings field types
* Added a new hook, edd_process_verified_download, to the download processing function
* Minor improvements to the email templating system
* Minor improvements to the View Order Details pop up
* Updated edd_sert_payment() to apply the date of the payment to the post_date field

= 1.0.9 =

* Updated the purchase/download history short codes to only show files for the price options the user has purchased
* Fixed a bug with the file upload meta box fields
* Added the ability to register custom payment method icons
* Added unique IDs to P tags on the checkout form
* Added an option to disable the PayPal IPN verification
* Added a new feature that allows source files to be restricted to specific price options
* Updated the "View Purchase Details" modal to include the price option purchased, if any
* Added labels above the file name and file URL fields to help users using browsers without placeholder support
* Made improvements to the checkout registration form layout
* Added an option in Settings > Misc to define the expiration length for download links - default is 24 hours
* Updated the [purchase_link] short code in the Download Configuration meta box to reflect the chosen button color
* Updated the "Short Code" column in the list table to include the correct button color option
* Added a new filter, edd_download_file_url_args,  for changing the arguments passed to the function that generages download URLs
* Fixed a bug with the EDD_READ_FILE_MODE constant
* Added a new filter to allow developers to change the redirect URL for the edd_login form
* Improved some file / function organization

= 1.0.8.5 =

* Added {payment_method} to the list of email template tags for showing the method of payment used for a purchase
* Removed the menu_position attribute from the "download" post type to help prevent menu conflicts
* Fixed a bug with the page options in settings
* Updated the edd_read_file() function to convert local URLs to absolute file paths
* Fixed a bug with the [downloads] short code
* Enhanced the function for checking if a user has purchased a download to add support for checking for specific price options
* Fixed a bug with the function that checks if a user has purchased a specific download
* Fixed a potential bug with the "settings updated" notice that could have caused duplicate messages to be shown

= 1.0.8.4 =

* Fixed a bug with download sale/earning stats going negative when reversing purchases
* Removed some blank form action attributes that caused the HTML to invalidate
* Added "Settings Updated" notification when saving plugin settings
* Made some improvements to the default purchase receipt email template 
* Renamed the "Manual Payment" gateway to "Test"
* Added options for linking the download titles in the [downloads] short code
* Removed the "You have already purchased this" message from the purchase link short code / template
* Added a "price" parameter to the [downloads] short code
* Improved the CSS on the variable price option forms
* Add a parameter to the [downloads] short code for showing the complete content
* Fixed a bug with free downloads
* Moved the function that triggers the purchase receipt to its own function/hook so that it can be modified more easily
* Added a few new action hooks
* Updated Spanish language files

= 1.0.8.3 =

* Added a default purchase receipt email that is used if no custom email has been defined
* Fixed a bug with the discount codes and their usage counts not getting recorded correctly
* Fixed a bug with the install script
* Fixed a problem with apostrophe encoding in the purchase summary sent to PayPal
* Added pagination to the download/sale log on download Edit screens
* Added new "edd_default_downloads_name" filter for changing the default singular and plural "download" labels used globally throughout the plugin
* Adding new span.edd-cart-item-separator to the cart widget and short code
* Added more support for the [downloads] short code, used to display a list or grid of digital products
* Moved load_plugin_textdomain to an "init" hook in order to work better with translation plugins
* Fixed a couple of undefined index errors
* Added option to send purchase receipt when manually marked a payment as complete
* Added new "edd_success_page_redirect" filter to the function that redirects a buyer to the success page
* Changed the default charset in the PayPal standard gateway to that of the website
* Added "Payment Method" to the "View Order Details" popup
* Made ajax enabled by default
* Reorganized the edd_complete_purchase() function to be more extensible
* Added new constant EDD_READ_FILE_MODE for defining how download files are delivered
* Added auto creation for .htaccess files in the uploads directory for EDD to help protect unauthorized file downloads
* Added Turkish language files
* Added detection for php.ini variables important to PayPal payment verification
* Added a new short code for showing a list of active discounts: [download_discounts]

= 1.0.8.2 =

* Added a number_format() check to the PayPal standard gateway
* Added the Turkish Lira to supported currencies
* Dramatically improved the default PayPal gateway, which should help prevent payments not getting verified
* Added edd_get_ip() and updated the user IP detection. It previously failed if the server was running SSL
* Added missing class name to the download history table
* Fixed a misnamed class in the purchase history table
* Updated purchase and download history to now show download links if redownload is disabled
* Added a new conditional called edd_no_redownload() that theme devs can use to check if redownloading of files is permitted
* Fixed problem with improper encoding of apostrphes in purchase receipt emails
* Added new edd_hook_callback() function for settings field type callbacks
* Updated default language files with new strings for translation

= 1.0.8.1 =

* Updated es_ES translation files
* A lots of code documentation improvements
* Completely rewrote the purchase processing functions to fix a couple of bugs and make the entire thing easier to debug and improve
* Fixed a problem with user emails not being recorded for guest purchases
* Improved the performance of the add-ons page with transients
* Reorganized some functions into more appropriate files
* Fixed translation domains on the login forms
* Added a new option for marking a payment as "refunded". The refund process must be done through the payment gateway still. When payments are marked as "refunded", the sales and earnings stats will be adjusted accordingly.
* Added an alert message to the "Delete Payment" link
* Updated French language files
* Added get_post_class() to the payments history page so that payment rows can be styled based on their status, post type, etc.
* Updated admin CSS to add custom background color to refunded payments
* Added new filter called "edd_payment_statuses", which can be used to register custom statuses

= 1.0.8 =

* Added the [purchase_history] shortcode for showing a detailed list of user's purchases
* Improved the names of the widgets
* Fixed a CSS bug with the Add Ons page
* Added the edd_get_checkout_uri() function for use by themes
* Fixed a couple of bugs with the login/register checkout forms
* Dramatically improved code documentation
* Fixed an incorrectly named parameter in the edd_after_download_content hook

= 1.0.7.2 =

* Added a new EDD Categories / Tags widget
* Removed duplicated code from payments history page
* Fixed a major bug that made it impossible to safely update orders
* Added user's IP address to payment meta
* Added localization to the default page titles created during install
* Removed old stripe.js code that is no longer used
* Added an enhancement to the cart widget that causes the "Purchase" button to reset when removing an item from the cart

= 1.0.7.1 =

* Added a second instance do_action('edd_purchase_form_user_info') to the checkout registration form
* Updated the edd_purchase_link() function to automatically detect chosen link styles and colors
* Fixed a bug with the discount code form on checkout. It now only shows if there is at least one active discount. Props to Sksmatt
* Fixed a bug with the Media Uploader when adding media to the content of a Download. Props to Sksmatt
* Added a wrapper div.edd-cart-ajax-alert around the message that shows when an item has been added to the cart
* Fixed a small error notice present in the checkout form short code. Props to Sksmatt 
* Fixed a small bug wit the edd_remove_item_url() present when on a 404 error page. Props to Sksmatt

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