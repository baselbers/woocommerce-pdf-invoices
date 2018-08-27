<?php
/**
 * Rate notice
 *
 * Ask user to rate the plugin on wordpress.org
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

$current_user   = wp_get_current_user();
$user_firstname = ! empty( $current_user->user_firstname ) ? ' ' . $current_user->user_firstname : '';
$rate_url       = 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform';
$action         = 'dismiss_notice_rate';
?>
<style>
	.wpi.notice.is-dismissible button[type=button].notice-dismiss {
		display: none;
	}
</style>
<div class="wpi notice notice-success is-dismissible">
	<p>
		<?php
		printf( __( 'Hi%1$s! You\'re using %2$s for some time now and we would appreciate your %3$s rating. It will support future development big-time.', 'woocommerce-pdf-invoices' ), esc_html( $user_firstname ), '<b>WooCommerce PDF Invoices</b>', '<a href="' . esc_url( $rate_url ) . '" target="_blank">★★★★★</a>' );
		?>
	</p>
	<form action="" method="post">
		<input type="hidden" name="wpi_action" value="<?php echo esc_attr( $action ); ?>"/>
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( $action ) ); ?>"/>
		<button type="submit" class="notice-dismiss"><span
				class="screen-reader-text"><?php __( 'Dismiss this notice.' ); ?></span></button>
	</form>
</div>
