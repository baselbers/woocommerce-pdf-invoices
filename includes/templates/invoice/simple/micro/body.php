<?php
$theme_color            = $this->template_options['bewpi_color_theme'];
$is_theme_text_black    = $this->template_options['bewpi_theme_text_black'];
$columns_count          = 4;
echo $this->outlining_columns_html( count( $this->order->get_taxes() ) );
?>
<table class="two-column customer">
	<tbody>
	<tr>
		<td class="address small-font" width="50%">
			<b><?php _e( 'Invoice to', 'woocommerce-pdf-invoices' ); ?></b><br/>
			<?php
			echo $this->order->get_formatted_billing_address() . '<br/>';
			// Billing phone.
			$billing_phone = method_exists( 'WC_Order', 'get_billing_phone' ) ? $this->order->get_billing_phone() : $this->order->billing_phone;
			echo $billing_phone ? sprintf( __( 'Phone: %s', 'woocommerce-pdf-invoices' ), $billing_phone ) : '';
			?>
		</td>
		<?php
		$formatted_shipping_address = $this->order->get_formatted_shipping_address();
		if ( $this->template_options['bewpi_show_ship_to'] && ! empty( $formatted_shipping_address ) && ! $this->has_only_virtual_products() ) { ?>
			<td class="address small-font" width="50%">
				<b><?php _e( 'Ship to:', 'woocommerce-pdf-invoices' ); ?></b><br/>
				<?php echo $formatted_shipping_address; ?>
			</td>
		<?php } ?>
	</tr>
	</tbody>
</table>
<table class="invoice-head">
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php echo WPI()->templater()->get_option( 'bewpi_title' ); ?></h1>
			<span class="number" style="color: <?php echo ( $is_theme_text_black ) ? 'black' : $theme_color; ?>;"><?php echo $this->get_formatted_number(); ?></span><br/>
			<span><?php echo $this->get_formatted_invoice_date(); ?></span><br/><br/>
			<span><?php printf( __( 'Order Number: %s', 'woocommerce-pdf-invoices' ), $this->order->get_order_number() ); ?></span><br/>
			<span><?php printf( __( 'Order Date: %s', 'woocommerce-pdf-invoices' ), $this->get_formatted_order_date() ); ?></span><br/>
			<?php $this->display_purchase_order_number(); ?><br/>
			<?php $this->display_vat_number(); ?>
		</td>
		<td class="total-amount" bgcolor="<?php echo $theme_color; ?>" <?php if ( $is_theme_text_black ) echo 'style="color: black;"'; ?>>
			<h1 class="amount"><?php echo wc_price( $this->order->get_total() - $this->order->get_total_refunded(), array( 'currency' => $this->order->get_currency() ) ); ?></h1>
			<p><?php echo WPI()->templater()->get_option( 'bewpi_intro_text' ); ?></p>
		</td>
	</tr>
	</tbody>
