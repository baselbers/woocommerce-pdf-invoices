<table class="foot border" style="border-top: 4px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;">
    <tr>
        <td class="company-details">
	        <p>
		        <?php echo nl2br( str_replace( '[payment_method]',
			        $this->order->payment_method_title,
			        $this->template_options[ 'bewpi_left_footer_column' ] ) ); ?>
	        </p>
        </td>
        <td class="payment">
	        <p>
		        <?php
		        if ( $this->template_options[ 'bewpi_right_footer_column' ] !== "" ) {
			        echo nl2br( str_replace( '[payment_method]',
				        $this->order->payment_method_title,
				        $this->template_options['bewpi_right_footer_column'] ) );
		        } else {
			        printf( __( '%s of %s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' );
		        }
		        ?>
	        </p>
        </td>
    </tr>
</table>