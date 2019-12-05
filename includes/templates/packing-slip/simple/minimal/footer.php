<?php
$theme_color         = $this->template_options['bewpi_color_theme'];
$is_theme_text_black = $this->template_options['bewpi_theme_text_black'];
?>
<div class="footer">
	<table class="foot border"
	       style="border-top: 4px solid <?php echo ( $is_theme_text_black ) ? 'black' : $theme_color; ?>;">
		<tr>
			<td class="left-footer-column">
				<p></p>
			</td>

			<td class="middle-footer-column">
				<p></p>
			</td>

			<td class="right-footer-column">
				<p><?php printf( __( '%1$s of %2$s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ); ?></p>
			</td>
		</tr>
	</table>
</div>