</table>
<table class="products small-font">
	<thead>
	<tr class="table-headers">
		<!-- Description -->
		<th class="align-left"><?php _e( 'Description', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- SKU -->
		<?php if ( $this->template_options['bewpi_show_sku'] ) { ?>
			<?php $columns_count++; ?>
			<th class="align-left"><?php _e( 'SKU', 'woocommerce-pdf-invoices' ); ?></th>
		<?php } ?>
		<!-- Cost -->
		<th class="align-left"><?php _e( 'Cost', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- Qty -->
		<th class="align-left"><?php _e( 'Qty', 'woocommerce-pdf-invoices' ); ?></th>
		<!-- Tax -->
		<?php if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) ) { ?>
			<?php foreach ( $this->order->get_taxes() as $tax_item ) { ?>
				<?php $columns_count++; ?>
				<th class="align-left"><?php echo $tax_item['label'] . ' ' . WC_Tax::get_rate_percent( $tax_item['rate_id'] ); ?></th>
			<?php } ?>
		<?php } ?>
		<!-- Total -->
		<th class="align-right"><?php _e( 'Total', 'woocommerce-pdf-invoices' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<!-- Products -->
	<?php foreach ( $this->order->get_items( 'line_item' ) as $item_id => $item ) {
		$product = $this->order->get_product_from_item( $item ); ?>
		<tr class="product-row">
			<td>
				<?php echo esc_html( $item['name'] );
				global $wpdb;

				$hidden_order_itemmeta = apply_filters( 'woocommerce_hidden_order_itemmeta', array(
					'_qty',
					'_tax_class',
					'_product_id',
					'_variation_id',
					'_line_subtotal',
					'_line_subtotal_tax',
					'_line_total',
					'_line_tax',
					'_wc_cog_item_cost',
					'_wc_cog_item_total_cost',
					'_reduced_stock',
				) );

				$hidden_order_itemmeta = apply_filters( 'bewpi_hidden_order_itemmeta', $hidden_order_itemmeta );

				foreach ( $this->order->has_meta( $item_id ) as $meta ) {
					// Skip hidden core fields.
					if ( in_array( $meta['meta_key'], $hidden_order_itemmeta, true ) ) {
						continue;
					}

					// Skip serialised meta.
					if ( is_serialized( $meta['meta_value'] ) ) {
						continue;
					}

					// Get attribute data.
					if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
						$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
						$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
						$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
					} else {
						$meta['meta_key'] = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $product ), $meta['meta_key'] );
					}

					echo '<div class="item-attribute"><span style="font-weight: bold;">' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ': </span>' . wp_kses_post( rawurldecode( $meta['meta_value'] ) ) . '</div>';
				}
				?>
			</td>
			<?php if ( $this->template_options['bewpi_show_sku'] ) { ?>
				<td><?php echo ( $product && $product->get_sku() ) ? $product->get_sku() : '-'; ?></td>
			<?php } ?>
			<td>
				<?php
				if ( isset( $item['line_total'] ) ) {
					if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
						echo '<del>' . wc_price( $this->order->get_item_subtotal( $item, false, true ), array( 'currency' => $this->order->get_currency() ) ) . '</del> ';
					}
					echo wc_price( $this->order->get_item_total( $item, false, true ), array( 'currency' => $this->order->get_currency() ) );
				}
				?>
			</td>
			<td>
				<?php
				echo $item['qty'];
				$refunded_qty = $this->order->get_qty_refunded_for_item( $item_id );
				if ( $refunded_qty ) {
					echo '<br/><small class="refunded">-' . $refunded_qty . '</small>';
				}
				?>
			</td>
			<?php
			if ( $this->template_options['bewpi_show_tax'] && empty( $legacy_order ) && wc_tax_enabled() ) :
				$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
				$tax_data      = maybe_unserialize( $line_tax_data );

				foreach ( $this->order->get_taxes() as $tax_item ) :
					$tax_item_id       = $tax_item['rate_id'];
					$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
					$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
					?>
					<td class="item-tax">
						<?php
						if ( isset( $tax_item_total ) ) {
							if ( isset( $tax_item_subtotal ) && $tax_item_subtotal !== $tax_item_total ) {
								echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $this->order->get_currency() ) ) . '</del> ';
							}

							echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $this->order->get_currency() ) );
						} else {
							echo '&ndash;';
						}

						$refunded = $this->order->get_tax_refunded_for_item( $item_id, $tax_item_id );
						if ( $refunded ) {
							echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_currency() ) ) . '</small>';
						}
						?>
					</td>

					<?php
				endforeach;
			endif;
			?>
			<td class="align-right item-total" width="">
				<?php
				if ( isset( $item['line_total'] ) ) {
					$incl_tax = $this->template_options['bewpi_display_prices_incl_tax'];

					if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
						echo '<del>' . wc_price( $this->order->get_line_subtotal( $item, $incl_tax, true ), array( 'currency' => $this->order->get_currency() ) ) . '</del> ';
					}

					echo wc_price( $this->order->get_line_total( $item, $incl_tax, true ), array( 'currency' => $this->order->get_currency() ) );
				}

				$refunded = $this->order->get_total_refunded_for_item( $item_id );
				if ( $refunded ) {
					echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_currency() ) ) . '</small>';
				}
				?>
			</td>
		</tr>
	<?php } ?>
	<!-- Space -->
	<tr class="space">
		<td colspan="<?php echo $columns_count; ?>"></td>
	</tr>
	<!-- Table footers -->
	<!-- Discount -->
	<?php $colspan = $this->get_colspan( $columns_count ); ?>
	<?php if ( $this->template_options['bewpi_show_discount'] && $this->order->get_total_discount() !== 0.00 ) { ?>
		<tr class="discount after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Discount', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_discount(), array( 'currency' => $this->order->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping -->
	<?php if ( $this->template_options['bewpi_show_shipping'] && $this->template_options['bewpi_shipping_taxable'] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Shipping', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Subtotal -->
	<?php if ( $this->template_options['bewpi_show_subtotal'] ) { ?>
		<tr class="subtotal after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Subtotal', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo $this->get_formatted_subtotal(); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping -->
	<?php if( $this->template_options['bewpi_show_shipping'] && ! (bool)$this->template_options["bewpi_shipping_taxable"] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'Shipping', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Fees -->
	<?php
	$line_items_fee      = $this->order->get_items( 'fee' );
	foreach ( $line_items_fee as $item_id => $item ) :
		?>
		<tr class="after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php echo ! empty( $item['name'] ) ? esc_html( $item['name'] ) : __( 'Fee', 'woocommerce' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right">
				<?php
				echo ( isset( $item['line_total'] ) ) ? wc_price( wc_round_tax_total( $item['line_total'] ) ) : '';

				if ( $refunded = $this->order->get_total_refunded_for_item( $item_id, 'fee' ) ) {
					echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_currency() ) ) . '</small>';
				}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	<!-- Tax -->
	<?php if ( $this->template_options['bewpi_show_tax_total'] && wc_tax_enabled() ) :
		foreach ( $this->order->get_tax_totals() as $code => $tax ) : ?>
			<tr class="after-products">
				<td colspan="<?php echo $colspan['left']; ?>"></td>
				<td colspan="<?php echo $colspan['right_left']; ?>"><?php echo $tax->label . ' ' . WC_Tax::get_rate_percent( $tax->rate_id ); ?></td>
				<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo $tax->formatted_amount; ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<!-- Zero Rate VAT -->
	<?php if ( $this->display_zero_rated_vat() ) { ?>
		<tr class="after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>"><?php _e( 'VAT 0%' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="align-right"><?php echo wc_price( 0, array( 'currency' => $this->order->get_currency() ) );  ?></td>
		</tr>
	<?php } ?>
	<!-- Total -->
	<tr class="after-products">
		<td colspan="<?php echo $colspan['left']; ?>"></td>
		<td colspan="<?php echo $colspan['right_left']; ?>" class="total"><?php _e( 'Total', 'woocommerce-pdf-invoices' ); ?></td>
		<td colspan="<?php echo $colspan['right_right']; ?>" class="grand-total align-right" style="color: <?php echo ( $is_theme_text_black ) ? 'black' : $theme_color; ?>;">
			<?php echo $this->get_formatted_total(); ?>
		</td>
	</tr>
	<!-- Refunded -->
	<?php if ( $this->order->get_total_refunded() > 0 ) { ?>
		<tr class="after-products">
			<td colspan="<?php echo $colspan['left']; ?>"></td>
			<td colspan="<?php echo $colspan['right_left']; ?>" class="refunded"><?php _e( 'Refunded', 'woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $colspan['right_right']; ?>" class="refunded align-right"><?php echo '-' . wc_price( $this->order->get_total_refunded(), array( 'currency' => $this->order->get_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	</thead>
</table>
<table id="terms-notes">
	<!-- Notes & terms -->
	<tr>
		<td class="border" colspan="3">
			<?php echo nl2br( WPI()->templater()->get_option( 'bewpi_terms' ) ); ?><br/>
			<?php
			if ( $this->template_options['bewpi_show_customer_notes'] ) :
				// Note added by customer.
				$customer_note = method_exists( 'WC_Order', 'get_customer_note' ) ? $this->order->get_customer_note() : $this->order->customer_note;
				if ( $customer_note ) {
					echo '<p><strong>' . __( 'Customer note', 'woocommerce-pdf-invoices' ) . ' </strong> ' . $customer_note . '</p>';
				}
				// Notes added by administrator on order details page.
				$customer_order_notes = $this->order->get_customer_order_notes();
				if ( count( $customer_order_notes ) > 0 ) {
					echo '<p><strong>' . __('Customer note', 'woocommerce-pdf-invoices') . ' </strong>' . $customer_order_notes[0]->comment_content . '</p>';
				}
			endif;
			?>
		</td>
	</tr>
	<?php if ( $this->display_zero_rated_vat() ) { ?>
		<tr>
			<td class="border" colspan="3">
				<?php _e( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' ); ?>
			</td>
		</tr>
	<?php } ?>
</table>