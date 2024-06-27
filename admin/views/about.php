<?php
	$plugin_main_file = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/custom-404-pro/custom-404-pro.php';
	$plugin_data      = get_plugin_data( $plugin_main_file );
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Custom 404 Pro Info', 'custom-404-pro' ); ?></h2><br>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="postbox">
					<h3 class="hndle"><span><?php esc_html_e( 'About the Author', 'custom-404-pro' ); ?></span></h3>
					<div class="inside">
						<div class="c4p-clearfix">
							<div class="c4p-left">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . 'me.png'; ?>" class="c4p-author-image"/>
							</div>
							<div class="c4p-left" style="width: 70%">
								<p><?php esc_html_e( 'Hi.', 'custom-404-pro' ); ?></p>
								<p><?php printf(esc_html__( 'My name is %1$sKunal Nagar%2$s and I\'m a Front-End Web Developer.', 'custom-404-pro' ),'<b>','</b>'); ?></p>
								<p><?php printf(esc_html__( 'For more info and résumé, you can visit my %1$swebsite%2$s.', 'custom-404-pro' ),'<a href="https://www.kunalnagar.in" target="_blank" rel="noopener noreferrer">','</a>'); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle"><?php esc_html_e( 'Like the plugin?', 'custom-404-pro' ); ?></h3>
					<div class="inside">
						<div class="misc-pub-section">
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_donations"/>
								<input type="hidden" name="business" value="knlnagar@gmail.com">
								<input type="hidden" name="item_name" value="<?php esc_attr_e( 'Custom 404 Pro is awesome!', 'custom-404-pro' ); ?>">
								<input type="hidden" name="currency_code" value="<?php esc_attr_e( 'USD', 'custom-404-pro' ); ?>"/>
								<input type="hidden" name="first_name" value="Kunal">
								<input type="hidden" name="last_name" value="Nagar">
								<input type="hidden" name="email" value="knlnagar@gmail.com">
								<input type="image" name="submit" border="0"
									   src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"
									   alt="<?php esc_attr_e( 'PayPal - The safer, easier way to pay online', 'custom-404-pro' ); ?>">
							</form>
						</div>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle"><?php esc_html_e( 'Plugin Info', 'custom-404-pro' ); ?></h3>
					<div class="inside">
						<div class="misc-pub-section">
							<label><?php esc_html_e( 'Name:', 'custom-404-pro' ); ?></label>
							<span>
							<b><?php echo $plugin_data['Title']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label><?php esc_html_e( 'Version:', 'custom-404-pro' ); ?></label>
							<span>
							<b><?php echo $plugin_data['Version']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label><?php esc_html_e( 'Author:', 'custom-404-pro' ); ?></label>
							<span>
							<b><?php echo $plugin_data['Author']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label><?php esc_html_e( 'Email:', 'custom-404-pro' ); ?></label>
							<span>
							<b><a href="mailto:knlnagar@gmail.com">knlnagar@gmail.com</a></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label><?php esc_html_e( 'Need help?', 'custom-404-pro' ); ?></label>
							<span>
							<b><a href="https://github.com/kunalnagar/custom-404-pro/issues"><?php esc_html_e( 'Create an Issue', 'custom-404-pro' ); ?></a></b>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
