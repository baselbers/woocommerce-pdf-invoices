<table class="two-column customer">
	<tbody>
	<tr>
		<td class="address small-font">
			<b><?php _e( 'Invoice to', 'be-woocommerce-pdf-invoices' ); ?></b><br/>
			<?php echo $this->order->get_formatted_billing_address(); ?><br/>
			<?php if ( $this->order->billing_phone != "" ) printf( __( 'Phone: %s', 'be-woocommerce-pdf-invoices' ), $this->order->billing_phone ); ?>
		</td>
		<td class="address small-font">
			<?php if ( $this->order->get_formatted_shipping_address() != "" ) { ?>
				<b><?php _e( 'Ship to', 'be-woocommerce-pdf-invoices' ); ?></b><br/>
				<?php echo $this->order->get_formatted_shipping_address(); ?>
			<?php } ?>
		</td>
	</tr>
	</tbody>
</table>
<table class="invoice-head">
	<tbody>
	<tr>
		<td class="invoice-details">
			<h1 class="title"><?php _e( 'Invoice', 'be-woocommerce-pdf-invoices' ); ?></h1>
			<span class="number" style="color: <?php echo $this->template_options['bewpi_color_theme']; ?>;"><?php echo $this->get_formatted_number(); ?></span><br/>
			<span class="small-font"><?php echo $this->get_formatted_invoice_date(); ?></span><br/><br/>
			<span class="small-font"><?php printf( __( 'Order Number: %s', 'be-woocommerce-pdf-invoices' ), $this->order->get_order_number() ); ?></span><br/>
			<span class="small-font"><?php printf( __( 'Order Date: %s', 'be-woocommerce-pdf-invoices' ), $this->get_formatted_order_date() ); ?></span><br/><br/>
		</td>
		<td class="total-amount" bgcolor="<?php echo $this->template_options['bewpi_color_theme']; ?>">
				<span>
					<h1 class="amount"><?php echo wc_price( $this->order->get_total() - $this->order->get_total_refunded(), array( 'currency' => $this->order->get_order_currency() ) ); ?></h1>
					<p><?php echo $this->template_options['bewpi_intro_text']; ?></p>
				</span>
		</td>
	</tr>
	</tbody>
