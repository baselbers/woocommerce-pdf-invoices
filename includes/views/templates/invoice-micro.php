<html>
<head>
    <style>
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
            width: 60%;
            vertical-align: middle;
            margin-bottom: 40px;
            display: inline-block;
        }
        .company .info {
            padding-left: 30px;
            text-align: left;
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
        .date, .thanks {
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
            color: <?php echo $this->template_settings['color_theme']; ?>;
        }
        .foot td.border {
            border-bottom: 8px solid <?php echo $this->template_settings['color_theme']; ?>;
        }
        /* End change colors */
        .space td {
            padding-bottom: 50px;
        }
    </style>
</head>
<body>
<div id="container">
    <table class="company">
        <tbody>
        <tr>
            <td class="logo">
                <?php if( $this->template_settings['company_logo'] != "" ) { ?>
                    <img class="company-logo" src="<?php echo $this->template_settings['company_logo']; ?>" alt="Company logo"/>
                <?php } else { ?>
                    <h1 class="company-logo"><?php echo $this->template_settings['company_name']; ?></h1>
                <?php } ?>
            </td>
            <td class="info">
                <?php echo nl2br( $this->template_settings['company_address'] ); ?>
            </td>
        </tr>
        </tbody>
    </table>
    <table class="two-column customer">
        <tbody>
        <tr>
            <td></td>
            <td class="address">
                <?php echo $this->order->get_formatted_billing_address(); ?>
            </td>
        </tr>
        </tbody>
    </table>
    <table class="invoice-head">
        <tbody>
        <tr>
            <td class="invoice-details">
                <h1 class="title"><?php _e( 'Invoice', $this->textdomain ); ?></h1>
                <span class="number"># <?php echo $this->get_formatted_invoice_number(); ?></span><br/>
                <span class="date"><?php echo $this->get_formatted_date(); ?></span>
            </td>
            <td class="total-amount" bgcolor="<?php echo $this->template_settings['color_theme']; ?>">
					<span>
					<h1 class="amount"><?php echo wc_price( $this->order->get_total() ); ?></h1>
					<p class="thanks">
                        <?php echo $this->template_settings['intro_text']; ?>
                    </p>
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
            if( $this->template_settings['show_sku'] ) {
                $colspan = 3;
                echo '<th class="align-left">' . __( "SKU", $this->textdomain ) . '</th>';
            } else {
                $colspan = 2; }
            ?>
            <th class="align-left"><?php _e( 'Quantity', $this->textdomain ); ?></th>
            <th class="align-left"><?php _e( 'Unit price', $this->textdomain ); ?></th>
            <th class="align-right"><?php _e( 'Total', $this->textdomain ); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach( $this->order->get_items( 'line_item' ) as $item ) {
            $product = wc_get_product( $item['product_id'] ); ?>
            <tr class="product">
                <td><?php echo $product->get_title(); ?></td>
                <?php if( $this->template_settings['show_sku'] ) { ?>
                    <td><?php echo $product->get_sku(); ?></td>
                <?php } ?>
                <td><?php echo $item['qty']; ?></td>
                <td><?php echo wc_price( $product->get_price_excluding_tax() ); ?></td>
                <td class="align-right"><?php echo wc_price( $product->get_price_excluding_tax( $item['qty'] ) ); ?></td>
            </tr>
        <?php } ?>
        <!-- Space -->
        <tr class="space">
            <td colspan="<?php echo $colspan; ?>"></td>
            <td colspan="2"></td>
        </tr>
        <!-- Discount -->
        <?php if( $this->template_settings['show_discount'] && $this->order->get_total_discount != 0 ) { ?>
            <tr class="discount after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Discount', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_total_discount() ); ?></td>
            </tr>
        <?php } ?>
        <!-- Shipping -->
        <?php if( $this->template_settings['show_shipping'] ) { ?>
            <tr class="shipping after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Shipping', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_total_shipping() ); ?></td>
            </tr>
        <?php } ?>
        <!-- Subtotal -->
        <?php if( $this->template_settings['show_subtotal'] ) { ?>
            <tr class="subtotal after-products">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Subtotal', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_subtotal() ); ?></td>
            </tr>
        <?php } ?>
        <!-- Tax -->
        <?php if( $this->template_settings['show_tax'] ) { ?>
            <tr class="tax">
                <td colspan="<?php echo $colspan; ?>"></td>
                <td width="25%"><?php _e( 'Tax', $this->textdomain ); ?></td>
                <td width="25%" class="align-right"><?php echo wc_price( $this->order->get_total_tax() ); ?></td>
            </tr>
        <?php } ?>
        <!-- Total -->
        <tr>
            <td colspan="<?php echo $colspan; ?>"></td>
            <td class="total" width="25%"><?php _e( 'Total', $this->textdomain ); ?></td>
            <td class="grand-total align-right" width="25%">
                <?php echo wc_price( $this->order->get_total() ) ?>
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