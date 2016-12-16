<?php
/**
 * Multiple checkbox setting.
 *
 * Callback html for multiple checkbox setting.
 *
 * @author      Bas Elbers
 * @category    Admin
 * @package     BE_WooCommerce_PDF_Invoices/Admin
 * @version     1.0.0
 */

$options = get_option( $args['page'] ); ?>
<ul id="<?php echo esc_html( $args['id'] ); ?>">
	<?php
	foreach ( $args['options'] as $arg ) {
		$name = sprintf( '%1$s[%2$s]', $args['page'], $arg['value'] );
		?>
		<li>
			<label>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0"/>
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $options[ $arg['value'] ], 1 ); ?>/>
				<?php echo esc_html( $arg['name'] ); ?>
			</label>
		</li>
	<?php } ?>
</ul>
<div class="bewpi-notes">
	<?php echo esc_html( $args['desc'] ); ?>
</div>
