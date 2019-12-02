<table class="company two-column">
	<tr>
		<td class="logo" width="50%">
			<?php
			if ( WPI()->get_option( 'template', 'company_logo' ) ) {
				printf( '<img class="company-logo" src="var:company_logo" style="max-height:100px;"/>' );
			} else {
				printf( '<h1 class="company-logo">%s</h1>', esc_html( WPI()->get_option( 'template', 'company_name' ) ) );
			}
			?>
		</td>
		<td class="info small-font" width="50%">
			<?php echo WPI()->get_formatted_company_address(); ?>
		</td>
	</tr>
</table>
