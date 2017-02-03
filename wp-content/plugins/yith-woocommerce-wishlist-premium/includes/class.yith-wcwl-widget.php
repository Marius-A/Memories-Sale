<?php
/**
 * Widget
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( !defined( 'YITH_WCWL' ) ) { exit; } // Exit if accessed directly

if( !class_exists( 'YITH_WCWL_Widget' ) ) {
    /**
     * WooCommerce Wishlist Widget
     *
     * @since 1.0.0
     */
    class YITH_WCWL_Widget extends WP_Widget {

        /**
         * Sets up the widgets
         */
        public function __construct(){
            parent::__construct(
                'yith-wcwl-lists',
                __( 'YITH Wishlist', 'yith-woocommerce-wishlist' ),
                array( 'description' => __( 'A list of user\'s wishlists', 'yith-woocommerce-wishlist' ), )
            );
        }

        /**
         * Outputs the content of the widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget( $args, $instance ) {
            $default_wishlist_title = get_option( 'yith_wcwl_wishlist_title' );

            $create_page_title = get_option( 'yith_wcwl_wishlist_create_title' );

            $manage_page_title = get_option( 'yith_wcwl_wishlist_manage_title' );

            $search_page_title = get_option( 'yith_wcwl_wishlist_search_title' );

            $wishlist_page_id = get_option( 'yith_wcwl_wishlist_page_id' );
	        $wishlist_page_id = function_exists( 'icl_object_id' ) ? icl_object_id( $wishlist_page_id, 'page', true ) : $wishlist_page_id;

            $current_wishlist = false;
            $active = false;

            if ( is_page( $wishlist_page_id ) ){
                // retrieve options from query string
                $action_params = get_query_var( 'wishlist-action', false );
                $action_params = explode( '/', $action_params );
                $action = ( isset( $action_params[0] ) ) ? $action_params[0] : false;

                $user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : false;

                if( $action == 'view' ){
                    $active = 'wishlist';
                    $current_wishlist = ( isset( $action_params[1] ) ) ? $action_params[1] : false;

                    if( ! $current_wishlist && isset( $user_id ) && $user_id = get_current_user_id() ){
                        $current_wishlist = 'default';
                    }
                }
                elseif( $action == 'user' ){
                    $active = 'wishlist';
                    $user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : false;
                    $user_id = ( empty( $user_id ) ) ? $action_params[1] : false;

                    if( $user_id == get_current_user_id() ){
                        $current_wishlist = 'default';
                    }
                }
                elseif( $action == 'create' ){
                    $current_wishlist = 'none';
                    $active = 'create';
                }
                elseif( $action == 'manage' ){
                    $current_wishlist = 'none';
                    $active = 'manage';
                }
                elseif( $action == 'search' ){
                    $current_wishlist = 'none';
                    $active = 'search';
                }
                else{
                    $current_wishlist = 'default';
                    $active = 'wishlist';
                }
            }

            $additional_info = array(
                'wishlist_url' => YITH_WCWL()->get_wishlist_url(),
                'instance' => $instance,
                'users_wishlists' => YITH_WCWL()->get_wishlists( ),
                'default_wishlist_title' => $default_wishlist_title,
                'user_logged_in' => is_user_logged_in(),
                'current_user_id' => get_current_user_id(),
                'multiple_wishlist_enabled' => get_option( 'yith_wcwl_multi_wishlist_enable' ) == 'yes',
                'create_page_title' => $create_page_title,
                'manage_page_title' => $manage_page_title,
                'search_page_title' => $search_page_title,
                'current_wishlist' => $current_wishlist,
                'active' => $active
            );

            $args = array_merge( $args, $additional_info );

            yith_wcwl_get_template( 'wishlist-widget.php', $args );
        }

        /**
         * Outputs the options form on admin
         *
         * @param array $instance The widget options
         */
        public function form( $instance ) {
            $show_create_link = ( isset( $instance['show_create_link'] ) && $instance['show_create_link'] == 'yes' );
            $show_search_link = ( isset( $instance['show_search_link'] ) && $instance['show_search_link'] == 'yes' );
            $show_manage_link = ( isset( $instance['show_manage_link'] ) && $instance['show_manage_link'] == 'yes' );
            $title = isset( $instance['title'] ) ?  $instance['title'] : '';
            $wishlist_link = isset( $instance['wishlist_link'] ) ?  $instance['wishlist_link'] : '';
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'yith-woocommerce-wishlist' )?></label>
                <input class="widefat"  id="<?php echo $this->get_field_id( 'title' )?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo $title ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'wishlist_link' ); ?>"><?php _e( '"Your wishlist" link:', 'yith-woocommerce-wishlist' )?></label>
                <input class="widefat"  id="<?php echo $this->get_field_id( 'wishlist_link' )?>" name="<?php echo $this->get_field_name( 'wishlist_link' ) ?>" type="text" value="<?php echo $wishlist_link ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'show_create_link' ); ?>">
                    <input id="<?php echo $this->get_field_id( 'show_create_link' )?>" name="<?php echo $this->get_field_name( 'show_create_link' ) ?>" type="checkbox" value="yes" <?php checked( $show_create_link ) ?> />
                    <?php _e( 'Show create link' ); ?>
                </label><br/>
                <label for="<?php echo $this->get_field_id( 'show_search_link' ); ?>">
                    <input id="<?php echo $this->get_field_id( 'show_search_link' )?>" name="<?php echo $this->get_field_name( 'show_search_link' ) ?>" type="checkbox" value="yes" <?php checked( $show_search_link ) ?> />
                    <?php _e( 'Show search link' ); ?>
                </label><br/>
                <label for="<?php echo $this->get_field_id( 'show_manage_link' ); ?>">
                    <input id="<?php echo $this->get_field_id( 'show_manage_link' )?>" name="<?php echo $this->get_field_name( 'show_manage_link' ) ?>" type="checkbox" value="yes" <?php checked( $show_manage_link ) ?> />
                    <?php _e( 'Show manage link' ); ?>
                </label>
            </p>
        <?php
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance The new options
         * @param array $old_instance The previous options
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            $instance['wishlist_link'] = ( ! empty( $new_instance['wishlist_link'] ) ) ? strip_tags( $new_instance['wishlist_link'] ) : '';
            $instance['show_create_link'] = ( isset( $new_instance['show_create_link'] ) ) ? 'yes' : 'no';
            $instance['show_search_link'] = ( isset( $new_instance['show_search_link'] ) ) ? 'yes' : 'no';
            $instance['show_manage_link'] = ( isset( $new_instance['show_manage_link'] ) ) ? 'yes' : 'no';

            return $instance;
        }
    }
}