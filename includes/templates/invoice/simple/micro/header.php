<table class="company two-column">
	<tr>
		<td class="logo" width="50%">
			<?php
			if ( BEWPI()->templater()->get_logo_url() ) {
				printf( '<img class="company-logo" src="var:company_logo"/>' );
			} else {
				printf( '<h1 class="company-logo">%s</h1>', esc_html( BEWPI()->templater()->get_option( 'bewpi_company_name' ) ) );
			}
			?>
		</td>
		<td class="info small-font" width="50%">
			<p><?php echo nl2br( BEWPI()->templater()->get_option( 'bewpi_company_address' ) ); ?></p>
			<p><?php echo nl2br( BEWPI()->templater()->get_option( 'bewpi_company_details' ) ); ?></p>
		</td>
	</tr>
</table>
