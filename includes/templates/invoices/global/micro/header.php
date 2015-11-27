<table class="company two-column">
    <tbody>
    <tr>
	    <td class="logo">
		    <?php $this->get_company_logo_html(); ?>
	    </td>
	    <td class="info">
		    <?php echo nl2br( $this->template_options['bewpi_company_address'] ); ?><br/>
		    <?php echo nl2br( $this->template_options['bewpi_company_details'] ); ?>
	    </td>
    </tr>
    </tbody>
</table>