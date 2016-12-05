=== Plugin Name ===
Contributors: baaaaas
Donate link: 
Tags: woocommerce pdf invoices, invoice, generate, pdf, woocommerce, attachment, email, completed order, customer invoice, processing order, attach, automatic, vat, rate, sequential, number
Requires at least: 4.0
Tested up to: 4.6
Stable tag: 2.4.12
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

> **WooCommerce PDF Invoices Premium**<br /><br />
> This plugin offers a premium version wich comes with the following features:<br /><br />
> - Periodically bill by generating and sending global invoices.<br />
> - Add additional PDF's to customer invoices.<br />
> - Send customer invoices directly to suppliers and others.<br />
> - Compatible with [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions) plugin emails.<br /><br />
> [Upgrade to WooCommerce PDF Invoices Premium >>](http://wcpdfinvoices.com)

#### Support

Support can take place on the [forum page](https://wordpress.org/support/plugin/woocommerce-pdf-invoices), where we will try to respond as soon as possible.

#### Contributing

If you want to add code to the source code, report an issue or request an enhancement, feel free to use [GitHub](https://github.com/baselbers/woocommerce-pdf-invoices).

#### Translating

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

#### Automatic installation
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce PDF Invoices" and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

#### Manual installation
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

#### How to add your custom template?
To getting started, copy the default template files (including folder) called `plugins/woocommerce-pdf-invoices/includes/templates/invoices/simple/micro` to `uploads/bewpi-templates/invoices/simple` and rename the template folder `micro`. The plugin will now detect the template and makes it available for selection within the template settings tab. Now go ahead en start making some changes to the template files! :)

#### How to add a fee to the invoice?
To add a fee to WooCommerce and your invoice, simply add the following action to your themes `functions.php`.

`
function add_woocommerce_fee() {
    global $woocommerce;

    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $amount = 5;
    $woocommerce->cart->add_fee( 'FEE_NAME', $amount, true, 'standard' );
}
add_action( 'woocommerce_cart_calculate_fees','add_woocommerce_fee' );
`

#### How to hide order item meta?
To hide order item meta from the invoice, simply add the following filter to your themes `functions.php`.

`
function add_hidden_order_items( $order_items ) {
    $order_items[] = '_subscription_interval';
    $order_items[] = '_subscription_length';
    // end so on...

    return $order_items;
}
add_filter( 'woocommerce_hidden_order_itemmeta', 'add_hidden_order_items' );
`

#### How to change the common PDF options?
To change the more common options of the PDF, use below example.

`
function custom_bewpi_mpdf_options( $options ) {
 	$options['mode'] = '';
 	$options['format'] = ''; // use [format]-L or [format]-P to force orientation (A4-L will be size A4 with landscape orientation)
 	$options['default_font_size'] = 0;
 	$options['default_font'] = 'opensans';
 	$options['margin_left'] = 14;
 	$options['margin_right'] = 14;
 	$options['margin_top'] = 14;
 	$options['margin_bottom'] = 0;
 	$options['margin_header'] = 14;
 	$options['margin_footer'] = 6;
 	$options['orientation'] = 'P'; // Also try to force with format option

 	return $options;
}
add_filter( 'bewpi_mpdf_options', 'custom_bewpi_mpdf_options' );
`

#### How to change the more advanced PDF options?
To fully customize the PDF, use below code. This filter gives you full control over the mPDF library. Check the mPDF [manual](https://www.dropbox.com/s/h44f7v5anvcmmvl/mpdfmanual.pdf?dl=0) for more info.

`
function bewpi_mpdf( $mpdf ) {
    // change the direction of the invoice to RTL
    $mpdf->SetDirectionality( 'rtl' );

    return $mpdf;
}
add_filter( 'bewpi_mpdf', 'bewpi_mpdf' );
`

#### How to display invoice download button on specific template files?
Add below code for example to your "thankyou" page or "customer-completed-order" email template.

`
echo do_shortcode( '[bewpi-download-invoice title="Download (PDF) Invoice {formatted_invoice_number}" order_id="' . $order->id . '"]' );
`

For use in WordPress editor use below shortcode. This will only work if you replace "{ORDER_ID}" with an actual order id.

`
[bewpi-download-invoice title="Download (PDF) Invoice {formatted_invoice_number}" order_id="{ORDER_ID}"]
`

Note: Download button will only be displayed when PDF exists and order has been paid.

#### Logo image shows a red cross?
By default the relative path is used for better performance, try to base64 the image. Also read the sticky topic on the support forum for more solutions!

`
function convert_company_logo_to_base64( $company_logo_path ) {
    $company_logo_url = str_replace( '..', get_site_url(), $company_logo_path );
    $type = pathinfo( $company_logo_url, PATHINFO_EXTENSION );
    $data = wp_remote_fopen( $company_logo_url );
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode( $data );
    return $base64;
}
add_filter( 'bewpi_company_logo_url', 'convert_company_logo_to_base64' );
`

#### How to remove 'Paid' watermark based on specific order statuses?
By default the 'Paid' watermark won't display for 'Pending', 'On-Hold' and 'Auto-Draft' statuses.

`
function bewpi_paid_watermark_excluded_order_statuses($order_statuses, $order_id){
    // add (short) name of order status to exclude
    return array('pending', 'on-hold', 'auto-draft');
}
add_filter('bewpi_paid_watermark_excluded_order_statuses', 'bewpi_paid_watermark_excluded_order_statuses', 10, 2);
`

#### How to remove 'Paid' watermark based on specific payment methods?
By default 'BACS', 'Cash on Delivery' and 'Cheque' payment methods are excluded, so the invoice won't get marked as paid.

`
function exclude_payment_method_for_watermark($payment_methods, $order_id){
    // add (short) name of payment method to exclude
    return array('bacs', 'cod', 'cheque', 'paypal');
}
add_filter('bewpi_paid_watermark_excluded_payment_methods', 'exclude_payment_method_for_watermark', 10, 2);
`

#### How to skip invoice generation based on specific payment methods?
Add the name of the payment method to the array.

`
function bewpi_attach_invoice_excluded_payment_methods($payment_methods) {
    return array('bacs', 'cod', 'cheque', 'paypal');
}
add_filter('bewpi_attach_invoice_excluded_payment_methods', 'bewpi_attach_invoice_excluded_payment_methods', 10, 2);
`

#### How to allow specific roles to download invoice?
Add the name of the role to the array. By default shop managers and administrators are allowed to download invoices.

`
function bewpi_allowed_roles_to_download_invoice($allowed_roles) {
    // available roles: shop_manager, customer, contributor, author, editor, administrator
    $allowed_roles[] = "editor";

    return $allowed_roles;
}
add_filter('bewpi_allowed_roles_to_download_invoice', 'bewpi_allowed_roles_to_download_invoice', 10, 2);
`

### How to alter formatted invoice number? ###
Add following filter function to your functions.php within your theme.

`
function alter_formatted_invoice_number( $formatted_invoice_number, $invoice_type ) {
   if ( $invoice_type === 'global' ) {
      // add M for global invoices
      return 'M' . $formatted_invoice_number;
   }
Filter to alter formatted invoice number.
   return $formatted_invoice_number;
}
add_filter('bewpi_formatted_invoice_number', 'alter_formatted_invoice_number', 10, 2);
`

== Changelog ==

= 2.4.13 - December 5, 2016 =

- Fixed: Fixed company logo "IMAGE Error: Image not found" and other mPDF image errors due to wrong (local)host server configurations (mainly on shared hosting) by using mPDF' "Image as a Variable" method.
- Fixed: Warning: call_user_func_array() expects parameter 1 to be a valid callback, class 'BE_WooCommerce_PDF_Invoices' does not have a method 'display_rate_admin_notice'.
- Fixed: Support for Hindi, Kuwaiti and more by adding "Free Serif" font.

= 2.4.12 - November 23, 2016 =

- Added: Estonian language files.
- Added: Option to be able to display text in black color. This fixes the invisible text problem when theme color is white or some other light color.
- Added zero VAT when user inserts a valid VAT Number with "WooCommerce EU VAT Number" plugin, because some EU countries demand it. Also added notice at the end of the PDF stating "Zero rated for VAT as customer has supplied EU VAT number".
- Improved: Don't pass objects by reference (this is default since PHP5).
- Improved: Moved some HTML out of translation strings.
- Improved: Removed some inline `IF` statements to adhere to the WordPress Code Standard.
- Improved: Escape attributes with user submitted values.
- Improved: Update PHPDocs for methods.
- Improved: Use `admin_footer_text` and 'update_footer' filters instead of `window.onload` to show/modify text.
- Improved: Use `add_query_arg` and `remove_query_arg` for building URL's.
- Improved: Create `DateTime` from explicit form we're saving it in.
- Improved: Remove unnecessary loop by passing arrays to `str_replace`.
- Improved: Namespace JS object into `BEWPI` object to prevent clashes with other global `Settings` objects.
- Improved: Use `wp_list_pluck` to build array of defaults.
- Improved: Updated German, French and Slovenian language files.
- Improved: Table by only showing column headers on first page and totals on last. The totals won't be cut off between pages anymore.
- Improved: Getting total amount by simply using `$order->get_total();` method and not manually calculating with refund.
- Fixed "WooCommerce Cost of Goods" plugin only hiding cost itemmeta in admin.

= 2.4.11 - November 14, 2016 =

- Added: Filter to alter formatted invoice number.
- Removed: Unnecessary language files and CSS.

= 2.4.10 - September 23, 2016 =

- Added: "On-hold" email to attach invoice
- Fixed: Several small bugs due to new version of mPDF

= 2.4.9 - September 21, 2016 =

- Fixed: Updated mPDF

= 2.4.8 - September 19, 2016 =

- Fixed: mPDF PHP7 errors (blank pages)

= 2.4.7 - May 2, 2016 =

- Fixed: Invoice not attached to email

= 2.4.6 - April 29, 2016 =

- Added: Option to change the title of the invoice
- Fixed: Invoice not attached to email
- Fixed: Shortcode error when no order_id is given

= 2.4.5 - April 15, 2016 =

- Added: Filter 'bewpi_allowed_roles_to_download_invoice' (check FAQ)
- Added: Watermark mPDF options
- Added: Italian language files
- Added: Actions 'bewpi_before_invoice_content' and 'bewpi_after_invoice_content' for WPML integration (WIP)
- Added: Filter 'bewpi_attach_invoice_excluded_payment_methods' to attach invoice depending on payment methods
- Improved: Norwegian language file

= 2.4.4 - March 11, 2016 =

- Added: Filter for email attachments
- Fixed: Invoice action buttons on order page not showing due to conflict with other invoicing plugin
- Fixed: Characters showing square like Rupee symbol

= 2.4.3 - March 06, 2016 =

- Removed: Borders on template due to testing layout.

= 2.4.2 - March 06, 2016 =

- Added: '[shipping_method]' placeholder and filter to add more placeholders.
- Added: Filters to FAQ page in order to fix the company logo showing red cross. (Read sticky topic on support forum first)
- Fixed: Paid watermark not showing
- Fixed: Sequential invoice number reset
- Fixed: 'SyntaxError: Unexpected token C' error
- Improved: Language files
- Removed: Unused global invoice template and dir

= 2.4.1 - February 10, 2016 =

- Added: Lithuanian language files
- Added: German language files
- Improved: Settings sidebar
- Fixed: Don't display paid watermark when payment method is Cash on Delivery
- Fixed: mPDF already included
- Fixed: Margin between header and address sections
- Fixed: Copy .htaccess and index.php files to many times into uploads folder

= 2.4.0 - January 15, 2016 =

- Added: Purchase Order Number from WooCommerce Purchase Order Gateway
- Added: VAT Number from WooCommerce EU VAT Number
- Added: Russian language files
- Added: Option to enable mPDF debugging
- Improved: Dutch language files
- Improved: Romain language files
- Fixed: Company logo image only showing red placeholder - Increased performance by using relative path to image
- Fixed: Color picker CSS conflict

= 2.3.20 - December 30, 2015 =

- Improved: Changed textdomain to plugin slug due to preparation of WordPress translations packages

= 2.3.19 - December 30, 2015 =

- Fixed: Translations not properly configured by removing Domain Path.

= 2.3.18 - December 30, 2015 =

- Fixed: Syriac, Arabic, Indic, Hebrew (and more) fonts integration.
- Improved: Number of zero digits for invoice number up to 20.

= 2.3.17 - December 25, 2015 =

- Added: Romanian language files
- Fixed: Shop managers access to view invoices.
- Fixed: Rating notice showing while activating plugin

= 2.3.16 - December 19, 2015 =

- Fixed: Permission for customers and admins to view invoices.

= 2.3.15 - December 18, 2015 =

- Added: Shortcode for downloading invoices
- Added: Option to enable/disable download button on account page
- Fixed: Invoice number always 1 due to no wp table prefix in query
- Fixed: Date localization and timestamps

= 2.3.14 - December 11, 2015 =

- Fixed: Fatal errors due to Wordpress 4.4
- Improved: Replaced textdomain variable by strongly typed string (properly prepared for translations)

= 2.3.13 - November 28, 2015 =

- Improved: Changed file_get_contents to wp_get_remote
- Fixed: Logo not always showing
- Fixed: Footer column (typo in code)

= 2.3.12 - November 28, 2015 =

- Improved: Micro and global (premium) template
- Improved: Code in order to disable allow_url_fopen
- Fixed: Header and footer repeating with too much content/text

= 2.3.11 - November 6, 2015 =

- Added: Do not attach option to email options
- Added: Swedish language files
- Improved: Address text not displayed if empty
- Improved: Billing phone text not displayed if empty
- Fixed: Invoice numbering gaps while cancelling invoice

= 2.3.10 - October 29, 2015 =

- Added: German language files.

= 2.3.9 - October 20, 2015 =

- Fixed: Admin notices not showing.

= 2.3.8 - October 9, 2015 =

- Fixed: Losing settings.

= 2.3.7 - October 6, 2015 =

- Added: Arabic font Amiri

= 2.3.6 - October 3, 2015 =

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