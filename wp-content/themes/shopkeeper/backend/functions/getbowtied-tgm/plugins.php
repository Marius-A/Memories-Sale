<?php

function check_getbowtied_plugins()
{
    if ( !get_option("getbowtied_".THEME_SLUG."_license") || (get_option("getbowtied_".THEME_SLUG."_license_expired") == 1) ) 
    {
        return FALSE;
    }

    $api_url = "http://my.getbowtied.com/api/update_plugins2.php";
    $theme = wp_get_theme();

    $args = array(
            'method' => 'POST',
            'timeout' => 30,
            'body' => array( 't' => THEME_NAME, 'l' => get_option("getbowtied_".THEME_SLUG."_license"))
    );



    $response = wp_remote_post( $api_url, $args );

    // echo '<pre>';
    // print_r($response);
    // echo '</pre>';
    // die();
    
    return $response;
}

function getbowtied_theme_register_required_plugins() 
{

    // Check GetBowtied server for latest plugins
    $response = check_getbowtied_plugins();
    $plugins = array();

    if ( is_wp_error( $response ) ) 
    {
        $error_message = $response->get_error_message();
        $request_msg = 'Something went wrong:'.$error_message.'. Please try again!';
    } 
    else 
    {
        $rsp = $response['body'];
        $rsp = json_decode($rsp, true);

        if (!empty($rsp))
        {
            foreach ($rsp as $plugin)
            {
                $plugins[] = array(
                    'name'                  => $plugin['name'], // The plugin name
                    'slug'                  => $plugin['slug'], // The plugin slug (typically the folder name)
                    'source'                => $plugin['source'], // The plugin source
                    'required'              => $plugin['required'], // If false, the plugin is only 'recommended' instead of required
                    'version'               => $plugin['version'], // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
                    'force_activation'      => $plugin['force_activation'], // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                    'force_deactivation'    => $plugin['force_deactivation'], // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                    'external_url'          => '', // If set, overrides default API URL and points to an external URL
                    'image_url'             => get_template_directory_uri() . '/backend/img/plugins/'.$plugin['image_url']
                );
            }
        }
    }

    if (!empty($plugins))
    {
        // Do nothing
    }
    else 
    {
        // If remote request fails use theme delivered plugins

        if (THEME_SLUG == 'the_retailer')
        {
            $plugins = array(

                // PREMIUM Plugins
            
                array(
                       'name'                  => 'WPBakery Visual Composer', // The plugin name
                       'slug'                  => 'js_composer', // The plugin slug (typically the folder name)
                       'source'                => get_template_directory() . '/inc/plugins/js_composer.zip', // The plugin source
                       'required'              => true, // If false, the plugin is only 'recommended' instead of required
                       'version'               => '4.11', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
                       'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                       'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                       'external_url'          => '', // If set, overrides default API URL and points to an external URL
                       'image_url'             => get_template_directory_uri() . '/backend/img/plugins/visual-composer.jpg',
                ),
                

                array(
                    'name'                     => 'Slider Revolution',
                    'slug'                     => 'revslider',
                    'source'                   => 'http://my.getbowtied.com/api/plugins/revslider.zip',
                    'required'                 => false,
                    'version'                  => '5.2.1',
                    'force_activation'         => false,
                    'force_deactivation'       => false,
                    'external_url'             => '',
                    'image_url'                =>  get_template_directory_uri() . '/backend/img/plugins/revolution-slider.jpg'
                ),

                // From WP Repository
                
                array(
                     'name'                  => 'WooCommerce', // The plugin name
                     'slug'                  => 'woocommerce', // The plugin slug (typically the folder name)
                     'source'                => 'https://downloads.wordpress.org/plugin/woocommerce.2.5.5.zip', // The plugin source
                     'required'              => false, // If false, the plugin is only 'recommended' instead of required
                     'version'               => '2.5.5', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
                     'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                     'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                     'external_url'          => '', // If set, overrides default API URL and points to an external URL
                     'image_url'             => get_template_directory_uri() . '/backend/img/plugins/woocommerce.jpg',
                ),


                array(
                    'name'                  => 'Contact Form 7',
                    'slug'                  => 'contact-form-7',
                    'source'                => 'https://downloads.wordpress.org/plugin/contact-form-7.4.4.zip',
                    'required'              => false,
                    'version'               => '4.4',
                    'force_activation'      => false,
                    'force_deactivation'    => false,
                    'external_url'          => '',
                    'image_url'             =>  get_template_directory_uri() . '/backend/img/plugins/contact-form-7.jpg'
                )
            );
        }
        else 
        {
            $plugins = array(

                // PREMIUM Plugins
            
                array(
                       'name'                  => 'WPBakery Visual Composer', // The plugin name
                       'slug'                  => 'js_composer', // The plugin slug (typically the folder name)
                       'source'                => get_template_directory() . '/inc/plugins/js_composer.zip', // The plugin source
                       'required'              => true, // If false, the plugin is only 'recommended' instead of required
                       'version'               => '4.11', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
                       'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                       'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                       'external_url'          => '', // If set, overrides default API URL and points to an external URL
                       'image_url'             => get_template_directory_uri() . '/backend/img/plugins/visual-composer.jpg',
                ),
                
                // From WP repository
                
                array(
                     'name'                  => 'WooCommerce', // The plugin name
                     'slug'                  => 'woocommerce', // The plugin slug (typically the folder name)
                     'source'                => 'https://downloads.wordpress.org/plugin/woocommerce.2.5.5.zip', // The plugin source
                     'required'              => false, // If false, the plugin is only 'recommended' instead of required
                     'version'               => '2.5.5', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
                     'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
                     'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
                     'external_url'          => '', // If set, overrides default API URL and points to an external URL
                     'image_url'             => get_template_directory_uri() . '/backend/img/plugins/woocommerce.jpg',
                ),

            );
        }
    }

    // echo '<pre>';
    // print_r($plugins);
    // echo '</pre>';
	
    $theme_text_domain = 'GetBowtied';

    $config = array(
        'domain'            => $theme_text_domain,
        'default_path'      => '',
        'parent_slug'       => 'themes.php',
        'menu'              => 'tgmpa-install-plugins',
        'has_notices'       => true,
        'is_automatic'      => true,
        'message'           => '',
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'tgmpa' ),
            'menu_title'                      => __( 'Install Plugins', 'tgmpa' ),
            'installing'                      => __( 'Installing Plugin: %s', 'tgmpa' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'tgmpa' ),
            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
            'return'                          => __( 'Return to Required Plugins Installer', 'tgmpa' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'tgmpa' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'tgmpa' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );

    tgmpa( $plugins, $config );
}

add_action( 'tgmpa_register', 'getbowtied_theme_register_required_plugins' );

?>