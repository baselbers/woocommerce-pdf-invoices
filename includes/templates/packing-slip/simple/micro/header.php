<div class="header">
	<table class="company">
		<tr>
			<td class="logo">
				<?php
				if ( WPI()->get_option( 'template', 'company_logo' ) ) {
					printf( '<img class="company-logo" src="var:company_logo" style="max-height:150px;"/>' );
				} else {
					printf( '<h1 class="company-logo">%s</h1>', esc_html( WPI()->get_option( 'template', 'company_name' ) ) );
				}
				?>
			</td>
			<td class="address">
				<?php
				echo WPI()->get_formatted_company_address();
				echo WPI()->get_formatted_company_details();
				?>
			</td>
		</tr>
	</table>
</div>