</table>
<?php echo $this->outlining_columns_html(); ?>
<table class="products small-font">
	<thead>
	<tr class="table-headers">
		<!-- Description -->
		<th class="align-left"><?php _e( 'Description', 'be-woocommerce-pdf-invoices' ); ?></th>
		<!-- SKU -->
		<?php
		if( $this->template_options['bewpi_show_sku'] ) {
			echo '<th class="align-left">' . __( "SKU", 'be-woocommerce-pdf-invoices' ) . '</th>';
		}
		?>
		<!-- Cost -->
		<th class="align-left"><?php _e( 'Cost', 'be-woocommerce-pdf-invoices' ); ?></th>
		<!-- Qty -->
		<th class="align-left"><?php _e( 'Qty', 'be-woocommerce-pdf-invoices' ); ?></th>
		<!-- Tax -->
		<?php
		$order_taxes    = $this->order->get_taxes();
		if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) && ! empty( $order_taxes ) ) :
			foreach ( $order_taxes as $tax_id => $tax_item ) :
				$tax_label = __( 'VAT', 'be-woocommerce-pdf-invoices' );
				$column_label = ! empty( $tax_item['label'] ) ? $tax_item['label'] : $tax_label;
				?>
				<th class="align-left">
					<?php echo $column_label; ?>
				</th>
			<?php
			endforeach;
		endif;
		?>
		<!-- Total -->
		<th class="align-right"><?php _e( 'Total', 'be-woocommerce-pdf-invoices' ); ?></th>
	</tr>
	</thead>
	<!-- Products -->
	<tbody>
	<?php foreach( $this->order->get_items( 'line_item' ) as $item_id => $item ) {
		$product = wc_get_product( $item['product_id'] ); ?>
		<tr class="product-row">
			<td>
				<?php echo $product->get_title(); ?>
				<?php
				global $wpdb;

				if ( $metadata = $this->order->has_meta( $item_id ) ) {
					foreach ( $metadata as $meta ) {

						// Skip hidden core fields
						if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
							'_qty',
							'_tax_class',
							'_product_id',
							'_variation_id',
							'_line_subtotal',
							'_line_subtotal_tax',
							'_line_total',
							'_line_tax',
						) ) ) ) {
							continue;
						}

						// Skip serialised meta
						if ( is_serialized( $meta['meta_value'] ) ) {
							continue;
						}

						// Get attribute data
						if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
							$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
							$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
						} else {
							$meta['meta_key']   = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $product ), $meta['meta_key'] );
						}

						echo '<div class="item-attribute"><span style="font-weight: bold;">' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ': </span>' . wp_kses_post( rawurldecode( $meta['meta_value'] ) ) . '</div>';
					}
				}
				?>
			</td>
			<?php
			if ( $this->template_options['bewpi_show_sku'] ) :
				echo '<td>';
				echo ( $product->get_sku() != '' ) ? $product->get_sku() : '-';
				echo '</td>';
			endif;
			?>
			<td>
				<?php
				if ( isset( $item['line_total'] ) ) {
					if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
						echo '<del>' . wc_price( $this->order->get_item_subtotal( $item, false, true ), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> ';
					}
					echo wc_price( $this->order->get_item_total( $item, false, true ), array( 'currency' => $this->order->get_order_currency() ) );
				}
				?>
			</td>
			<td>
				<?php
				echo $item['qty'];

				if ( $refunded_qty = $this->order->get_qty_refunded_for_item( $item_id ) )
					echo '<br/><small class="refunded">-' . $refunded_qty . '</small>';
				?>
			</td>
			<?php
			if ( empty( $legacy_order ) && wc_tax_enabled() && $this->template_options['bewpi_show_tax'] ) :
				$line_tax_data = isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '';
				$tax_data      = maybe_unserialize( $line_tax_data );

				foreach ( $this->order->get_taxes() as $tax_item ) :
					$tax_item_id       = $tax_item['rate_id'];
					$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
					$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
					?>

					<td class="item-tax">
						<?php
						if ( '' != $tax_item_total ) {
							if ( isset( $tax_item_subtotal ) && $tax_item_subtotal != $tax_item_total ) {
								echo '<del>' . wc_price( wc_round_tax_total( $tax_item_subtotal ), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> ';
							}

							echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $this->order->get_order_currency() ) );
						} else {
							echo '&ndash;';
						}

						if ( $refunded = $this->order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
							echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_order_currency() ) ) . '</small>';
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
					$incl_tax = (bool)$this->template_options[ 'bewpi_display_prices_incl_tax' ];

					if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
						echo '<del>' . wc_price( $this->order->get_line_subtotal( $item, $incl_tax, true ), array( 'currency' => $this->order->get_order_currency() ) ) . '</del> ';
					}

					echo wc_price( $this->order->get_line_total( $item, $incl_tax, true ), array( 'currency' => $this->order->get_order_currency() ) );
				}

				if ( $refunded = $this->order->get_total_refunded_for_item( $item_id ) ) {
					echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_order_currency() ) ) . '</small>';
				}
				?>
			</td>
		</tr>
	<?php } ?>
	<!-- Space -->
	<tr class="space">
		<td colspan="<?php echo $this->columns_count; ?>"></td>
	</tr>
	</tbody>
	<tfoot>
	<!-- Table footers -->
	<!-- Discount -->
	<?php if( $this->template_options['bewpi_show_discount'] && $this->order->get_total_discount() !== 0.00 ) { ?>
		<tr class="discount after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Discount', 'be-woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_discount(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping -->
	<?php if( $this->template_options['bewpi_show_shipping'] && (bool)$this->template_options["bewpi_shipping_taxable"] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Shipping', 'be-woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Subtotal -->
	<?php if( $this->template_options['bewpi_show_subtotal'] ) { ?>
		<tr class="subtotal after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Subtotal', 'be-woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->get_subtotal(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Shipping -->
	<?php if( $this->template_options['bewpi_show_shipping'] && ! (bool)$this->template_options["bewpi_shipping_taxable"] ) { ?>
		<tr class="shipping after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Shipping', 'be-woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	<!-- Fees -->
	<?php
	$line_items_fee      = $this->order->get_items( 'fee' );
	foreach ( $line_items_fee as $item_id => $item ) :
		?>
		<tr class="after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php echo ! empty( $item['name'] ) ? esc_html( $item['name'] ) : __( 'Fee', 'woocommerce' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right">
				<?php
				echo ( isset( $item['line_total'] ) ) ? wc_price( wc_round_tax_total( $item['line_total'] ) ) : '';

				if ( $refunded = $this->order->get_total_refunded_for_item( $item_id, 'fee' ) ) {
					echo '<br/><small class="refunded">-' . wc_price( $refunded, array( 'currency' => $this->order->get_order_currency() ) ) . '</small>';
				}
				?>
			</td>
		</tr>
	<?php endforeach; ?>
	<!-- Tax -->
	<?php if( $this->template_options['bewpi_show_tax_total'] && wc_tax_enabled() ) :
		foreach ( $this->order->get_tax_totals() as $code => $tax ) : ?>
			<tr class="after-products">
				<td colspan="<?php echo $this->colspan['left']; ?>"></td>
				<td colspan="<?php echo $this->colspan['right_left']; ?>"><?php echo $tax->label; ?></td>
				<td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo $tax->formatted_amount; ?></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	<!-- Total -->
	<tr class="after-products">
		<td colspan="<?php echo $this->colspan['left']; ?>"></td>
		<td colspan="<?php echo $this->colspan['right_left']; ?>" class="total"><?php _e( 'Total', 'be-woocommerce-pdf-invoices' ); ?></td>
		<td colspan="<?php echo $this->colspan['right_right']; ?>" class="grand-total align-right" style="color: <?php echo $this->template_options['bewpi_color_theme']; ?>;"><?php echo $this->get_total(); ?></td>
	</tr>
	<!-- Refunded -->
	<?php if ( $this->order->get_total_refunded() > 0 ) { ?>
		<tr class="after-products">
			<td colspan="<?php echo $this->colspan['left']; ?>"></td>
			<td colspan="<?php echo $this->colspan['right_left']; ?>" class="refunded"><?php _e( 'Refunded', 'be-woocommerce-pdf-invoices' ); ?></td>
			<td colspan="<?php echo $this->colspan['right_right']; ?>" class="refunded align-right"><?php echo '-' . wc_price( $this->order->get_total_refunded(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
		</tr>
	<?php } ?>
	</tfoot>
</table>
<table id="terms-notes">
	<!-- Notes & terms -->
	<tr>
		<td class="border" colspan="3">
			<?php echo nl2br( $this->template_options['bewpi_terms'] ); ?><br/>
			<?php
			if ( $this->template_options['bewpi_show_customer_notes'] ) :
				// Note added by customer.
				if ( $this->order->post->post_excerpt != "" ) {
					echo '<p><strong>' . __( 'Customer note', 'be-woocommerce-pdf-invoices' ) . ' </strong> ' . $this->order->post->post_excerpt . '</p>';
				}
				// Notes added by administrator on order details page.
				$customer_order_notes = $this->order->get_customer_order_notes();
				if ( count( $customer_order_notes ) > 0 ) {
					echo '<p><strong>' . __('Customer note', 'be-woocommerce-pdf-invoices') . ' </strong>' . $customer_order_notes[0]->comment_content . '</p>';
				}
			endif;
			?>
		</td>
	</tr>
</table>