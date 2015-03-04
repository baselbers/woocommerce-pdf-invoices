<style>
    /* Page template CSS */
    #post-2 header, #post-2 footer {
        display: none;
    }
    /* Invoice template 1 CSS */
    #body {
        padding: 40px 40px 0;
        color: #5A5E61;
    }
    .row {
        width: 100%;
        margin-bottom: 20px;
    }
    .logo .left, .logo .right, .logo .center {
        float: left;
    }
    .logo .center {
        width: 45%;
        margin: 0 auto;
        border: 1px solid black;
    }
    .logo .left, .logo .right {
        width: 20%;
        border: 1px solid black;
    }
    .logo .right {
        float: right;
    }
    #company-logo {
        margin: 0 auto;
        height: 150px;
        line-height: 150px;
        width: 150px;
        font-size: 18px;
        background-color: #57C56F;
        color: white;
        text-align: center;
    }
    #company-logo span {
        vertical-align: middle;
        line-height: 14px;
        font-weight: bold;
    }
    .company-address {
        text-align: right;
        padding-right: 0;
        font-size: 16px;
    }
    .intro, .coupon, .title, #invoice-number {
        text-align: center;
    }
    .intro {
        font-size: 16px;
    }
    #expires, #invoice-number{
        font-size: 12px !important;
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
        font-size: 16px;
    }
    table {
        padding: 20px;
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
    tr.border-bottom td, tr.border-bottom th, td.border-bottom {
        border-bottom: 1px solid #E4E7E9;
    }
    .align-left { text-align: left; }
    .align-center { text-align: center; }
    .normalcase { text-transform: none; }
    .uppercase { text-transform: uppercase; }
    .circle { border-radius: 50%; }
    .product td {
        color: black;
    }
    .product td, .total {
        font-weight: bold;
        font-size: 16px;
    }
    .discount td, .subtotal td, .tax td, .shipping td {
        font-weight: normal;
        font-size: 14px;
    }
    .payment-method, .customer-note{
        font-size: 12px;
        font-weight: normal;
    }
    .payment-method {
        vertical-align: middle;
    }
    .customer-note {
        color: black;
        width: 100%;
        text-align: center;
        margin-top: 20px;
        padding: 10px;
        /*background-color: #E8E8E8;*/
    }
    #footer {
        width: 100%;
        font-size: 14px;
        background-color: #3A3F43;
    }
    .questions, .company-address  {
        padding: 40px;
        width: 40%;
        float: left;
        border: 1px solid black;
    }
    .questions p {
        margin: 0;
    }
</style>
    <div id="body" class="row">
        <div class="row logo">
            <div class="left"></div>
            <div class="center">
                <div id="company-logo" class="circle uppercase"><span>My company</span></div>
            </div>
            <div class="right">
                <?php //echo nl2br( $this->template_settings['company_address'] ); ?>
                <?php //echo nl2br( $this->template_settings['company_details'] ); ?>
            </div>
        </div>
        <div class="row intro">
            <!--<h1>Thank you!</h1>-->
            <p>
                Thanks for shopping with us today.<br/>
                You'll find your invoice below.
            </p>
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
                        <td class="border-bottom">Discount</td>
                        <td class="border-bottom"><?php echo wc_price( $this->order->get_total_discount() ); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if( $this->template_settings['show_shipping'] ) { ?>
                        <tr class="shipping">
                            <td colspan="<?php echo $colspan; ?>"></td>
                            <td class="border-bottom">Shipping</td>
                            <td class="border-bottom normalcase"><?php echo wc_price( $this->order->get_total_shipping() ); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if( $this->template_settings['show_subtotal'] ) { ?>
                    <tr class="subtotal">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom">Subtotal</td>
                        <td class="border-bottom"><?php echo wc_price( $this->order->get_subtotal() ); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if( $this->template_settings['show_tax'] ) { ?>
                    <tr class="tax">
                        <td colspan="<?php echo $colspan; ?>"></td>
                        <td class="border-bottom">Tax</td>
                        <td class="border-bottom"><?php echo wc_price( $this->order->get_total_tax() ); ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td class="payment-method align-left normalcase" colspan="<?php echo $colspan; ?>">Payment via <?php echo $this->order->payment_method_title; ?></td>
                        <td class="total border-bottom">Total</td>
                        <td class="total border-bottom"><?php echo wc_price( $this->order->get_total() ); ?></td>
                    </tr>
                </tbody>
            </table>
            <div class="customer-note">
                <?php echo $this->order->get_customer_order_notes()[0]->comment_content; ?>
            </div>
        </div>
<div id="footer">
    <div class="questions">
        <span><strong>Questions?</strong></span>
        <p>
            No problem. You can get in touch with us on Facebook and Twitter and we'll get back to you as soon as we can.
        </p>
    </div>
    <div class="company-address">
        <span><strong>Company</strong></span><br/>
        Street<br/>
        City<br/>
        Country
    </div>
</div>