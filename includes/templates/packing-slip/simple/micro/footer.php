<?php
$theme_color         = $this->template_options['bewpi_color_theme'];
$is_theme_text_black = $this->template_options['bewpi_theme_text_black'];
?>
<div class="footer">
	<table class="foot border">
		<tr>
			<td class="left-footer-column">

			</td>

			<td class="middle-footer-column">
				<p><?php printf( __( '%1$s of %2$s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ); ?></p>
			</td>

			<td class="right-footer-column">

			</td>
		</tr>
	</table>
</div>