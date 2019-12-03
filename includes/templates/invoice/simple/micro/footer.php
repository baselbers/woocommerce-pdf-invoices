<?php
$theme_color         = WPI()->get_option( 'template', 'color_theme' );
$is_theme_text_black = WPI()->get_option( 'template', 'theme_text_black' );
?>

<table class="foot border" style="border-top: 4px solid <?php echo $is_theme_text_black ? 'black' : esc_attr( $theme_color ); ?>;">
	<tr>
		<td class="left-footer-column">
			<p><?php echo nl2br( WPI()->get_option( 'template', 'left_footer_column' ) ); ?></p>
		</td>

		<td class="middle-footer-column">
			<p><?php printf( __( '%1$s of %2$s', 'woocommerce-pdf-invoices' ), '{PAGENO}', '{nbpg}' ); ?></p>
		</td>

		<td class="right-footer-column">
			<p><?php echo nl2br( WPI()->get_option( 'template', 'right_footer_column' ) ); ?></p>
		</td>
	</tr>
</table>