<table class="company two-column" style="overflow: hidden;">
    <tr>
        <td class="logo" width="50%">
            <?php BEWPI()->templater()->print_logo(); ?>
        </td>
        <td class="info small-font" width="50%">
            <p><?php echo nl2br( BEWPI()->templater()->get_option( 'bewpi_company_address', $this->order->id ) ); ?></p>
            <p><?php echo nl2br( BEWPI()->templater()->get_option( 'bewpi_company_details', $this->order->id ) ); ?></p>
        </td>
    </tr>
</table>