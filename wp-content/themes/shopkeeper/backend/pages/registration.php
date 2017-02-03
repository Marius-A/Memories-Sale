<div class="wrap about-wrap getbowtied-about-wrap getbowtied-registration-wrap">
	
    <?php require_once('global/pages-header.php'); ?>

    <?php 

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['getbowtied_key']))
        {
           $rsp = Getbowtied_Admin_Pages::validate_license(trim($_POST['getbowtied_key']));
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST['getbowtied_key']) && empty($_POST['action']))
        {
            $rsp = 'Please fill out the product key.';
        }
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'delkey')
        {
            delete_option("getbowtied_".THEME_SLUG."_license");
            delete_option("getbowtied_".THEME_SLUG."_license_expired");
            delete_option("getbowtied_".THEME_SLUG."_intercom_email");
        }
    ?>


    <?php 
        $activated = get_option("getbowtied_".THEME_SLUG."_license");
        $expired   = get_option("getbowtied_".THEME_SLUG."_license_expired");

        if ($activated == ''): 
    ?>

    	<div class="getbowtied-registration-form">
        	<form id="getbowtied_product_registration" action="" method="POST">
                
                <input type="hidden" name="register" value="true" />
                
                <h2 for="getbowtied_key"><?php echo __( "Product Activation", "getbowtied" ); ?></h2>

                <p class="getbowtied_key_infos">Enable automatic theme & premium plugin updates directly from the administration panel. To validate your purchase 
                <a href="<?php echo $getbowtied_settings['getbowtied_url']; ?>" target="_blank">generate a product key</a> and paste it below.</p>

                <br />

                <?php 
                    if ( ! empty( $rsp ))
                    {
                        echo '<span style="color:red">'.$rsp.'</span><br/><br/>';
                    }
                ?>
                
                <input type="text" name="getbowtied_key" id="getbowtied_key" placeholder="Paste your Product Key Here" value="" />

                <br /><br />
            
                <button class="button button-primary getbowtied-register" type="submit"><?php echo __( "Submit Product Key", "getbowtied" ); ?></button>
            
            </form>
        </div>

    <?php elseif ($activated != '' && $expired != 1): ?>

        <div class="getbowtied-registration-done">
                
                <input type="hidden" name="register" value="true" />
                
                <h2 for="getbowtied_key"><?php echo __( "Product Activated", "getbowtied" ); ?></h2>

                <p class="getbowtied_key_infos">You will now be able to update the theme without having to leave the administration panel.</p>

                <br />
                
                <div class="getbowtied_product_key"><?php echo get_option("getbowtied_".THEME_SLUG."_license");?>
                        <form class="delete_key" method="POST" action="">
                            <input type="hidden" name="action" value="delkey">
                            <button alt="Remove this key" type="submit" value="submit">
                                <span class="dashicons dashicons-no"></span>
                            </button>
                        </form>
                </div>

                <br />

                <div class="getbowtied_key_infos_2">Moving your site to a new location? <a href="<?php echo $getbowtied_settings['getbowtied_url']; ?>" target="_blank">Generate a new Product Key</a> and paste it on the new domain. Changing the domain name will revoke the access for this one here.</div>

                <br />
            
                <a href="<?php echo $getbowtied_settings['getbowtied_url']; ?>" class="button button-primary getbowtied-manage-licenses" target="_blank" type="submit"><?php echo __( "Manage Product Keys", "getbowtied" ); ?></a>

        </div>

    <?php elseif ($activated != '' && $expired == 1): ?>

        <div class="getbowtied-registration-form expired">

            <form id="getbowtied_product_registration" action="" method="POST">
                
                <input type="hidden" name="register" value="true" />
                
                <h2 for="getbowtied_key"><?php echo __( "Product Activation", "getbowtied" ); ?></h2>

                <p class="getbowtied_key_infos">Your <b>Product Key</b>  is no longer active on this domain. Your site will no longer receive automatic theme updates. That's fine if you moved your site and activated it for a new domain.</p>
                <div class="getbowtied_key_infos_2">Need a 2<sup>nd</sup> product key? <a href="<?php echo $getbowtied_settings['purchase']; ?>" target="_blank">Purchase another license</a> </div>
                <br/><br/>
                <a href="<?php echo $getbowtied_settings['getbowtied_url']; ?>" class="button button-primary getbowtied-manage-licenses" type="submit"><?php echo __( "Manage Product Keys", "getbowtied" ); ?></a>
                
                <br /><br/>

                <?php 
                    if ( ! empty( $rsp ))
                    {
                        echo '<span style="color:red">'.$rsp.'</span><br/><br/>';
                    }
                ?>

                <input type="text" name="getbowtied_key" id="getbowtied_key" placeholder="Paste your Product Key Here" value="<?php echo get_option("getbowtied_".THEME_SLUG."_license");?>" />
                
                <br /><br/> 

                <button class="button button-primary getbowtied-register" type="submit"><?php echo __( "Submit Product Key", "getbowtied" ); ?></button>

                           

            </form>

        </div>

    <?php endif; ?>

    <!-- <i> TESTING BUTTON </i>

    <form method="POST" action="">
    <input type="hidden" name="action" value="delkey">
    <button type="submit" value="submit">DELETE KEY</button>
    </form> -->

</div>