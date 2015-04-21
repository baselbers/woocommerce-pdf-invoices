=== Plugin Name ===
Contributors: baaaaas
Donate link: 
Tags: woocommerce pdf invoices, invoice, generate, pdf, woocommerce, attachment, email, completed order, customer invoice, processing order, attach, automatic, vat, rate, sequential, number
Requires at least: 3.5
Tested up to: 4.1.1
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically or manually create and send PDF Invoices for WooCommerce orders and connect with Dropbox, Google Drive, OneDrive or Egnyte.

== Description ==
This WooCommerce plugin creates customizable PDF invoices for WooCommerce orders with the ability to setup an invoice number with a specific format. Attach the PDF invoice to the WooCommerce email type of your choice and try out sending invoices automatically to Dropbox, Google Drive, OneDrive or Egnyte. It's simply awesome!

= Main features =
- Automatic PDF invoice generation and attachment
- Attach PDF invoice to WooCommerce email type of your choice
- Attach PDF invoice to New Order WooCommerce email
- Connect with Google Drive, Egnyte, Dropbox or OneDrive
- Many PDF invoice template customization options
- Sequential invoice number system
- Customize invoice number format
- Manually (re)create or delete PDF invoice
- Resend PDF invoices to customer
- Without annoying advertisements

Install the plugin and try out all the features, it will simply be awesome.

= Support =

Support can take place on the [forum page](https://wordpress.org/support/plugin/woocommerce-pdf-invoices), where we will try to respond as soon as possible.

= Contributing =

If you want to add code to the source code, report an issue or request an enhancement, feel free to use [GitHub](https://github.com/baselbers/woocommerce-pdf-invoices).

= Translating =

Contribute a translation on [GitHub](https://github.com/baselbers/woocommerce-pdf-invoices#translating).

== Screenshots ==

1. General settings
2. Template settings
3. View or Cancel the invoice from the order page.
4. Create a new invoice from the order page.
5. View the invoice from the shop order page.
6. Nice and clean invoice template with the ability to change the color.

== Installation ==

= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce PDF Invoices" and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

= Manual installation =
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Changelog ==

= 2.2.0 - April 8, 2015 =

- Added: Download invoice button on My account page
- Added: Norwegian language files
- Added: Settings sections into settings pages
- Added: Checkbox to reset invoice number counter
- Fixed: Updating plugin removed all invoices -- Invoices into uploads dir
- Fixed: Order number format
- Improved: Code -- Refactored

= 2.1.0 - April 8, 2015 =

- Added: Variable products attributes on template
- Added: Shipping address on template
- Added: Order number and order date on template
- Added: Option to add the year to the invoice number
- Added: Option to change order date format
- Fixed: Header CSS on template
- Improved: Dutch language file

= 2.0.6 - April 3, 2015 =

- Fixed: Displays wrong unit price for variation products
- Fixed: Some currencies not getting displayed

= 2.0.5 - March 30, 2015 =

- Fixed: Invoice number type doens't get saved
- Improved: WPI_Invoice class code

= 2.0.4 - March 30, 2015 =

- Added: Option to use WC order number as invoice number
- Added: Slovenian language file
- Added: French language file
- Fixed: Translation invoice

= 2.0.3 - March 27, 2015 =

- Fixed: Suffix and company logo disappearing

= 2.0.2 - March 26, 2015 =

- Fixed: PHP 5.3+ compatibility

= 2.0.1 - March 26, 2015 =

- Fixed: Validation errors
- Fixed: Parse error '['

= 2.0.0 - March 23, 2015 =

- Added: Send invoice to your personal cloud storage with emailitin.com
- Added: Option to change the date format
- Added: Option to change the invoice number format
- Added: Prefix and suffix option for the invoice number
- Added: Option to determine the number of zero digits for the invoice number
- Added: Option to reset invoice number on first of january
- Added: Option to change the color of the template
- Improved: Template
- Improved: Sequential invoice numbers
- Improved: Input fields allows HTML tags for text markup
- Improved: Server-side validation on the options
- Fixed: Invoices saved into public upload folder

= 1.1.2 - March 10, 2015 =

- Fixed: Fatal error WC_ORDER::get_shipping()

= 1.1.1 - February 6, 2014 =

- Added: Choose starting point for invoice numbers
- Fixed: Invoice number stays at 0000
- Fixed: Translation

= 1.1.0 - February 3, 2014 =

- Added: Choose to display product SKU.
- Added: Choose to display notes.
- Added: Choose your desired invoice number format.
- Added: Attach invoice to admin "New Order" email type.
- Added: Input your desired VAT rates to display.
- Added: Sequential invoice numbers.
- Improved: Display and calculation of VAT rates.
- Fixed: Product SKU

= 1.0.2 - December 13, 2013 =

- Added: Attach pdf invoice to email type of your choice.
- Added: Translation ready.
- Added: Update and error notes to the settings page.
- Improved: Notes to the settings page.

= 1.0.1 - December 7, 2013 =

- Added: Notes to the settings page.
- Improved: Changed individual address fields to one textarea field.
- Improved: Automatic linebreaks in textarea fields.

= 1.0.0 - December 6, 2013 =

- Initial release.