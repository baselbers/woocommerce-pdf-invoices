<style>
    /* Page template CSS */
    #post-2 header, #post-2 footer {
        display: none;
    }
    h4 {
        color: #32373A;
        border-bottom: 2px solid #E4E7E9;
        padding: 0 0 3px 0; margin: 0;
        text-transform: uppercase;
    }
    p {
        margin: 8px 0 0 0; padding: 0 ;
    }
    /* Invoice template 1 CSS */
    #container {
        min-height: 100%;
        position: relative;
        color: #32373A;
    }
    #body {
        padding: 40px 40px 0;
    }
    .row {
        width: 100%;
        margin-bottom: 30px;
    }
    .logo {
        width: 100%;
        display: table;
    }
    .logo-wrapper {
        display: table-cell;
        text-align: center;
        vertical-align: middle;
        font-size: 18px;
    }
    .company-logo {
        max-height: 150px;
    }
    .coupon, .title, #invoice-number {
        text-align: center;
    }
    .intro {
        font-size: 16px;
        text-align: center;
    }
    #expires {
        font-size: 12px;
    }
    .coupon {
        padding-left: 40px;
        padding-right: 40px;
        background-color: #F8F8F8;
        color: #32373B;
    }
    #coupon-code {
        margin: 0 auto;
        border: 1px dashed #F8F8F8;
        padding: 10px;
        background-color: #52AF68;
        color: white;
        font-weight: bold;
    }
    .title {
        margin: 0;
        padding: 20px;
        color: white;
        background-color: #52AF68;
    }
    .title h1, .title span {
        margin: 0; padding: 0;
    }
    #invoice-number {
        padding: 20px;
        background-color: #387747;
        color: white;
        font-size: 14px;
    }
    table.products {
        padding: 20px 20px;
        width: 100%;
        background-color: #F8F8F8;
        /*margin-bottom: 20px;*/
    }
    td, th {
        padding: 10px;
        text-align: right;
        font-weight: bold;
        vertical-align: middle;
        font-size: 14px;
        text-transform: uppercase;
    }
    tr.border-bottom td, td.border-bottom {
        border-bottom: 1px solid #E4E7E9;
    }
    tr.border-bottom th {
        border-bottom: 3px solid #E4E7E9;
    }
    .align-left { text-align: left; }
    .align-center { text-align: center; }
    .align-right { text-align: right; }
    .normalcase { text-transform: none; }
    .uppercase { text-transform: uppercase; }
    .circle { border-radius: 50%; }
    .two-column { width: 40%; text-transform: uppercase; padding: 20px 30px; background-color: #F8F8F8; }
    .one-column { width: 100%; padding: 20px 30px; background-color: #F8F8F8; }
    .left { float: left;  text-align: left; }
    .right { float: right; text-align: right; }
    .border-bottom-non-product { border-bottom: 2px solid #E4E7E9; }
    .product td {
        font-weight: normal;
    }
    .total {
        border-bottom: 3px solid #E4E7E9;
    }
    .discount td, .subtotal td, .tax td, .shipping td, .total td {
        font-weight: bold;
    }
    .payment-method{
        font-size: 12px;
        font-weight: normal;
    }
    .payment-method {
        vertical-align: middle;
    }
    #footer {
        width: 100%;
        font-size: 14px;
        background-color: #3A3F43;
        color: white;
        padding: 40px;
    }
    .questions {
        float: left;
        width: 40%;
    }
    .company-address {
        float: right;
        text-align: right;
        width: 35%;
    }
</style>
<div id="container">
    <div id="body">
        <div class="row logo">
            <div class="logo-wrapper">
                <!--<div id="company-logo" class="circle uppercase"><span>My company</span></div>-->
                <?php if( $this->template_settings['company_logo'] != "" ) { ?>
                    <img class="company-logo" src="<?php echo $this->template_settings['company_logo']; ?>" alt="Company logo"/>
                <?php } else { ?>
                    <div class="company-logo"><?php echo $this->template_settings['company_name']; ?></div>
                <?php } ?>
            </div>
        </div>
        <div class="row intro">
            <?php echo $this->template_settings['intro_text']; ?>
        </div>
        <div class="row">
            <div class="two-column left">
                <h4>Billing address</h4>
                <p class="normalcase">
                    <?php echo $this->order->get_formatted_billing_address(); ?>
                </p>
            </div>
            <div class="two-column right">
                <h4>Shipping address</h4>
                <p class="normalcase">
                    <?php echo $this->order->get_formatted_shipping_address(); ?>
                </p>
            </div>
        </div>
        <!-- COUPON -->
        <!--<div class="row coupon">
        <h3>20% off next purchase</h3>
        <p>
            For being a regular customer, here's a little something from <br/>
            us. Use the coupon code to get 20% off your next order!
        </p>
        <div id="coupon-code">
            c0up0n_c0d3
        </div>
        <p id="expires">
        <strong>Expires on: 1st January 2015</strong>
        </p>
        </div>-->
        <div class="row invoice">
            <div class="title">
                <h1>Your Invoice</h1>
                <span id="invoice-date" class="uppercase"><?php echo $this->get_formatted_date(); ?></span>
            </div>
            <div id="invoice-number">
                Invoice Number: <?php echo $this->get_formatted_invoice_number(); ?>
            </div>
            <table class="products">
                <thead>
                <tr class="border-bottom">
                    <th class="align-left">Description</th>
                    <?php if( $this->template_settings['show_sku'] ) { $colspan = 3; ?>
                        <th class="align-center uppercase">SKU</th>
                    <?php } else { $colspan = 2; } ?>
                    <th class="align-center">Quantity</th>
                    <th>Unit price</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach( $this->order->get_items( 'line_item' ) as $item ) {
                    $product = wc_get_product( $item['product_id'] ); ?>
                    <tr class="product border-bottom">
                        <td class="align-left normalcase"><?php echo $product->get_title(); ?></td>
                        <?php if( $this->template_settings['show_sku'] ) { ?>
                            <td class="align-center uppercase"><?php echo $product->get_sku(); ?></td>
                        <?php } ?>
                        <td class="align-center"><?php echo $item['qty']; ?></td>
                        <td><?php echo wc_price( $product->get_price_excluding_tax() ); ?></td>
                        <td><?php echo wc_price( $product->get_price_excluding_tax( $item['qty'] ) ); ?></td>
                    </tr>
                <?php } ?>
                <?php if( $this->template_settings['show_discount'] && $this->order->get_total_discount != 0 ) { ?>
                    <tr class="discount">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom-non-product">Discount</td>
                        <td class="border-bottom-non-product"><?php echo wc_price( $this->order->get_total_discount() ); ?></td>
                    </tr>
                <?php } ?>
                <?php if( $this->template_settings['show_shipping'] ) { ?>
                    <tr class="shipping">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom-non-product">Shipping</td>
                        <td class="border-bottom-non-product normalcase"><?php echo wc_price( $this->order->get_total_shipping() ); ?></td>
                    </tr>
                <?php } ?>
                <?php if( $this->template_settings['show_subtotal'] ) { ?>
                    <tr class="subtotal">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom-non-product">Subtotal</td>
                        <td class="border-bottom-non-product"><?php echo wc_price( $this->order->get_subtotal() ); ?></td>
                    </tr>
                <?php } ?>
                <?php if( $this->template_settings['show_tax'] ) { ?>
                    <tr class="tax">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom-non-product">Tax</td>
                        <td class="border-bottom-non-product"><?php echo wc_price( $this->order->get_total_tax() ); ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td class="payment-method align-left normalcase" colspan="<?php echo $colspan; ?>">Payment via <?php echo $this->order->payment_method_title; ?></td>
                    <td class="total">Total</td>
                    <td class="total"><?php echo wc_price( $this->order->get_total() ); ?></td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php if( count($this->order->get_customer_order_notes()) > 0 ) { ?>
        <div class="row one-column align-center order-notes">
            <h4>Order notes</h4>
            <p>
                <?php
                foreach( $this->order->get_customer_order_notes() as $note ) {
                    echo $note->comment_content . "<br/>";
                }
                ?>
            </p>
        </div>
        <?php } ?>
    </div>
    <div id="footer">
        <div class="questions">
            <!--<span><strong>Questions?</strong></span>
            <p>
                No problem. You can get in touch with us on Facebook and Twitter and we'll get back to you as soon as we can.
            </p>-->
        </div>
        <div class="company-address normalcase">
            <?php echo nl2br( $this->template_settings['company_address'] ); ?>
            <?php echo nl2br( $this->template_settings['company_details'] ); ?>
        </div>
    </div>
</div>