<?php
	$plugin_main_file = dirname( dirname( dirname( __DIR__ ) ) ) . '/custom-404-pro/custom-404-pro.php';
	$plugin_data      = get_plugin_data( $plugin_main_file );
?>

<div class="wrap">
	<h2>Custom 404 Pro Info</h2><br>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="postbox">
					<h3 class="hndle"><span>About the Author</span></h3>
					<div class="inside">
						<div class="c4p-clearfix">
							<div class="c4p-left">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . 'me.jpg'; ?>" class="c4p-author-image"/>
							</div>
							<div class="c4p-left" style="width: 70%">
								<p>Hi.</p>
								<p>My name is <b>Kunal Nagar</b> and I'm a Front-End Web Developer.</p>
								<p>For more info and résumé, you can visit my <a href="https://www.kunalnagar.in" target="_blank" rel="noopener noreferrer">website</a>.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle">Like the plugin?</h3>
					<div class="inside">
						<div class="misc-pub-section">
							<form action="https://www.paypal.com/donate" method="post" target="_top">
								<input type="hidden" name="business" value="YEKFFE2HSGHTJ" />
								<input type="hidden" name="no_recurring" value="0" />
								<input type="hidden" name="currency_code" value="CAD" />
								<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button" />
								<img alt="" border="0" src="https://www.paypal.com/en_CA/i/scr/pixel.gif" width="1" height="1" />
							</form>
						</div>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle ui-sortable-handle">Plugin Info</h3>
					<div class="inside">
						<div class="misc-pub-section">
							<label>Name:</label>
							<span>
							<b><?php echo $plugin_data['Title']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Version:</label>
							<span>
							<b><?php echo $plugin_data['Version']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Author:</label>
							<span>
							<b><?php echo $plugin_data['Author']; ?></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Email:</label>
							<span>
							<b><a href="mailto:hireme@kunalnagar.in">hireme@kunalnagar.in</a></b>
							</span>
						</div>
						<div class="misc-pub-section">
							<label>Need help?</label>
							<span>
							<b><a href="https://github.com/kunalnagar/custom-404-pro/issues">Create an Issue</a></b>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
