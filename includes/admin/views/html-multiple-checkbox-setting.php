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

$page_options = get_option( $args['page'] );
$options      = $page_options[ $args['name'] ];
?>
<ul id="<?php echo esc_html( $args['id'] ); ?>">
	<?php
	foreach ( $args['options'] as $arg ) {
		$name    = sprintf( '%1$s[%2$s][]', $args['page'], $args['name'] );
		$checked = in_array( $args['value'], $options, true );
		?>
		<li>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="0" />
			<input id="<?php echo $arg['value']; ?>" type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" <?php checked( $options[ $arg['value'] ], 1 ); ?> />
			<label for="<?php echo $arg['value']; ?>"">
				<?php echo esc_html( $arg['name'] ); ?>
			</label>
		</li>
	<?php } ?>
</ul>
<div class="bewpi-notes">
	<?php echo $args['desc']; ?>
</div>
