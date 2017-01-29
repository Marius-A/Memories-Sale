<div class="wrap about-wrap getbowtied-about-wrap getbowtied-welcome-wrap">
	
    <?php require_once('global/pages-header.php'); ?>
    <h2 class="welcome_title">Thank you for using <?php echo getbowtied_theme_name(); ?>! </h2>

	<div class="getbowtied-welcome">
        <div class="theme-browser rendered">
            
            <a class="theme key" href="<?php echo admin_url( 'admin.php?page=' . $registration_page ); ?>">                

               <img src="<?php echo get_template_directory_uri().'/backend/img/key.png'; ?>" /><br/>
               <?php echo __('1. Enter the Product Key', 'getbowtied'); ?>
            </a>

            <a class="theme plugins" href="<?php echo admin_url( 'admin.php?page=' . $plugins_page ) ?>">

                <img src="<?php echo get_template_directory_uri().'/backend/img/plugin.png'; ?>" /><br/>
                <?php echo __('2. Install the Plugins', 'getbowtied'); ?>
            </a>

            <a class="theme demo" href="<?php echo admin_url( 'admin.php?page=' . $demos_page ); ?>">

                <img src="<?php echo get_template_directory_uri().'/backend/img/demo.png'; ?>" /><br/>
                <?php echo __('3. Install the Demo Content', 'getbowtied'); ?>
            </a>

            <div style="clear:both"></div>
            <p class="need-help"><?php echo __('Need help getting started? Check out the', 'getbowtied'); ?> <a href="<?php echo $getbowtied_settings['theme_docs'] ?>" target="_blank"><?php echo __('Theme’s Documentation', 'getbowtied'); ?><span class="dashicons dashicons-external"></span></a>
        </div>
    </div>
<!-- ... -->
</div>

