<table class="foot small-font">
    <tbody>
    <tr>
        <td class="border" colspan="2" style="border-bottom: 8px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;">
            <?php echo $this->template_options['bewpi_terms']; ?><br/>
            <?php
            if ( $this->template_options['bewpi_show_customer_notes'] && $this->order->post->post_excerpt != "" ) :
                // Note added by customer.
                echo '<p><strong>' . __( 'Customer note', $this->textdomain ) . '</strong> ' . $this->order->post->post_excerpt . '</p>';
                // Notes added administrator on order details page.
                $customer_order_notes = $this->order->get_customer_order_notes();
                if ( count( $customer_order_notes ) > 0 ) {
                    echo '<p><strong>' . __('Customer note', $this->textdomain) . '</strong>' . $customer_order_notes[0]->comment_content . '</p>';
                }
            endif;
            ?>
        </td>
    </tr>
    <tr>
        <td class="company-details"><p><?php echo nl2br( $this->template_options['bewpi_company_details'] ); ?></p></td>
        <td class="payment"><p><?php printf( __( '%sPayment%s via', $this->textdomain ), '<b>', '</b>' ); ?>  <?php echo $this->order->payment_method_title; ?></p></td>
    </tr>
    </tbody>
</table>