<table class="company two-column" style="overflow: hidden;">
    <tr>
        <td class="logo" width="50%"><?php $this->get_company_logo_html(); ?></td>
        <td class="info small-font" width="50%">
            <?php echo nl2br( $this->template_options['bewpi_company_address'] ); ?><br/>
            <?php echo nl2br( $this->template_options['bewpi_company_details'] ); ?>
        </td>
    </tr>
</table>