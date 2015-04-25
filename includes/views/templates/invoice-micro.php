<html>
<head>
    <style>
        div, table, td, tr, tfoot, tbody, thead, span, h1, h2, h3, h4 {
            /*border: 1px solid black;*/
        }
        h1.company-logo {
            font-size: 30px;
        }
        img.company-logo {
            max-height: 150px;
        }
        table {
            border-collapse: collapse;
            font-size: 14px;
            width: 100%;
            color: #757575;
        }
        table.products th {
            border-bottom: 2px solid #A5A5A5;
        }
        table.products td{
            vertical-align: top;
        }
        table.products td, table.products th {
            padding: 5px;
        }
        tr.product-row td {
            border-bottom: 1px solid #CECECE;
        }
        td.total, td.grand-total {
            border-top: 4px solid #8D8D8D;
            padding-bottom: 0; margin-bottom: 0;
        }
        td.grand-total {
            font-size: 16px !important;
        }
        table, tbody, h1 {
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 36px;
        }
        span {
            display: block;
            width: 100%;
        }
        .align-left { text-align: left; }
        .align-right { text-align: right; }
        .company {
            margin-bottom: 40px;
        }
        .company .logo {
            text-align: left;
        }
        .company .info {
            text-align: left;
            vertical-align: middle;
        }
        .two-column {
            margin-bottom: 40px;
            width: 100%;
        }
        .two-column td {
            text-align: left;
            vertical-align: top;
            width: 50%;
        }
        .invoice-head {
            margin-bottom: 20px;
            margin-right: -64px;
        }
        .invoice-head td {
            text-align: left;
        }
        .invoice-head .title {
            color: #525252;
        }
        td.invoice-details {
            vertical-align: top;
        }
        .number {
            font-size: 16px;
        }
        .small-font {
            font-size: 12px;
        }
        .total-amount p {
            margin: 0; padding: 0;
        }
        .total-amount {
            padding: 20px 20px 30px 20px;
            width: 54%;
        }
        .total-amount, .total-amount h1 {
            color: white;
        }
        div.item-attribute {
            font-size: 12px;
            margin-top: 10px;
        }
        .invoice {
            margin-bottom: 20px;
        }
        .foot {
            margin: 0 -64px;
        }
        .foot td.border {
            padding: 20px 40px;
            width: 100%;
            text-align: center;
        }
        td.company-details, td.payment {
            padding: 20px 40px 40px 40px;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }
        .foot td {
            border: 1px solid white;
        }
        .refunded {
            color: #a00 !important;
        }
        .number, .grand-total {
            color: <?php echo $this->template_options['bewpi_color_theme']; ?>;
        }
        .total-without-refund {
            color: #757575 !important;
        }
        .foot td.border {
            border-bottom: 8px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;
        }
        /* End change colors */
        .space td {
            padding-bottom: 50px;
        }
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
        tr.product-row td:nth-child(1) { /* Description td */
            width: <?php echo $this->desc_cell_width; ?>;
        }
        table.products {
            table-layout: fixed;
        }
        table.products td {
            overflow: hidden;
        }
    </style>
</head>
<body>
<div id="container">
    <table class="company two-column">
        <tbody>
        <tr>
            <td class="logo">
                <?php if ( !empty( $this->template_options['bewpi_company_logo'] ) ) { ?>
                    <img class="company-logo" src="<?php echo $this->template_options['bewpi_company_logo']; ?>"/>
                <?php } else { ?>
                    <h1 class="company-logo"><?php echo $this->template_options['bewpi_company_name']; ?></h1>
                <?php } ?>
            </td>
            <td class="info">
                <?php echo nl2br( $this->template_options['bewpi_company_address'] ); ?>
            </td>
        </tr>
        </tbody>
    </table>
    <table class="two-column customer">
        <tbody>
        <tr>
            <td class="address small-font">
                <b><?php _e( 'Invoice to', $this->textdomain ); ?></b><br/>
                <?php echo $this->order->get_formatted_billing_address(); ?>
            </td>
            <td class="address small-font">
                <b><?php _e( 'Ship to', $this->textdomain ); ?></b><br/>
                <?php echo $this->order->get_formatted_shipping_address(); ?>
            </td>
        </tr>
        </tbody>
    </table>
    <table class="invoice-head">
        <tbody>
        <tr>
            <td class="invoice-details">
                <h1 class="title"><?php _e( 'Invoice', $this->textdomain ); ?></h1>
                <span class="number"><?php echo $this->get_formatted_number(); ?></span><br/>
                <span class="small-font"><?php echo $this->get_formatted_invoice_date(); ?></span><br/><br/>
                <span class="small-font"><?php printf( __( 'Order Number %s', $this->textdomain ), $this->order->get_order_number() ); ?></span><br/>
                <span class="small-font"><?php printf( __( 'Order Date %s', $this->textdomain ), $this->get_formatted_order_date() ); ?></span><br/>
            </td>
            <td class="total-amount" bgcolor="<?php echo $this->template_options['bewpi_color_theme']; ?>">
				<span>
					<h1 class="amount"><?php echo wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_order_currency() ) ); ?></h1>
					<p class="small-font"><?php echo $this->template_options['bewpi_intro_text']; ?></p>
				</span>
            </td>
        </tr>
        </tbody>
    </table>
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
				        <?php printf( __( 'VAT %s', true, $this->textdomain ), WC_Tax::get_rate_percent( $tax->rate_id ) ); ?>
			        </td>
			        <td colspan="<?php echo $this->colspan['right_right']; ?>" class="align-right"><?php echo $tax->formatted_amount; ?></td>
		        </tr>
	        <?php endforeach; ?>
        <?php } ?>
        <!-- Total -->
        <tr class="after-products">
            <td colspan="<?php echo $this->colspan['left']; ?>"></td>
            <td colspan="<?php echo $this->colspan['right_left']; ?>" class="total"><?php _e( 'Total', $this->textdomain ); ?></td>
            <td colspan="<?php echo $this->colspan['right_right']; ?>" class="grand-total align-right"><?php echo $this->get_total(); ?></td>
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
</div>
</body>
</html>