<style>
    <?php
    // Create css for outlining the product cells.
    $righter_product_row_tds_css = "";
    for ( $td = $this->colspan['left'] + 1; $td <= $this->number_of_columns; $td++ ) {
    if ( $td !== $this->number_of_columns ) {
        $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . "),";
    } else {
          $righter_product_row_tds_css .= "tr.product-row td:nth-child(" . $td . ")";
          $righter_product_row_tds_css .= "{ width: " . ( 50 / $this->colspan['right'] ) . "%; }";
      }
    }
    echo $righter_product_row_tds_css;
    ?>
    tr.product-row td:nth-child(1) {
        width: <?php echo $this->desc_cell_width; ?>;
    }
</style>
<table class="products small-font">
        <thead>
        <tr class="table-headers">
            <th class="align-left"><?php _e( 'Description', $this->textdomain ); ?></th>
            <?php
            if( $this->template_options['bewpi_show_sku'] ) {
                echo '<th class="align-left">' . __( "SKU", $this->textdomain ) . '</th>';
            }
            ?>
	        <th class="align-left"><?php _e( 'Cost', $this->textdomain ); ?></th>
            <th class="align-left"><?php _e( 'Qty', $this->textdomain ); ?></th>

	        <!-- Tax -->
	        <?php
	        $order_taxes    = $this->order->get_taxes();
	        if ( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() && empty( $legacy_order ) && ! empty( $order_taxes ) ) :
		        foreach ( $order_taxes as $tax_id => $tax_item ) :
			        $column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'VAT', $this->textdomain );
			        ?>
			        <th class="align-left">
				        <?php echo esc_attr( $column_label ); ?>
			        </th>
		        <?php
		        endforeach;
	        endif;
	        ?>

            <th class="align-right"><?php _e( 'Total', $this->textdomain ); ?></th>
        </tr>
        </thead>
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
		                if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) {
			                echo '<del>' . wc_price( $item['line_subtotal'], array( 'currency' => $this->order->get_order_currency() ) ) . '</del> ';
		                }
		                echo wc_price( $item['line_total'], array( 'currency' => $this->order->get_order_currency() ) );
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
	        <td colspan="<?php echo $this->number_of_columns; ?>"></td>
        </tr>
        <!-- Table footers -->
        <!-- Discount -->
        <?php if( $this->template_options['bewpi_show_discount'] ) { ?>
            <tr class="discount after-products">
                <td colspan="<?php echo $this->colspan['left']; ?>"></td>
                <td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Discount', $this->textdomain ); ?></td>
                <td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_discount(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Shipping -->
        <?php if( $this->template_options['bewpi_show_shipping'] ) { ?>
            <tr class="shipping after-products">
                <td colspan="<?php echo $this->colspan['left']; ?>"></td>
                <td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Shipping', $this->textdomain ); ?></td>
                <td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Subtotal -->
        <?php if( $this->template_options['bewpi_show_subtotal'] ) { ?>
            <tr class="subtotal after-products">
                <td colspan="<?php echo $this->colspan['left']; ?>"></td>
                <td colspan="<?php echo $this->colspan['right_left']; ?>"><?php _e( 'Subtotal', $this->textdomain ); ?></td>
                <td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo wc_price( $this->order->get_subtotal(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Tax -->
        <?php if( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() ) {
	        foreach ( $this->order->get_tax_totals() as $code => $tax ) : ?>
		        <tr class="after-products">
                    <td colspan="<?php echo $this->colspan['left']; ?>"></td>
			        <td colspan="<?php echo $this->colspan['right_left']; ?>">
				        <?php printf( __( 'VAT %s', $this->textdomain ), WC_Tax::get_rate_percent( $tax->rate_id ) ); ?>
			        </td>
			        <td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo $tax->formatted_amount; ?></td>
		        </tr>
	        <?php endforeach; ?>
        <?php } ?>
        <!-- Total -->
        <tr class="after-products">
            <td colspan="<?php echo $this->colspan['left']; ?>"></td>
            <td colspan="<?php echo $this->colspan['right_left']; ?>" class="total"><?php _e( 'Total', $this->textdomain ); ?></td>
            <td colspan="<?php echo $this->colspan['right_right']; ?>" class="grand-total align-right" style="color: <?php echo $this->template_options['bewpi_color_theme']; ?>;"><?php echo $this->get_total(); ?></td>
        </tr>
        <!-- Refunded -->
        <?php if ( $this->order->get_total_refunded() > 0 ) { ?>
        <tr class="after-products">
            <td colspan="<?php echo $this->colspan['left']; ?>"></td>
            <td colspan="<?php echo $this->colspan['right_left']; ?>" class="refunded"><?php _e( 'Refunded', $this->textdomain ); ?></td>
            <td colspan="<?php echo $this->colspan['right_right']; ?>" class="refunded align-right"><?php echo '-' . wc_price( $this->order->get_total_refunded(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
        </tr>
        <?php } ?>
        </tbody>
    </table>