<?php 
	$active_page 		= $_GET["page"];

	$welcome_page 		= "getbowtied_theme";
	$registration_page 	= "getbowtied_theme_registration";
	$plugins_page 		= "getbowtied_theme_plugins";
	$demos_page 		= "getbowtied_theme_demos";
	
	$getbowtied_settings = Getbowtied_Admin_Pages::settings();

?>



<h1><?php echo getbowtied_theme_name(); ?></h1>
<p><strong><?php echo __( "Version", "getbowtied" ); ?> <?php echo getbowtied_theme_version(); ?></strong> - 
<a href="<?php echo $getbowtied_settings['release_notes']; ?>" target="_blank"><?php echo __( "View Release Notes", "getbowtied" ); ?></a></p>

<div class="getbowtied-theme-badge" style="background-image:url(<?php echo $getbowtied_settings['theme_logo']; ?>)"></div>

<h2 class="nav-tab-wrapper getbowtied-tab-wrapper">
	<?php
	printf( '<a href="%s" class="nav-tab ' . ($active_page == $welcome_page ? 		'nav-tab-active' : '') . '">%s</a>', admin_url( 'admin.php?page=' . $welcome_page ),		__( "Welcome", "getbowtied" ) );
    printf( '<a href="%s" class="nav-tab ' . ($active_page == $registration_page ? 	'nav-tab-active' : '') . '">%s</a>', admin_url( 'admin.php?page=' . $registration_page ), 	__( "Product Activation", "getbowtied" ) );
	printf( '<a href="%s" class="nav-tab ' . ($active_page == $plugins_page ? 		'nav-tab-active' : '') . '">%s</a>', admin_url( 'admin.php?page=' . $plugins_page ), 		__( "Plugins", "getbowtied" ) );
	printf( '<a href="%s" class="nav-tab ' . ($active_page == $demos_page ? 		'nav-tab-active' : '') . '">%s</a>', admin_url( 'admin.php?page=' . $demos_page ), 			__( "Demo", "getbowtied" ) );
	?>
</h2>
