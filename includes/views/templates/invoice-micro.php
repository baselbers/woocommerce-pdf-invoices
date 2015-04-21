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
        tr.product td {
            border-bottom: 1px solid #CECECE;
        }
        td.total, td.grand-total {
            border-top: 4px solid #8D8D8D;
        }
        td.grand-total {
            font-size: 16px;
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
        .number, .grand-total {
            color: <?php echo $this->template_options['bewpi_color_theme']; ?>;
        }
        .foot td.border {
            border-bottom: 8px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;
        }
        /* End change colors */
        .space td {
            padding-bottom: 50px;
        }
    </style>
</head>
<body>
<div id="container">
    <table class="company two-column">
        <tbody>
        <tr>
            <td class="logo">
                <?php if( !empty( $this->template_options['bewpi_company_logo'] ) ) { ?>
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
            <td>
                <b><?php _e( 'Invoice to', $this->textdomain ); ?></b><br/>
                <?php echo $this->order->get_formatted_billing_address(); ?>
            </td>
            <td class="address">
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
    <table class="products">
        <thead>
        <tr>
            <th class="align-left"><?php _e( 'Description', $this->textdomain ); ?></th>
            <?php
            $colspan = $this->get_colspan();
            if( $this->template_options['bewpi_show_sku'] ) {
                echo '<th class="align-left">' . __( "SKU", $this->textdomain ) . '</th>';
            }
            ?>
            <th class="align-left"><?php _e( 'Quantity', $this->textdomain ); ?></th>
            <th class="align-left"><?php _e( 'Unit price', $this->textdomain ); ?></th>
	        <?php if ( $this->template_options['bewpi_show_tax'] ) {
		        echo '<th class="align-left">' . __( "Tax", $this->textdomain ) . '</th>';
	        } ?>
            <th class="align-right"><?php _e( 'Total', $this->textdomain ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $this->order->get_items( 'line_item' ) as $item_id => $item ) {
            $product = wc_get_product( $item['product_id'] ); ?>
            <tr class="product">
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
                                $meta['meta_key']   = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $_product ), $meta['meta_key'] );
                            }

                            echo '<div class="item-attribute"><span style="font-weight: bold;">' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ': </span>' . wp_kses_post( rawurldecode( $meta['meta_value'] ) ) . '</div>';
                        }
                    }
                    ?>
                </td>
	                <?php if( $this->template_options['bewpi_show_sku'] ) echo '<td>' . $product->get_sku() . '</td>'; ?>
                <td>
	                <?php echo $item['qty']; ?>
                </td>
                <td>
	                <?php echo wc_price( $this->order->get_item_total( $item, false, true ), array( 'currency' => $this->order->get_order_currency() ) ); ?>
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
				            <?php echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $this->order->get_order_currency() ) ); ?>
			            </td>

		                <?php
		            endforeach;
	            endif;
	            ?>
                <td class="align-right">
	                <?php echo wc_price( $item['line_total'], array( 'currency' => $this->order->get_order_currency() ) ); ?>
                </td>
            </tr>
        <?php } ?>
        <!-- Space -->
        <tr class="space">
            <td colspan="<?php echo $colspan; ?>"></td>
            <td colspan="2"></td>
        </tr>
        <!-- Discount -->
        <?php if( $this->template_options['bewpi_show_discount'] && $this->order->get_total_discount != 0 ) { ?>
            <tr class="discount after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Discount', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_total_discount(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Shipping -->
        <?php if( $this->template_options['bewpi_show_shipping'] ) { ?>
            <tr class="shipping after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Shipping', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_total_shipping(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Subtotal -->
        <?php if( $this->template_options['bewpi_show_subtotal'] ) { ?>
            <tr class="subtotal after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Subtotal', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_subtotal(), array( 'currency' => $this->order->get_order_currency() ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Tax -->
        <?php if( $this->template_options['bewpi_show_tax'] && wc_tax_enabled() ) {
	        foreach ( $this->order->get_tax_totals() as $code => $tax ) : ?>
		        <tr>
			        <td colspan="<?php echo $colspan; ?>"></td>
			        <td width="25%">
				        <?php printf( __( 'Tax %s', true, $this->textdomain ), WC_Tax::get_rate_percent( $tax->rate_id ) ); ?>
			        </td>
			        <td width="25%" class="align-right"><?php echo $tax->formatted_amount; ?></td>
		        </tr>
	        <?php endforeach; ?>
        <?php } ?>
        <!-- Total -->
        <tr>
            <td colspan="<?php echo $colspan; ?>"></td>
            <td class="total" width="25%"><?php _e( 'Total', $this->textdomain ); ?></td>
            <td class="grand-total align-right" width="25%">
                <?php echo wc_price( $this->order->get_total(), array( 'currency' => $this->order->get_order_currency() ) ); ?>
            </td>
        </tr>
        <?php /*<tr>
            <td colspan="<?php echo $colspan; ?>"></td>
            <td class="refunded" width="25%"><?php _e( 'Refunded', $this->textdomain ); ?></td>
            <td class="refunded-total align-right" width="25%">
                <?php echo wc_price( $this->order->get_total_refunded() ) ?>
            </td>
        </tr> */?>
        </tbody>
    </table>
</div>
</body>
</html>