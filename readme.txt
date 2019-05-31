=== Plugin Name ===
Contributors: baaaaas
Donate link: 
Tags: woocommerce pdf invoices, invoice, packing slips, delivery note, packing list, shipping list, generate, pdf, woocommerce, attachment, email, customer invoice, processing, vat, tax, sequential, number, dropbox, google drive, onedrive, egnyte, cloud, storage
Requires at least: 4.0
Tested up to: 5.2
Stable tag: 3.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically generate and attach customizable PDF Invoices and PDF Packing Slips for WooCommerce emails and directly send to Dropbox, Google Drive, OneDrive or Egnyte.

== Description ==
*Invoicing can be time consuming. Well, not anymore! WooCommerce PDF Invoices automates the invoicing process by generating and sending it to your customers.*

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

> **WooCommerce PDF Invoices Premium**<br /><br />
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
7. Nice and clean template called 'Micro'.
8. Nice and clean template called 'Minimal'.

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
 * Hide order itemmeta on WooCommerce PDF Invoices' invoice template.
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

= 2.8.1 - April 21, 2017 =

- Added: Option to disable Packing Slips.
- Added: Composer (PHP 5.2 compatible) classmap autoloading.
- Added: Custom `get_order_item_totals()` method to be able to override and did some backporting.
- Improved: JavaScript by only running code on correct page.
- Improved: Plugin size by using forked mpdf repo and removed a lot of default fonts to keep only [fonts that cover most languages/characters](https://mpdf.github.io/fonts-languages/fonts-language-cover-v5-x.html).
- Improved: Font by switching from 'Arial' to 'dejavusanscondensed' for best character coverage.
- Improved: WooCommerce compatibility.
- Improved: Prefix and suffix by removing unnecessary '[prefix]' and '[suffix]' placeholders.s
- Improved: Language files.
- Improved: Admin notices hooks only loading on admin.
- Improved: 'bewpi_mpdf' filter by moving it directly before document write and added document object as argument.
- Fixed: Template specific settings not always showing. Make sure your custom template contains template name in order to get template specific settings.
- Fixed: 'PHP Warning:  copy(): The first argument to copy() function cannot be a directory' when moving PDF invoices to new uploads directory.
- Removed: Refunds in totals on 'Minimal' invoice template.

= 2.8.0 - April 19, 2017 =

- Added: Packing Slip PDF document (for 'Minimal' template, not 'Micro').
- Fixed: 'Warning: ReflectionProperty::getValue() expects exactly 1 parameter, 0 given'.

= 2.7.3 - April 18, 2017 =

- Improved: `setup_directories()` running on every (admin) request by only running when `WPI_UPLOADS_DIR` does not exists.
- Fixed: 'Call to undefined function bewpi_get_id()' by dumping Composer autoloading.
- Fixed: `GLOB_BRACE` unsupported on some systems.

= 2.7.2 - April 18, 2017 =

- Added: Filter 'bewpi_my_account_pdf_name' to change the name of the PDF button on My Account page.
- Fixed: 'Fatal error: Call to a member function get_id() on null' by checking object type in method `add_emailitin_as_recipient()`.

= 2.7.1 - April 14, 2017 =

- Fixed: 'PHP Fatal error:  Call to undefined method WC_Order::get_id()'.

= 2.7.0 - April 13, 2017 =

- Added: A brand new template inspired by [NextStepWebs](https://github.com/NextStepWebs/simple-html-invoice-template) called 'Minimal' that makes use of the new `BEWPI()->templater()` class. Important: 'Micro' template is deprecated and will no longer be supported. We've created a petition [#162](https://github.com/baselbers/woocommerce-pdf-invoices/issues/162) where you can leave a vote to keep the 'Micro' template.
- Added: 'composer.json' file, requiring mPDF and using autoloading.
- Added: Class `BEWPI_Template` which serves all template data. Your custom template needs an update!
- Added: 'bewpi_skip_invoice_generation' filter to skip invoice based on order data like products, categories etc.
- Improved: Uploads directory by moving all files (templates, invoices and fonts) to new 'uploads/woocommerce-pdf-invoices' directory! Do not use the old uploads/bewpi-invoices and uploads/bewpi-templates anymore!
- Improved: `load_plugin_textdomain` method by using locale filter.
- Improved: File structure by moving partials to includes/admin/views.
- Improved: Invoice number reset by using transient instead of updating complete template options.
- Improved: Template by using `printf()` for better readability and added html escaping.
- Improved: Template by checking for company logo url to display logo.
- Improved: `get_template()` method by moving it to `BEWPI_Abstract_Document` class so child classes can use it.
- Improved: 'bewpi_before_document_generation' action by changing arguments array into separate variables.
- Improved: Invoice `type` variable by using relative paths (invoice/simple and invoice/global), so renamed 'invoices' directory to singular 'invoice'.
- Improved: `templater()` by setting `$order` object as a class variable, so the class methods can make use of it instead of using `$order_id` as param.
- Improved: Settings descriptions due to new template.
- Improved: Settings page by not showing related settings based on selected template.
- Fixed: PDF invoice url by changing order of filter arguments.
- Fixed: 'Invoice No.' column not always before 'Actions' column on Shop Order page.
- Fixed: '_bewpi_pdf_invoice_path' postmeta only created when option 'Reset yearly' is enabled.
- Fixed: WooCommerce 3.x.x+ compatibility.
- Removed: Unused and unnecessary actions 'bewpi_before_output_template_to_buffer' and 'bewpi_after_output_template_to_buffer'.
- Removed: 'bewpi_lang_dir' filter, because WordPress made update-safe directory 'wp-content/languages/plugins'.
- Removed: `get_template_dir()` method. Using `BEWPI()->templater->get_template()` instead.
- Removed: Open Sans font and replaced it with Arial due to the use of composer. We load all fonts from mPDF library now.

= 2.6.4 - March 6, 2017 =

- Fixed: 'Fatal error:  Call to a member function get_total() on null' by checking for `WC_Order` object type within `attach_invoice_to_email()` method.

= 2.6.3 - February 24, 2017 =

- Added: Option to disable generation of PDF invoices for orders with only free products.
- Improved: Font usage by defining font-family within style.css file.
- Improved: Translation files.
- Fixed: Company logo not found by checking for possible modified image source url.
- Fixed: PDF invoices not updated (with paid watermark) when order has been modified.
- Fixed: Customer shipping address always showing.
- Fixed: 'Update Failed: Internal Server Error' when updating plugin (from version 2.5.7-) by temporary changing max_execution_time setting.
- Fixed: Company logo url not saving due to use of `esc_url()` regarding special characters.

= 2.6.2 - February 15, 2017 =

- Improved: Company logo setting by just using a attachment url from Media Library. Note that the image won't be shown on settings page anymore.
- Improved: 'readme.txt' and 'settings-sidebar.php' files.
- Fixed: `bewpi_mpdf` filter not working properly. Should be placed before writing html.
- Fixed: Invoice number compatibility with third party plugins by using `get_order_number()` method instead of `id`.
- Fixed: Invoice number column on mobile by adding a '-' when no invoice number exists.

= 2.6.1 - January 30, 2017 =

- Improved: `bewpi-install-date` option name by renaming it to `bewpi_install_date`.
- Improved: `_bewpi_on_plugin_update` function to use less database calls and memory. Allocate at least 256MB in order to update to version 2.6+.

= 2.6.0 - January 29, 2017 =

- Added: 'bewpi_formatted_invoice_number_order_date' filter to change format of order date within formatted invoice number.
- Added: VAT rate percentages. Update your custom template within uploads folder!
- Added: '_bewpi_invoice_pdf_path' postmeta to easily get the path to the invoice due to possible pdf location changes.
- Improved: Check if invoice has been generated by using '_bewpi_invoice_number' and '_bewpi_invoice_pdf_path' postmeta.
- Improved: Reducing database calls by making 'exists()' method static so a complete invoice does not need to be initialized.
- Improved: '_bewpi_invoice_date' postmeta has been updated to mysql date in order to prevent faulty formatting.
- Improved: Reducing database calls by not using/saving '_bewpi_formatted_invoice_number' and '_bewpi_invoice_year'. Instead use '_bewpi_invoice_date' and '_bewpi_invoice_number' and format invoice number and get year within code.
- Improved: Language files.
- Fixed: PDF invoice not attached to emails due to wrong path to file given.
- Fixed: 'Fatal error: Call to a member function is_virtual() on boolean' by changing expression from 'null' to 'boolean' due to type checking operator. Update your custom template within uploads folder!
- Fixed: 'Fatal error: Call to a member function get_title() on a non-object' when trying to generate invoice with deleted product. Update your custom template within uploads folder!

= 2.5.7 - January 20, 2017 =

- Improved: Directory security by adding .htaccess and index.php to pdf invoices directory.

= 2.5.6 - January 19, 2017 =

- Improved: Language files.
- Improved: Number of database calls to check if invoice exists.
- Improved: Only deleting invoices with numbers greater then next number when using counter reset.
- Fixed: 'view' action translatable.
- Fixed: Not correct next number displayed on template settings page by changing input html attribute disabled to readonly.

= 2.5.5 - January 12, 2017 =

- Improved: Language files.
- Improved: Plugin action links on plugins.php page.
- Improved: `get_total()` method to `get_formatted_total()` and `get_subtotal()` to `get_formatted_subtotal()`. Custom templates in uploads folder need to be updated!
- Fixed: "Access denied" for customer trying to download/view pdf invoice by separating code into admin and frontend callback methods.
- Fixed: "Fatal error: require_once(): Failed opening required 'header.php'" when custom template has been deleted and user option still has custom template set.

= 2.5.4 - January 10, 2017 =

- Added: '[order-date]' and '[order-number]' to invoice number option.
- Improved: Language files.
- Fixed: Customers shipping address not displayed at all.
- Fixed: Total amount not calculating with refunds.

= 2.5.3 - January 9, 2017 =

- Added: Option to show shipping address and do not show shipping address when order has only virtual products.
- Fixed: Reset counter option.
- Fixed: Settings sidebar font color conflicts.
- Fixed: Invoice number column on Shop Order page always visible.

= 2.5.2 - January 5, 2017 =

- Fixed: "Expression is not allowed as class constant value" due to PHP versions older then 5.6.

= 2.5.1 - January 5, 2017 =

- Fixed: "Warning: array_merge(): Argument #2 is not an array" by casting empty get_option to array.
- Fixed: "Parse error: syntax error, unexpected T_OBJECT_OPERATOR" by not using class member access on instantiation.

= 2.5.0 - January 5, 2017 =

- Added: Invoice number column on Shop Order page.
- Added: Czech Republic language files thanks to Stanislav Cihak.
- Improved: All language files.
- Improved: Overall code from BE_WooCommerce_PDF_Invoices class and Settings classes by following WordPress Coding Standards and removing unnecessary variables, functions etc. (long way to go but it's a start)
- Improved: Email attachment option with multiple checkboxes to attach invoice to multiple email types.
- Improved: Admin notices by using transients and did some separation of concern by creating a new class file for admin notices.
- Fixed: Fatal error "tfoot must appear before tbody" by deleting tfoot and added thead so the header will appear on multiple pages. The tfoot does not need to be on all pages.
- Fixed: Not sending email when there are multiple BCC headers.
- Fixed: Hidden order itemmeta hiding on admin pages by adding custom filter "bewpi_hidden_order_itemmeta".
- Fixed: Activation admin notice keeps displaying when redirected to different page.
- Removed: Filters 'bewpi_paid_watermark_excluded_payment_methods' and 'bewpi_paid_watermark_excluded_order_statuses', because there is no reason to show watermark based on order status or payment method. Watermark should only be displayed when order has been paid for, so order status should be Processing or Completed. Using WooCommerce' "is_paid" function to achieve this.

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
