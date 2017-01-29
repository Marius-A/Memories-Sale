<?php
$plugins = TGM_Plugin_Activation::$instance->plugins;
$installed_plugins = get_plugins();

//print_r($plugins);
//echo '<br/>';
//print_r($installed_plugins);
?>
<div class="wrap about-wrap getbowtied-about-wrap">

    <?php include_once('global/pages-header.php'); ?>
    <br />
	<div class="getbowtied-install-plugins">
		<div class="theme-browser rendered">
			
			<?php
			foreach( $plugins as $plugin ):
				$class = '';
				$plugin_status = '';
				$file_path = $plugin['file_path'];
				$plugin_action = $this->getbowtied_theme_plugin_links( $plugin );

				if( is_plugin_active( $file_path ) ) {
					$plugin_status = 'active';
					$class = 'active';
				}
			?>
			
			<div class="theme <?php echo $class; ?>">

				<div class="theme-screenshot">
					<img src="<?php echo $plugin['image_url']; ?>" alt="" />
				</div>

				<h3 class="theme-name">
					<?php
					if( $plugin_status == 'active' ) {
						//echo sprintf( '<span>%s</span> ', __( 'Active:', 'getbowtied' ) );
					}
					echo $plugin['name'];
					?>

					<?php if( isset( $installed_plugins[$plugin['file_path']] ) ): ?> 
					<div class="plugin-info">
						<?php echo sprintf('Version %s', $installed_plugins[$plugin['file_path']]['Version'] ); ?>
					</div>
					<?php endif; ?>

				</h3>



				<div class="theme-actions">
					<?php foreach( $plugin_action as $action ) { echo $action; } ?>
				</div>

				<?php if( isset( $plugin_action['update'] ) && $plugin_action['update'] ): ?>
				<div class="theme-update">Update Available: Version <?php echo $plugin['version']; ?></div>
				<?php endif; ?>

				<?php if( $plugin['required'] ): ?>
				<div class="plugin-required">
					<?php _e( 'Required', 'getbowtied' ); ?>
				</div>
				<?php endif; ?>

			</div>

			<?php endforeach; ?>

		</div>
	</div>

</div>