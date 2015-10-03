=== Plugin Name ===
Contributors: baaaaas
Donate link: 
Tags: woocommerce pdf invoices, invoice, generate, pdf, woocommerce, attachment, email, completed order, customer invoice, processing order, attach, automatic, vat, rate, sequential, number
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 2.3.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.

== Description ==
*Invoicing can be time consuming. Well, not anymore! WooCommerce PDF Invoices automates the invoicing process by generating and sending it to your customers.*

This WooCommerce plugin generates PDF invoices, attaches it to the WooCommerce email type of your choice and sends invoices to your customers and Dropbox, Google Drive, OneDrive or Egnyte. The clean and customizable template will definitely suit your needs.

= Main features =
- Automatic PDF invoice generation and attachment
- Manually create or delete PDF invoice
- Attach PDF invoice to WooCommerce email type of your choice
- Connect with Google Drive, Egnyte, Dropbox or OneDrive
- Clean PDF Invoice template with with many customization options
- WooCommerce order numbering or built-in sequential invoice numbering
- Many invoice and date format customization options
- Advanced items table with refunds, discounts, different item tax rates columns and more
- Resend PDF invoices to customer
- Download invoice from customer account
- Mark invoices as paid

> **Invoicing on Steriods**<br /><br />
> We're working on a more powerful version of this invoicing plugin. Be the first to know when we're ready to launch and receive a unique one-time discount!<br /><br />
> [Read more about WooCommerce PDF Invoices Premium.](http://wcpdfinvoices.com)

= Support =

Support can take place on the [forum page](https://wordpress.org/support/plugin/woocommerce-pdf-invoices), where we will try to respond as soon as possible.

= Contributing =

If you want to add code to the source code, report an issue or request an enhancement, feel free to use [GitHub](https://github.com/baselbers/woocommerce-pdf-invoices).

= Translating =

Contribute a translation on [GitHub](https://github.com/baselbers/woocommerce-pdf-invoices#translating).

== Screenshots ==

1. General settings
2. Template settings
3. View or Cancel invoice from the order page.
4. Create new invoice from the order page.
5. View invoice from the shop order page.
6. Download invoice from account.
6. Nice and clean template with refunds, different tax rates, the ability to change the color and more!

== Installation ==

= Automatic installation =
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce PDF Invoices" and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

= Manual installation =
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

= How to add your custom template? =
To easily get started, copy the default template files (including folder) called `plugins/woocommerce-pdf-invoices/includes/templates/invoices/simple/micro` to `uploads/bewpi-templates/invoices/simple` and rename the template folder `micro` to a template name you like. This way the plugin will detect the template and makes it available to select it within the template settings tab. Now go ahead en start making some changes to the template files! :)

= How to add a fee to the invoice? =
To add a fee to your invoice, simply add the following action to your themes `functions.php`.

`add_action( 'woocommerce_cart_calculate_fees','add_woocommerce_fee' );
function add_woocommerce_fee() {
    global $woocommerce;

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $amount = 5;
    $woocommerce->cart->add_fee( 'FEE_NAME', $amount, true, 'standard' );
}`

= How to hide order item meta? =
To hide order item meta from the invoice, simply add the following filter to your themes `functions.php`.

`add_filter( 'woocommerce_hidden_order_itemmeta', 'add_hidden_order_items' );
function add_hidden_order_items( $order_items ) {
    $order_items[] = '_subscription_interval';
    $order_items[] = '_subscription_length';
    // end so on...

    return $order_items;
}`

== Changelog ==

= 2.3.6 - Oktober 3, 2015 =

- Fixed: Errors while activating plugin due to missing custom template dirs

= 2.3.5 - September 27, 2015 =

- Added: POT file
- Added: Option to display subtotal including or excluding shipping
- Added: Settings sidebars with information
- Added: Many hooks for interacting with your own code
- Fixed: File upload size to 2MB
- Fixed: Admin notifications not always showing

= 2.3.4 - September 16, 2015 =

- Fixed: Subtotal not displaying including tax
- Fixed: Plugin activation and deactivation hooks
- Fixed: Logo not always showing
- Improved: Settings markup
- Improved: Admin notices

= 2.3.3 - August 13, 2015 =

- Improved: Check if allow_url_fopen is enabled for image conversion to base64
- Improved: Norwegian language file thanks to Anders Sørensen :)

= 2.3.2 - August 12, 2015 =

- Added: Font to display rupee currency
- Fixed: Check if order has been paid
- Improved: Payment status showing as watermark

= 2.3.1 - August 8, 2015 =

- Fixed: Blank page after view invoice

= 2.3.0 - August 7, 2015 =

- Added: Payment status paid or unpaid on invoice
- Added: Ability to add custom templates
- Fixed: Deleted line item total displaying line item total including refunds
- Fixed: Header total displaying total excluding refunds
- Improved: Code by refactoring classes and architecture

= 2.2.10 - July 3, 2015 =

- Added: Filter for mpdf options
- Fixed: Email it in not receiving email

= 2.2.9 - June 22, 2015 =

- Added: Client billing phone number
- Added: Option to display including tax
- Added: Discount not showing while 0.00
- Added: Formatted invoice number to download button
- Fixed: Tax showing correct label

= 2.2.8 - May 15, 2015 =

- Fixed: BEWPI_TEMPLATES_DIR not defined

= 2.2.7 - May 15, 2015 =

- Added: Filter to change path to textdomain
- Added: Fees on invoice
- Added: Option to add month to invoice number format
- Fixed: Image not always showing on invoice

= 2.2.6 - May 14, 2015 =

- Fixed: Sequential invoice numbering

= 2.2.5 - May 13, 2015 =

- Fixed: Invoice not generated with order

= 2.2.4 - May 11, 2015 =

- Fixed: Admin notice
- Fixed: VAT translation
- Improved: Invoice header repeating on every page
- Improved: Template into separate files

= 2.2.3 - April 28, 2015 =

- Added: Customer notes added via order details page
- Fixed: Invoice not translated
- Fixed: Date not translated
- Updated: Language files

= 2.2.2 - April 25, 2015 =

- Added: Admin notices
- Improved: Translations

= 2.2.1 - April 25, 2015 =

- Added: Support for multiple languages like Chinese, Greek, Latin etc.
- Fixed: Invoice translation
- Fixed: Language files translatable
- Fixed: wc_tax_enabled function support due to WooCommerce 2.2 and lower
- Improved: French language files

= 2.2.0 - April 24, 2015 =

- Added: Download invoice button on My account page
- Added: Norwegian language files
- Added: Settings sections into settings pages
- Added: Checkbox to reset invoice number counter
- Added: Refunds on invoice template
- Added: Item tax and different total taxes on invoice template
- Fixed: Updating plugin removed all invoices -- Invoices into uploads dir
- Fixed: Order number not formatted
- Fixed: Invoice not viewable and removable in IE on Order details page
- Improved: Completely refactored code
- Improved: Dutch language file

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