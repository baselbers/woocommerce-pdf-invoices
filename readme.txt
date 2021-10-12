=== Plugin Name ===
Contributors: baaaaas
Donate link: 
Tags: Invoices for WooCommerce, invoice, packing slips, delivery note, packing list, shipping list, generate, pdf, woocommerce, attachment, email, customer invoice, processing, vat, tax, sequential, number, dropbox, google drive, onedrive, egnyte, cloud, storage
Requires at least: 4.0
Tested up to: 5.8
Stable tag: 3.1.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically generate and attach customizable PDF Invoices and PDF Packing Slips for WooCommerce emails and directly send to Dropbox, Google Drive, OneDrive or Egnyte.

== Description ==
*Invoicing can be time consuming. Well, not anymore! Invoices for WooCommerce automates the invoicing process by generating and sending it to your customers.*

This WooCommerce plugin generates PDF invoices and PDF packing slips, attaches it to WooCommerce email types of your choice and sends invoices to your customers' Dropbox, Google Drive, OneDrive or Egnyte. Choose between multiple clean and customizable templates.

= Main features =
- Automatic PDF invoice generation and attachment.
- Manually create or delete PDF invoice.
- Attach PDF invoice to multiple WooCommerce email types of your choice.
- Generate PDF packing slips.
- Connect with Google Drive, Egnyte, Dropbox or OneDrive.
- Multiple clean and highly customizable PDF Invoice templates.
- WooCommerce order numbering or built-in sequential invoice numbering.
- Many invoice and date format customization options.
- Advanced items table with refunds, discounts, different item tax rates columns and more.
- Download invoice from My Account page.
- Mark invoices as paid.

