<html>
<head>
    <style>
        #container {
            border: 1px solid black;
            height: 100%;
            width: 100%;
        }
        #container table {
            width: 100%;
        }
        #container table tr, #container table td {
            border: 1px solid black;
        }
        #company-logo {
            width: 150px;
        }
        .item-thumb img {
            width: 21px !important;
            height: 21px !important;
        }
        .invoice-item .refunded{
            display: block;
        }
    </style>
</head>
<body>
<div id="container">

    <table id="head" class="row">
        <tbody>
        <tr>
            <td id="logo">
                <?php ( $invoice->template_settings['company_logo'] !== '' ) ? $invoice->get_company_logo() : $invoice->get_company_name();  ?>
            </td>
        </tr>
        </tbody>
    </table>

    <table id="order-info">
        <tbody>
        <tr>
            <td id="invoice-info">
                <span id="invoice-number">
                    <?php echo $invoice->get_formatted_number(); ?>
                </span>
                <span id="invoice-date">
                    <?php echo $invoice->get_formatted_date(); ?>
                </span>
            </td>
        </tr>
        <tr>
            <td id="address-company">
                <?php echo nl2br( $invoice->template_settings['company_address'] ); ?>
            </td>
            <td id="address-billing">
                <?php echo $order->get_formatted_billing_address(); ?>
            </td>
            <td id="address-shipping">
                <?php echo $order->get_formatted_shipping_address(); ?>
            </td>
        </tr>
        </tbody>
    </table>

    <table id="products">
        <thead> <!-- Header -->
        <tr>
            <th></th>
            <?php
            $item_table_headers = $invoice->get_item_table_headers();
            foreach( $item_table_headers as $key => $th ) :
                if( $th['show'] )
                    echo '<th id="' . strtolower($key) . '">' . $th['title'] . '</th>';
            endforeach;
            ?>
        </tr>
        </thead>
        <tbody> <!-- Body -->
        <?php foreach( $order->get_items( 'line_item' ) as $item_id => $item ) :
            $_product = wc_get_product( $item['product_id'] );
            include WPI_TEMPLATES_DIR . '../html-invoice-item.php';
        endforeach; ?>
        </tbody>
        <tfoot> <!-- Footer -->
        <?php
        $colspan = $invoice->get_colspan();
        foreach( $invoice->get_item_table_footers() as $key => $tf ) :
            if( $tf['show'] ) :
                if( $key === 'tax' && wc_tax_enabled() ) :
                    foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
                        <tr>
                            <td colspan="<?php echo $colspan; ?>"></td>
                            <td>
                                <?php
                                $wc_tax = new WC_Tax();
                                printf( __( 'Tax %s', true, $invoice->textdomain ), $wc_tax->get_rate_percent( $tax->rate_id ) );
                                ?>
                            </td>
                            <td><?php echo $tax->formatted_amount; ?></td>
                        </tr>
                    <?php endforeach;
                else : ?>
                    <tr>
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td><?php echo $tf['title']; ?></td>
                        <td>
                            <?php
                            if ( $key === 'total' ) {
                                echo $tf['amount'];
                            } else {
                                echo wc_price( $tf['amount'] );
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                endif;
            endif;
        endforeach;
        ?>
        </tfoot>
    </table>
</div>
</body>
</html>