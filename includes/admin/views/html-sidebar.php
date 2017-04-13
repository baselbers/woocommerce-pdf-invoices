<aside class="bewpi-sidebar premium">
	<h3><?php _e( 'WooCommerce PDF Invoices Premium', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php _e( 'This plugin offers a premium version which comes with the following features:', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Change the font of the PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Generate PDF invoices in multiple languages (WPML and Polylang compatible).', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Bulk generate PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Bulk export and/or download PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Bill periodically by generating and sending global invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Add additional PDF\'s to PDF invoices.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php _e( 'Send customer invoices directly to suppliers and others.', 'woocommerce-pdf-invoices' ); ?><br/>
		- <?php printf( __( 'Attach invoices to <a href="%s">WooCommerce Subscriptions</a> emails.', 'woocommerce-pdf-invoices' ), "http://www.woothemes.com/products/woocommerce-subscriptions/" ); ?><br/>
	</p>
	<a class="bewpi-learn-more" href="http://wcpdfinvoices.com" target="_blank"><?php _e ( 'Learn more', 'woocommerce-pdf-invoices' ); ?></a>
</aside>
<aside class="bewpi-sidebar premium">
	<h3><?php _e( 'Stay up-to-date', 'woocommerce-pdf-invoices' ); ?></h3>
	<!-- Begin MailChimp Signup Form -->
	<link href="//cdn-images.mailchimp.com/embedcode/slim-081711.css" rel="stylesheet" type="text/css">
	<p>
		<?php _e( 'We\'re constantly developing new features, stay up-to-date by subscribing to our newsletter.', 'woocommerce-pdf-invoices' ); ?>
	</p>
	<div id="bewpi-mc-embed-signup">
		<form action="//wcpdfinvoices.us11.list-manage.com/subscribe/post?u=f270649bc41a9687a38a8977f&amp;id=395e1e319a" method="post" id="bewpi-mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate style="padding: 0">
			<div id="bewpi-mc-embed-signup-scroll">
				<?php $user_email = get_the_author_meta( 'user_email', get_current_user_id() ) ?>
				<input type="email" value="<?php if( $user_email !== "" ) echo $user_email; ?>" name="EMAIL" class="email" id="bewpi-mce-EMAIL" placeholder="<?php _e( 'Your email address', 'woocommerce-pdf-invoices' ); ?>" required>
				<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
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
	<!--End mc_embed_signup-->
</aside>

<aside class="bewpi-sidebar about">
	<h3><?php _e( 'About', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php _e( 'This plugin is an open source project wich aims to fill the invoicing gap of <a href="http://www.woothemes.com/woocommerce">WooCommerce</a>.' , 'woocommerce-pdf-invoices' ); ?>
	</p>
	<?php _e( '<b>Version</b>: ' . WPI_VERSION, 'woocommerce-pdf-invoices' ); ?>
	<br/>
	<?php _e( '<b>Author</b>: <a href="https://github.com/baselbers">Bas Elbers</a>', 'woocommerce-pdf-invoices' ); ?>
</aside>
<aside class="bewpi-sidebar support">
	<h3><?php _e( 'Support', 'woocommerce-pdf-invoices' ); ?></h3>
	<p>
		<?php _e( 'We will never ask for donations, but to guarantee future development, we do need your support. Please show us your appreciation by leaving a <a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-pdf-invoices?rate=5#postform">★★★★★</a> rating and vote for <a href="https://wordpress.org/plugins/woocommerce-pdf-invoices/">works</a>.', 'woocommerce-pdf-invoices' ); ?>
	</p>
	<!-- Github star -->
	<div class="github btn">
		<iframe src="https://ghbtns.com/github-btn.html?user=baselbers&repo=woocommerce-pdf-invoices&type=star&count=true" frameborder="0" scrolling="0" width="170px" height="20px"></iframe>
	</div>
	<!-- FB share -->
	<div class="btn">
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) return;
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/<?php echo get_bloginfo( 'language' ); ?>/sdk.js#xfbml=1&version=v2.4&appId=483906578380615";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));</script>
		<div class="fb-share-button" data-href="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-layout="button_count"></div>
	</div>
	<!-- Tweet -->
	<div class="twitter btn">
		<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wordpress.org/plugins/woocommerce-pdf-invoices/" data-text="<?php _e( 'Checkout this amazing free WooCommerce PDF Invoices plugin for WordPress!', 'woocommerce-pdf-invoices' ); ?>">Tweet</a>
		<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
	</div>
</aside>
<aside class="bewpi-sidebar need-help">
	<h3><?php _e( 'Need Help?', 'woocommerce-pdf-invoices' ); ?></h3>
	<ul>
		<li><a href="https://wordpress.org/plugins/woocommerce-pdf-invoices/faq/"><?php _e( 'Frequently Asked Questions', 'woocommerce-pdf-invoices' ); ?> </a></li>
		<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Support forum', 'woocommerce-pdf-invoices' ); ?></a></li>
		<li><a href="https://wordpress.org/support/plugin/woocommerce-pdf-invoices"><?php _e( 'Request a feature', 'woocommerce-pdf-invoices' ); ?></a></li>
	</ul>
</aside>