> **Invoices for WooCommerce Premium**<br /><br />
> This plugin offers a premium version which comes with the following features:<br /><br />
> - Attach PDF invoices to many more email types including third party plugins<br />
> - Send credit notes and cancelled PDF invoices<br />
> - Fully customize PDF invoice table content by modifying line item columns and total rows<br />
> - Automatically send a reminder email configurable within a specific period of time and display a payment due date<br />
> - Bulk generate PDF invoices<br />
> - Bulk export and/or download PDF invoices<br />
> - Bill periodically by generating and sending global invoices<br />
> - Let customers decide to generate a PDF invoice on checkout<br />
> - Change the font of the PDF invoices<br />
> - Add additional PDF files to PDF invoices<br />
> - Send customer invoices directly to multiple recipients<br />
> - Compatible with [WooCommerce Subscriptions](http://www.woothemes.com/products/woocommerce-subscriptions) plugin emails.<br /><br />
> [Upgrade to Invoices for WooCommerce Premium >>](http://wcpdfinvoices.com)

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
7. Nice and clean template called 'Micro'.
8. Nice and clean template called 'Minimal'.

== Installation ==

#### Automatic installation
Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't even need to leave your web browser. To do an automatic install of WooCommerce, log in to your WordPress admin panel, navigate to the Plugins menu and click Add New.

In the search field type "Invoices for WooCommerce" and click Search Plugins. Once you've found our plugin you can view details about it such as the the point release, rating and description. Most importantly of course, you can install it by simply clicking Install Now. After clicking that link you will be asked if you're sure you want to install the plugin. Click yes and WordPress will automatically complete the installation.

#### Manual installation
The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application.

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation's wp-content/plugins/ directory.
3. Activate the plugin from the Plugins menu within the WordPress admin.

== Frequently Asked Questions ==

#### How to add your custom template?
Copy the default template files (including folder) you'll find in `plugins/woocommerce-pdf-invoices/includes/templates/invoice/simple` to `uploads/woocommerce-pdf-invoices/templates/invoice/simple`. The plugin will automatically detect the template and makes it available for selection within the Template Settings. Now go ahead and start making some changes to the template files! :)

Important: Before you update the plugin, always have a look at the Changelog if their have been any changes to the template files. There will be updates that require updating your custom template!

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
/**
 * Hide order itemmeta on Invoices for WooCommerce' invoice template.
 *
 * @param array $hidden_order_itemmeta itemmeta.
 *
 * @return array
 */
function bewpi_alter_hidden_order_itemmeta( $hidden_order_itemmeta ) {
	$hidden_order_itemmeta[] = '_wc_cog_item_cost';
	$hidden_order_itemmeta[] = '_wc_cog_item_total_cost';
	$hidden_order_itemmeta[] = '_subscription_interval';
    $hidden_order_itemmeta[] = '_subscription_length';
	// end so on..
	return $hidden_order_itemmeta;
}
add_filter( 'bewpi_hidden_order_itemmeta', 'bewpi_alter_hidden_order_itemmeta', 10, 1 );
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
function bewpi_mpdf( $mpdf, $document ) {
    // change the direction of the invoice to RTL
    $mpdf->SetDirectionality( 'rtl' );

    return $mpdf;
}
add_filter( 'bewpi_mpdf', 'bewpi_mpdf', 10, 2 );
`

#### How to display invoice download button on specific template files?
Add below code for example to your "thankyou" page or "customer-completed-order" email template.

`
echo do_shortcode( '[bewpi-download-invoice title="Download (PDF) Invoice {formatted_invoice_number}" order_id="' . $order->get_id() . '"]' );
`

For use in WordPress editor use below shortcode. This will only work if you replace "ORDER_ID" with an actual order id.

`
[bewpi-download-invoice title="Download (PDF) Invoice {formatted_invoice_number}" order_id="ORDER_ID"]
`

Note: Download button will only be displayed when PDF exists and order has been paid.

#### How to skip invoice generation based on specific payment methods?
Add the name of the payment method to the array.

`
function bewpi_attach_invoice_excluded_payment_methods( $payment_methods ) {
    return array( 'bacs', 'cod', 'cheque', 'paypal' );
}
add_filter( 'bewpi_attach_invoice_excluded_payment_methods', 'bewpi_attach_invoice_excluded_payment_methods', 10, 2 );
`

#### How to skip invoice generation in general?
Add below function to your themes 'functions.php' file.

`
function bewpi_skip_invoice_generation( $skip, $status, $order ) {
    // Do your stuff based on the order.

    return true; // True to skip.
}
add_filter( 'bewpi_skip_invoice_generation', 'bewpi_skip_invoice_generation', 10, 3 );
`

#### How to allow specific roles to download invoice?
Add the name of the role to the array. By default shop managers and administrators are allowed to download invoices.

`
function bewpi_allowed_roles_to_download_invoice($allowed_roles) {
    // available roles: shop_manager, customer, contributor, author, editor, administrator
    $allowed_roles[] = "editor";
    // end so on..
    return $allowed_roles;
}
add_filter( 'bewpi_allowed_roles_to_download_invoice', 'bewpi_allowed_roles_to_download_invoice', 10, 2 );
`

### How to alter formatted invoice number? ###
Add following filter function to your 'functions.php' within your theme.

`
function alter_formatted_invoice_number( $formatted_invoice_number, $document_type ) {
   if ( $document_type === 'invoice/global' ) { // 'simple' or 'global'.
      // add M for global invoices.
      return 'M' . $formatted_invoice_number;
   }

   return $formatted_invoice_number;
}
add_filter( 'bewpi_formatted_invoice_number', 'alter_formatted_invoice_number', 10, 2 );
`

### How to add custom fields/meta-data to the PDF invoice template? ###
Use below code to display meta-data. Replace `{META_KEY}` with the actual key. If you use another plugin, just ask the key from the author of that plugin.

`
<?php echo BEWPI()->templater()->get_meta( '{META_KEY}' ); ?>
`

Important: A custom template is required to add a custom field to the PDF invoice.

### How to use a different template based on some order variable? ###
Use below code to use a different template based on WPML order language. You can for example change the function to use a different template based on the payment method instead.

`
/**
 * Change template based on WPML order language.
 * Make sure to create custom templates with the correct names or the templates won't be found.
 *
 * @param string $template_name template name.
 * @param string $template_type template type like global or simple.
 * @param int    $order_id WC Order ID.
 *
 * @return string
 */
function change_template_based_on_order_language( $template_name, $template_type, $order_id ) {
	$order_language = get_post_meta( $order_id, 'wpml_language', true );

	if ( false === $order_language ) {
		return $template_name;
	}

	switch ( $order_language ) {
		case 'en':
			$template_name = 'minimal-en';
			break;
		case 'nl':
			$template_name = 'minimal-nl';
			break;
	}

	return $template_name;
}
add_filter( 'wpi_template_name', 'change_template_based_on_order_language', 10, 3 );
`

### How to add invoice information meta? ###
Use below code to add invoice information meta to the PDF invoice template.

`
/**
 * Add PDF invoice information meta (from third party plugins).
 *
 * @param array         $info Invoice info meta.
 * @param BEWPI_Invoice $invoice Invoice object.
 * @since 2.9.8
 *
 * @return array.
 */
function add_invoice_information_meta( $info, $invoice ) {
	$payment_gateway = wc_get_payment_gateway_by_order( $invoice->order );

	// Add PO Number from 'WooCommerce Purchase Order Gateway' plugin.
	if ( $payment_gateway && 'woocommerce_gateway_purchase_order' === $payment_gateway->get_method_title() ) {
		$po_number = WPI()->get_meta( $invoice->order, '_po_number' );
		if ( $po_number ) {
			$info['po_number'] = array(
				'title' => __( 'Purchase Order Number:', 'woocommerce-pdf-invoices' ),
				'value' => $po_number,
			);
		}
	}

	// Add VAT Number from 'WooCommerce EU VAT Number' plugin.
	$vat_number = WPI()->get_meta( $invoice->order, '_vat_number' );
	if ( $vat_number ) {
		$info['vat_number'] = array(
			'title' => __( 'VAT Number:', 'woocommerce-pdf-invoices' ),
			'value' => $vat_number,
		);
	}

	return $info;
}
add_filter( 'wpi_invoice_information_meta', 'add_invoice_information_meta', 10, 2 );
`

### How to change the invoice date? ###
Use below filter to change the invoice date.

`
/**
 * Change invoice date to order date in order to regenerate old invoices and keep the date.
 *
 * @param string                 $invoice_date date of invoice.
 * @param BEWPI_Abstract_Invoice $invoice      invoice object.
 *
 * @return string needs to be in mysql format.
 */
function change_invoice_date_to_order_date( $invoice_date, $invoice ) {
	// get_date_paid() or get_date_created().
	$date_completed = $invoice->order->get_date_completed();
	if ( null !== $date_completed ) {
		return $date_completed->date( 'Y-m-d H:i:s' );
	}

	return $invoice_date;
}
add_filter( 'wpi_invoice_date', 'change_invoice_date_to_order_date', 10, 2 );
`

#### How to update the PDF invoice when it already has been sent to the customer?
Since version 2.9.4 the plugin removed the ability to update the PDF invoice when it already has been sent to the customer. If in what manner you still want to update the invoice, you can do so by resetting a custom field.

1. Go to Edit Order page.
2. Change custom field 'bewpi_pdf_invoice_sent' value within custom field widget to 0.
3. Refresh page and Update button will appear.

== Changelog ==

= 3.1.9 - October 12, 2021 =

- Fixed: Fixed action name.

= 3.1.8 - September 17, 2021 =

- Fixed: Fatal error.

= 3.1.7 - July 13, 2021 =

- Improved: Plugin name.
- Improved: Translation files.

= 3.1.6 - April 23, 2021 =

- Removed: Iframe and other sidebar social media links.

= 3.1.5 - April 21, 2021 =

- Improved: Translation files.

= 3.1.4 - September 17, 2020 =

- Fixed: Translation files by adding keywords.

= 3.1.3 - August 31, 2020 =

- Fixed: Fatal error.

= 3.1.2 - August 31, 2020 =

- Added: Filter to add custom information.
- Fixed: Fixed last total row border weight.
- Fixed: jQuery .live() has been removed.
- Fixed: Inconsistent number of args passed to woocommerce_order_item_meta_start thanks to @cyjosh.

= 3.1.1 - June 21, 2020 =

- Fixed: Company logo PNG not working.
- Fixed: Company details not showing in template.

= 3.1.0 - June 17, 2020 =

- Added: Packing slip meta box to Edit Order page to generate packing slip.
- Added: Param to filters wpi_after_invoice_content and wpi_before_invoice_content and changed prefix.
- Added: Filter 'wpi_after_document_generation'.
- Added: Filter 'wpi_pdf_invoice_filename' to change the name of the pdf invoice file.
- Added: Filters 'wpi_show_my_account_pdf' and 'wpi_show_download_invoice_shortcode' to override displaying the invoice based on paid status.
- Added: Filter 'wpi_invoice_number' to change the invoice number.
- Improved: Filter 'bewpi_formatted_invoice_number' to 'wpi_formatted_invoice_number'.
- Improved: NL translation files thanks to @freasy.
- Improved: Invoice number format only allowing letters, numbers, whitespaces and hyphens minuses.
- Improved: Formatted company address and details by splitting into separate functions.
- Fixed: Settings error notices not showing.
- Fixed: Replacing placeholders while getting options.
- Fixed: mPDF compatibility with PHP 7.4.

= 3.0.11 - November 8, 2019 =

- Added: Company registration number.

= 3.0.10 - October 28, 2019 =

- Fixed: Fatal errors when using micro template.

= 3.0.9 - October 26, 2019 =

- Improved: Custom logo upload setting by using the native media library.

= 3.0.8 - August 30, 2019 =

- Fixed: Reset invoice counter not deleting invoices.

= 3.0.7 - June 11, 2019 =

- Fixed: Packing slip not displaying custom meta and user meta fields.

= 3.0.6 - June 7, 2019 =

- Improved: Sequential invoice numbering by refactoring code.

= 3.0.5 - May 31, 2019 =

- Fixed: Sequential invoice numbering not incrementing.

= 3.0.4 - May 21, 2019 =

- Fixed: Moving get_formatted_base_address() to WPI() instance.

= 3.0.3 - May 20, 2019 =

- Improved: Renamed 'wpi_invoice_date' filter to 'wpi_invoice_custom_date'.
- Fixed: Debug button not showing on edit order page.
- Fixed: Accessing constant directly from function causing syntax errors.

= 3.0.2 - April 25, 2019 =

- Fixed: Using order number as invoice number.
- Fixed: Fatal error sabre ubl_invoice dependency.
- Fixed: Formatted base address not showing on packing slips.

= 3.0.1 - April, 2019 =

- Fixed: Fatal error PLUGIN_SLUG constant.

= 3.0.0 - April, 2019 =

- Added: Debug settings tab with debug information.
- Added: Loading textdomain from Loco Translate folder wp-content/languages/loco/plugins.
- Added: Filters to add (custom) customer address fields.
- Improved: Translations and updated language files.
- Improved: Options not loading on every request.
- Improved: Only show admin notices when enabled within wp-config.php.
- Improved: Admin notices JS code only loading on plugins.php page.
- Fixed: Not printing invoice details which have empty values.
- Fixed: Translations not working due to update WordPress.

= 2.9.17 - August 9, 2018 =

- Fixed: Removing logo url from settings.

= 2.9.16 - August 3, 2018 =

- Added: 'wpi_order_item_totals_left' action to template.
- Improved: Translation files.
- Fixed: VAT column not always displayed.

= 2.9.15 - July 26, 2018 =

- Added: Check for EU B2B zero rated vat.
- Improved: Templates in general.

= 2.9.14 - July 23, 2018 =

- Improved: Margin between template header and body. 

= 2.9.13 - July 18, 2018 =
 
- Improved: PDF margin and general template design. 
- Fixed: PHP 7.2 compatibility.

= 2.9.12 - February 18, 2018 =

- Fixed: Invoice number column displayed at first place of shop order table.

= 2.9.11 - January 17, 2018 =

- Added: 'wpi_invoice_date' filter to change the date of the invoice.
- Added: Bulk Print PDF Packing Slips action that merges selected packing slips.
- Added: Filter to change the default value of the request invoice checkout field.
- Added: Filter 'bewpi_settings_capability' to change settings permissions.
- Added: Allowance for network admin to view pdf invoice without registering for single site.
- Improved: Alignment of invoice actions within Edit Order page.
- Improved: Language files and translations.
- Fixed: Not existing attachments year folder.

= 2.9.10 - November 13, 2017 =

- Added: Multisite compatibility by changing uploads directory.
- Added: WC required version comments.
- Fixed: Missing $line_items on invoice template for has_only_virtual_products().
- Fixed: Fatal error non-numeric value.
- Fixed: Enhanced select options not removable.

= 2.9.9 - October 19, 2017 =

- Fixed: Parse error: syntax error, unexpected '::'.

= 2.9.8 - October 18, 2017 =

- Added: 'add_invoice_information_meta' filter to add/remove PDF invoice information meta. See FAQ for example code. Make sure to update your custom template!
- Added: 'wpi_item_description_data' filter to modify product description data.
- Fixed: Options with enhanced selections resetting sort order.

= 2.9.7 - October 12, 2017 =

- Fixed: WC 3.2.0 compatibility.
- Fixed: 'bewpi_skip_invoice_generation' filter parameter using order object instead of order total.

= 2.9.6 - October 10, 2017 =

- Added: Filter 'wpi_skip_pdf_invoice_attachment' to skip PDF invoice email attachment.
- Fixed: Non-dismissable notice by temporary disabling it.
- Fixed: PDF invoice marked as sent when sent to admin.

= 2.9.5 - September 20, 2017 =

- Fixed: Download invoice from my account page not showing.
- Fixed: Non-dismissable rate admin notice.

= 2.9.4 - September 13, 2017 =

- Added: Added invoice actions to view, update and delete invoice.
- Added: Action 'wpi_watermark_end' to add multiple watermarks.
- Improved: Language files by adding more keywords.
- Fixed: Company logo not found when protocol has been changed.
- Fixed: [prefix] and/or [suffix] hardcoded in invoice number.
- Fixed: Fixed body options section not showing on settings page.
- Fixed: 'Fatal Error: non-numeric value encountered' when using position absolute.

= 2.9.3 - July 5, 2017 =

- Added: 'wpi_template_name' filter to change the template based on specific order variables. See FAQ.
- Added: 'wpi_email_types' filter to add email types.
- Fixed: PDF abortion error by not using date format from settings for [order-date] since it can have slashes.
- Fixed: Missing argument 3 fatal error due to 'woocommerce_checkout_order_processed' hook used by third party plugins.
- Removed: Greyed out WooCommerce Subscriptions emails.

= 2.9.2 - June 12, 2017 =

- Added: Filter to change the value of the option when using `WPI()->get_option()`. See [Issue #190](https://github.com/baselbers/woocommerce-pdf-invoices/issues/190).
- Added: SKU to packing slip.
- Fixed: Packing slips redirecting to Edit Order page when using micro template. Consider using minimal template. Micro template is deprecated and will probably no longer be supported in future versions.
- Fixed: WC 2.6 compatibility.

= 2.9.1 - May 15, 2017 =

- Improved: Loading settings only on settings pages.
- Improved: Option method for getting options by option group and option name.
- Improved: Main global class function name by renaming it from 'BEWPI()' to 'WPI()'.
- Improved: Viewing packing slip by using Download and Send to browser view modes.
- Improved: Creation of uploads directories only on admin request and plugin activation/update.
- Fixed: 'BEWPI()->templater()->get_meta()' always empty by setting order directly after order creation.
- Fixed: 'Bad gateway' and 'PHP Warning: A non-numeric value encountered' on checkout page due to mPDF 7.1 incompatibility.
- Fixed: ttfontdata folder losing cached font data files by using custom directory in uploads folder.
- Fixed: .html extension added while viewing/downloading packing slip.
- Fixed: VAT number message always showing when '_vat_number_is_valid' is not empty.
- Fixed: Sequential Invoice Number plugin compatibility by using `get_order_number()` instead of `get_id()`.

= 2.9.0 - May 15, 2017 =

- Improved: Spanish translation files thanks to [Jorge Fuentes](http://jorgefuentes.net).
- Improved: Settings classes with a complete refactor.
- Improved: File names by removing unnecessary prefixes.
- Improved: PDF invoice generation by skipping unnecessary PDF invoice update for same request.
- Improved: Deactivation notice by only checking for notice on plugins.php page.
- Fixed: Facebook share button.
- Fixed: Download from my account page not working.
- Fixed: Item meta and download item meta not displayed inline within table cells by stripping `<p>` and `<br>` tags. Update custom template needed!
- Removed: Unused CSS and JS.