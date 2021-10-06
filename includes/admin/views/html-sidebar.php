<aside class="bewpi-sidebar premium">
	<h3><?php _e( 'Invoices for WooCommerce Premium', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php _e( 'This plugin offers a premium version which comes with the following features:', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Attach PDF invoices to many more email types including third party plugins.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Send credit notes and cancelled PDF invoices when refunding or cancelling order.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Fully customize the table content by modifying line item columns and total rows.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Automatically send PDF invoices as a reminder configurable within a specific period of time.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Let customers decide if they would like to get a PDF invoice on checkout.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Change the font of the PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>

		- <?php _e( 'Generate PDF invoices in multiple languages (WPML and Polylang compatible).', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Bulk generate PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Bulk export and/or download PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Add additional PDF\'s to PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Send PDF invoices to multiple recipients.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php printf( __( 'Attach invoices to <a href="%s">WooCommerce Subscriptions</a> emails.', 'woocommerce-pdf-invoices' ), "http://www.woothemes.com/products/woocommerce-subscriptions/" ); ?><br/>
	</p>
	<a class="bewpi-learn-more" href="http://wcpdfinvoices.com" target="_blank"><?php _e ( 'Learn more', 'woocommerce-pdf-invoices' ); ?></a>
</aside>
<!--<aside class="bewpi-sidebar premium">
	<h3><?php _e( 'Stay up-to-date', 'woocommerce-pdf-invoices' ); ?></h3>
	<link href="//cdn-images.mailchimp.com/embedcode/slim-081711.css" rel="stylesheet" type="text/css">
	<p>
		<?php _e( 'We\'re constantly developing new features, stay up-to-date by subscribing to our newsletter.', 'woocommerce-pdf-invoices' ); ?>
	</p>
	<div id="bewpi-mc-embed-signup">
		<form action="//wcpdfinvoices.us11.list-manage.com/subscribe/post?u=f270649bc41a9687a38a8977f&amp;id=395e1e319a" method="post" id="bewpi-mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate style="padding: 0">
			<div id="bewpi-mc-embed-signup-scroll">
				<?php $user_email = get_the_author_meta( 'user_email', get_current_user_id() ) ?>
				<input type="email" value="<?php if( $user_email !== "" ) echo $user_email; ?>" name="EMAIL" class="email" id="bewpi-mce-EMAIL" placeholder="<?php _e( 'Your email address', 'woocommerce-pdf-invoices' ); ?>" required>
				<div style="position: absolute; left: -5000px;">
					<input type="text" name="b_f270649bc41a9687a38a8977f_395e1e319a" tabindex="-1" value="">
				</div>
				<div class="clear">
					<input style="" type="submit" value="<?php _e( 'Signup', 'woocommerce-pdf-invoices' ); ?>" name="subscribe" id="bewpi-mc-embedded-subscribe" class="button">
				</div>
				<div class="bewpi-no-spam">
					<?php _e( 'No spam, ever. Unsubscribe at any time', 'woocommerce-pdf-invoices' ); ?>
				</div>
			</div>
		</form>
	</div>
</aside>-->

<aside class="bewpi-sidebar about">
	<h3><?php _e( 'About', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php _e( 'This plugin is an open source project wich aims to fill the invoicing gap of <a href="http://www.woothemes.com/woocommerce">WooCommerce</a>.' , 'woocommerce-pdf-invoices' ); ?>
	</p>
	<?php
	echo '<b>' . sprintf( __( 'Version: %s', 'woocommerce-pdf-invoices' ), WPI_VERSION ) . '</b>';
	printf( '<br>' );
	echo '<b>' . sprintf( __( 'Author: %s', 'woocommerce-pdf-invoices' ), '<a href="https://github.com/baselbers">Bas Elbers</a>' ) . '</b>';
	?>
</aside>
<aside class="bewpi-sidebar support">
	<h3><?php _e( 'Support', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php printf( __( 'We will never ask for donations, but to guarantee future development, we do need your support. Please show us your appreciation by leaving a <a href="%1$s">★★★★★</a> rating and vote for <a href="%2$s">works</a>.', 'woocommerce-pdf-invoices' ), 'https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform', 'https://wordpress.org/plugins/woocommerce-pdf-invoices/' ); ?>
	</p>
	<!-- FB share -->
	<!--<div id="fb-root"></div>
	<div class="btn">
		<script>
			(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/<?php echo get_locale(); ?>/sdk.js#xfbml=1&version=v2.9";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		</script>
		<div class="fb-share-button" data-href="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-layout="button_count" data-size="small" data-mobile-iframe="true">
			<a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse"></a>
		</div>
	</div>
	<div class="twitter btn">
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-text="<?php _e( 'Checkout this amazing free Invoices for WooCommerce plugin for WordPress!', 'woocommerce-pdf-invoices' ); ?>">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	</div>-->
</aside>
<aside class="bewpi-sidebar need-help">
	<h3><?php _e( 'Need Help?', 'woocommerce-pdf-invoices' ); ?></h3>
	<ul>
		<li><a href="https://wordpress.org/plugins/woocommerce-pdf-invoices/faq/"><?php _e( 'Frequently Asked Questions', 'woocommerce-pdf-invoices' ); ?> </a></li>
		<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Support forum', 'woocommerce-pdf-invoices' ); ?></a></li>
		<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Request a feature', 'woocommerce-pdf-invoices' ); ?></a></li>
	</ul>
</aside>