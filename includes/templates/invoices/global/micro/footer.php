<table class="foot">
	<tbody>
	<tr>
		<td class="company-details" style="border-top: 4px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;">
			<p>
				<?php echo nl2br( $this->template_options[ 'bewpi_left_footer_column' ] ); ?>
			</p>
		</td>
		<td class="payment" style="border-top: 4px solid <?php echo $this->template_options['bewpi_color_theme']; ?>;">
			<p>
				<?php
				if ( $this->template_options[ 'bewpi_right_footer_column' ] !== "" ) {
					echo nl2br( $this->template_options['bewpi_left_footer_column'] );
				} else {
					printf( __( '%s of %s', $this->textdomain ), '{PAGENO}', '{nbpg}' );
				}
				?>
			</p>
		</td>
	</tr>
	</tbody>
</table>