<?php
$plugin_main_file = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/custom-404-pro.php';
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
								<img src="<?php echo plugin_dir_url( __FILE__ ) . 'me.png'; ?>"
								     class="c4p-author-image"/>
							</div>
							<div class="c4p-left" style="width: 70%">
								<p>
									Hi. My name is <b>Kunal Nagar</b> and I'm a Freelance Web Developer from <a
										href="http://en.wikipedia.org/wiki/Jaipur" target="blank">Jaipur, India</a>. I
									build high quality websites using Core PHP, WordPress, Laravel etc. I also work on
									Hybrid Mobile Applications using Ionic. You can find out more about the services I
									provide on my <a href="http://kunalnagar.in" target="blank">website</a>.
								</p>
								<p>
									This plugin is my second attempt at maintaining an Open Source project. I recently
									learned about the Git Workflow system and I wonder how I ever did Software
									Development without it. The Git Workflow doesn't necessarily apply to teams; you can
									use it to enhance your development skills so that it is easy to maintain large and
									complex projects.
								</p>
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
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
								<input type="hidden" name="cmd" value="_donations"/>
								<input type="hidden" name="business" value="knlnagar@gmail.com">
								<input type="hidden" name="item_name" value="Custom 404 Pro is awesome!">
								<input type="hidden" name="currency_code" value="USD"/>
								<input type="hidden" name="first_name" value="Kunal">
								<input type="hidden" name="last_name" value="Nagar">
								<input type="hidden" name="email" value="knlnagar@gmail.com">
								<input type="image" name="submit" border="0"
								       src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif"
								       alt="PayPal - The safer, easier way to pay online">
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
							<b><a href="mailto:knlnagar@gmail.com">knlnagar@gmail.com</a></b>
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
