<style>
    /* Page template CSS */
    #post-2 header, #post-2 footer {
        display: none;
    }
    /* Invoice template 1 CSS */
    #body {
        padding: 40px 40px 60px;
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
        width: 55%;
        margin: 0 auto;
    }
    .logo .left, .logo .right {
        width: 15%;
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
        padding-right: 40px;
    }
    .intro, .coupon, .title, #invoice-number {
        text-align: center;
    }
    .intro {
        font-size: 18px;
    }
    #expires, #invoice-number{
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
    .title h1 {
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
        font-size: 12px;
    }
    .payment-method {
        font-size: 12px;
        font-weight: normal;
    }
    /*#footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        font-size: 14px;
    }
    .questions, .company-address  {
        padding: 40px;
    }
    .questions {
        width: 40%;
        float: left;
    }
    .questions p {
        margin: 0;
    }*/
</style>
    <div id="body">
        <div class="row logo">
            <div class="left"></div>
            <div class="center">
                <div id="company-logo" class="circle"><span>Your Logo</span></div>
            </div>
            <div class="company-address right">
                <span class="uppercase"><strong>Company</strong></span><br/>
                Street<br/>
                City<br/>
                Country
            </div>
        </div>
        <div class="row intro">
            <h1>Thank you!</h1>
            <p>
                Thanks for shopping with us today.<br/>
                You'll find your invoice below.
            </p>
        </div>
        <!-- COUPON -->
        <div class="row coupon">
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
        </div>
        <div class="row invoice">
            <div class="title">
                <h1>Your Invoice</h1>
                <span id="invoice-date" class="uppercase">25th dec 2014</span>
            </div>
            <div id="invoice-number">
                Invoice Number: 11342
            </div>
            <table class="products">
                <thead>
                    <tr class="border-bottom">
                        <th class="align-left">Description</th>
                        <th class="align-center">Quantity</th>
                        <th>Unit price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="product border-bottom">
                        <td class="align-left normalcase">Awesome Widget</td>
                        <td class="align-center">1</td>
                        <td>8.09</td>
                        <td>8.09</td>
                    </tr>
                    <tr class="discount">
                        <td colspan="2"></td>
                        <td class="border-bottom">Discount</td>
                        <td class="border-bottom">0.00</td>
                    </tr>
                    <tr class="subtotal">
                        <td colspan="2"></td>
                        <td class="border-bottom">Subtotal</td>
                        <td class="border-bottom">8.09</td>
                    </tr>
                    <tr class="tax">
                        <td colspan="2"></td>
                        <td class="border-bottom">Tax 21%</td>
                        <td class="border-bottom">1.70</td>
                    </tr>
                    <tr class="shipping">
                        <td colspan="2"></td>
                        <td class="border-bottom">Shipping</td>
                        <td class="border-bottom normalcase">Free Shipping</td>
                    </tr>
                    <tr>
                        <td class="payment-method align-left normalcase" colspan="2">Paid with Visa:<br/>**** **** **** 1234</td>
                        <td class="total border-bottom">Total</td>
                        <td class="total border-bottom">9.79</td>
                    </tr>
                </tbody>
            </table>
        </div>

<!--<div id="footer">
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
</div>-->