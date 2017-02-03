<?php
/**
 * Shortcodes Premium class
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 1.1.5
 */

if ( ! defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly

if( ! class_exists( 'YITH_WCWL_Shortcode_Premium' ) ) {
    /**
     * YITH WCWL Shortcodes Premium
     *
     * @since 1.0.0
     */
    class YITH_WCWL_Shortcode_Premium {

        /**
         * Constructor
         *
         * @return \YITH_WCWL_Shortcode_Premium
         * @since 2.0.0
         */
        public function __construct() {
            // Filters applied to add params to wishlist views
            add_filter( 'yith_wcwl_available_wishlist_views', array( 'YITH_WCWL_Shortcode_Premium', 'add_wishlist_views' ) );
	        add_filter( 'yith_wcwl_wishlist_params', array( 'YITH_WCWL_Shortcode_Premium', 'wishlist_view' ), 5, 5 );
            add_filter( 'yith_wcwl_wishlist_params', array( 'YITH_WCWL_Shortcode_Premium', 'wishlist_create' ), 10, 5 );
            add_filter( 'yith_wcwl_wishlist_params', array( 'YITH_WCWL_Shortcode_Premium', 'wishlist_manage' ), 15, 5 );
            add_filter( 'yith_wcwl_wishlist_params', array( 'YITH_WCWL_Shortcode_Premium', 'wishlist_search' ), 20, 5 );

            // Filters applied to add params to add-to-wishlist view
            add_filter( 'yith_wcwl_add_to_wishlist_params', array( 'YITH_WCWL_Shortcode_Premium', 'add_to_wishlist_popup' ) );
        }

        /**
         * Add premium wishlist views
         *
         * @param $views array Array of available views
         * @return array New array of availableviews
         * @since 2.0.0
         */
        public static function add_wishlist_views( $views ){
            return array_merge( $views, array( 'create', 'manage', 'search' ) );
        }

	    /**
	     * Filters template params, to add view-specific variables
	     *
	     * @param $additional_params array Array of params to filter
	     * @param $action string Action from query string
	     * @param $action_params array Array of query-string params
	     * @param $pagination string Whether or not pagination is enabled for template (not always required; value showuld be "yes" or "no")
	     * @param $per_page string Number of elements per page (required only if $pagination == 'yes'; should be a numeric string)
	     *
	     * @return array Filtered array of params
	     * @since 2.0.0
	     */
	    public static function wishlist_view( $additional_params, $action, $action_params, $pagination, $per_page ){
		    /* === VIEW TEMPLATE === */
		    if( ! empty( $additional_params['template_part'] ) && $additional_params['template_part'] == 'view' ){

			    $multi_wishlist = ( get_option( 'yith_wcwl_multi_wishlist_enable' ) == 'yes' ) ? true : false;
			    $additional_info = get_option( 'yith_wcwl_show_additional_info_textarea' ) == 'yes';
			    $additional_info_label = get_option( 'yith_wcwl_additional_info_textarea_label' );
			    $move_to_another_wishlist = get_option( 'yith_wcwl_show_move_to_another_wishlist' ) == 'yes';
			    $show_cb = get_option( 'yith_wcwl_cb_show' ) == 'yes';

			    $additional_params = array_merge( $additional_params, array(
				    'additional_info' => $additional_info,
					'additional_info_label' => $additional_info_label,
				    'users_wishlists' => YITH_WCWL()->get_wishlists(),
				    'available_multi_wishlist' => $multi_wishlist && is_user_logged_in(),
				    'move_to_another_wishlist' => $move_to_another_wishlist,
				    'show_last_column' => $additional_params['show_last_column'] || ( $multi_wishlist && is_user_logged_in() && $move_to_another_wishlist ),
				    'show_cb' => $show_cb
			    ) );
		    }

		    return $additional_params;
	    }

        /**
         * Filters template params, to add create-specific variables
         *
         * @param $additional_params array Array of params to filter
         * @param $action string Action from query string
         * @param $action_params array Array of query-string params
         * @param $pagination string Whether or not pagination is enabled for template (not always required; value showuld be "yes" or "no")
         * @param $per_page string Number of elements per page (required only if $pagination == 'yes'; should be a numeric string)
         *
         * @return array Filtered array of params
         * @since 2.0.0
         */
        public static function wishlist_create( $additional_params, $action, $action_params, $pagination, $per_page ){
            /* === CREATE TEMPLATE === */
            if( ! empty( $action ) && $action == 'create' && get_option( 'yith_wcwl_multi_wishlist_enable', false ) == 'yes' ){
                /*
                 * no wishlist has to be loaded
                 */

                $template_part = 'create';

                if ( !is_user_logged_in() ) {
                    if ( function_exists( 'wc_add_notice' ) ) {
                        wc_add_notice( __( 'Sorry! This feature is available only for logged users', 'yith-woocommerce-wishlist' ), 'notice' );
                    }
                    else {
                        global $woocommerce;
                        $woocommerce->add_message( __( 'Sorry! This feature is available only for logged users', 'yith-woocommerce-wishlist' ) );
                    }
                }

                $page_title = get_option( 'yith_wcwl_wishlist_create_title' );

                $additional_params = array(
                    'page_title' => $page_title,
                    'template_part' => $template_part
                );
            }

            return $additional_params;
        }

        /**
         * Filters template params, to add manage-specific variables
         *
         * @param $additional_params array Array of params to filter
         * @param $action string Action from query string
         * @param $action_params array Array of query-string params
         * @param $pagination string Whether or not pagination is enabled for template (not always required; value showuld be "yes" or "no")
         * @param $per_page string Number of elements per page (required only if $pagination == 'yes'; should be a numeric string)
         *
         * @return array Filtered array of params
         * @since 2.0.0
         */
        public static function wishlist_manage( $additional_params, $action, $action_params, $pagination, $per_page ){
            /* === MANAGE TEMPLATE === */
            if( ! empty( $action ) && $action == 'manage' && get_option( 'yith_wcwl_multi_wishlist_enable', false ) == 'yes' ) {
                /*
                 * someone is managing his wishlists
                 * loads all logged user wishlist
                 */

                $template_part = 'manage';

                $page_title = get_option( 'yith_wcwl_wishlist_manage_title' );

                $user_wishlists = array();

                if ( !is_user_logged_in() ) {
                    if ( function_exists( 'wc_add_notice' ) ) {
                        wc_add_notice( __( 'Sorry! This feature is available only for logged users', 'yith-woocommerce-wishlist' ), 'notice' );
                    }
                    else {
                        global $woocommerce;
                        $woocommerce->add_message( __( 'Sorry! This feature is available only for logged users', 'yith-woocommerce-wishlist' ) );
                    }
                }
                else {
                    // retrieve user wishlist
                    $user_wishlists = YITH_WCWL()->get_wishlists( array( 'user_id' => get_current_user_id() ) );
                }

                $default_wishlist_title = get_option( 'yith_wcwl_wishlist_title' );

                $additional_params = array(
                    'page_title' => $page_title,
                    'template_part' => $template_part,
                    'user_wishlists' => $user_wishlists,
                    'current_user_id' => get_current_user_id(),
                    'default_wishlist_title' => $default_wishlist_title
                );
            }

            return $additional_params;
        }

        /**
         * Filters template params, to add search-specific variables
         *
         * @param $additional_params array Array of params to filter
         * @param $action string Action from query string
         * @param $action_params array Array of query-string params
         * @param $pagination string Whether or not pagination is enabled for template (not always required; value showuld be "yes" or "no")
         * @param $per_page string Number of elements per page (required only if $pagination == 'yes'; should be a numeric string)
         *
         * @return array Filtered array of params
         * @since 2.0.0
         */
        public static function wishlist_search( $additional_params, $action, $action_params, $pagination, $per_page ){
            /* === SEARCH TEMPLATE === */
            if( ! empty( $action ) && $action == 'search' ){
                /*
                 * someone is searching a wishlist
                 * loads wishlist corresponding to search
                 */

                $wishlist_search = isset( $action_params[1] ) ? $action_params[1] : false;
                $wishlist_search = ( ! $wishlist_search && isset( $_REQUEST[ 'wishlist_search' ] ) ) ? $_REQUEST[ 'wishlist_search' ] : $wishlist_search;

                $template_part = 'search';

                $page_title = get_option( 'yith_wcwl_wishlist_search_title' );
                $search_results = false;

                if( ! empty( $wishlist_search ) ){
                    $count = YITH_WCWL()->count_users_with_wishlists( $wishlist_search );

                    // sets current page, number of pages and element offset
                    $current_page = max( 1, get_query_var( 'paged' ) );
                    $offset = 0;

                    // sets variables for pagination, if shortcode atts is set to yes
                    if( $pagination == 'yes' && $count > 1 ){
                        $pages = ceil( $count / $per_page );

                        if( $current_page > $pages ){
                            $current_page = $pages;
                        }

                        $offset = ( $current_page - 1 ) * $per_page;

                        if( $pages > 1 ){
                            $page_links = paginate_links( array(
                                'base' => esc_url( add_query_arg( array( 'paged' => '%#%' ), YITH_WCWL()->get_wishlist_url( 'search' . '/' . $wishlist_search ) ) ),
                                'format' => '?paged=%#%',
                                'current' => $current_page,
                                'total' => $pages,
                                'show_all' => true
                            ) );
                        }
                    }
                    else{
                        $per_page = false;
                    }

                    $search_results = YITH_WCWL()->get_users_with_wishlist( array( 'search' => $wishlist_search, 'limit' => $per_page, 'offset' => $offset ) );
                }

                $default_wishlist_title = get_option( 'yith_wcwl_wishlist_title' );

                $additional_params = array(
                    'page_title' => $page_title,
                    'pages_links' => isset( $page_links ) ? $page_links : false,
                    'search_string' => $wishlist_search,
                    'search_results' => $search_results,
                    'template_part' => $template_part,
                    'default_wishlist_title' => $default_wishlist_title
                );
            }

            return $additional_params;
        }

        /**
         * Add additional params to use in wishlist popup
         *
         * @param $additional_info array Array of parameters
         *
         * @return array Filtered array of params
         * @since 2.0.0
         */
        public static function add_to_wishlist_popup( $additional_info ){
            global $product;

            //$product_image = get_the_post_thumbnail( $product->id, apply_filters( 'single_product_small_thumbnail_size', 'shop_thumbnail' ) );
            $product_image = '';

            $multi_wishlist = ( get_option( 'yith_wcwl_multi_wishlist_enable' ) == 'yes' ) ? true : false;
            $lists = YITH_WCWL()->get_wishlists();

            $popup_title = apply_filters( 'yith_wcwl_add_to_wishlist_popup_title', __( 'Select a Wishlist', 'yith-woocommerce-wishlist' ) );

            $label_option = get_option( 'yith_wcwl_add_to_wishlist_popup_text' );
            $label = apply_filters( 'yith_wcwl_button_popup_label', $label_option );

            $template_part = ( $multi_wishlist && is_user_logged_in() ) ? 'popup' : 'button';

            $popup_classes = apply_filters( 'yith_wcwl_add_to_wishlist_popup_classes', get_option( 'yith_wcwl_use_button' ) == 'yes' ? 'add_to_wishlist popup_button single_add_to_wishlist button alt' : 'add_to_wishlist popup_button' );

	        $disable_wishlist = get_option( 'yith_wcwl_disable_wishlist_for_unauthenticated_users' );
	        $show_exists_in_a_wishlist = get_option( 'yith_wcwl_show_exists_in_a_wishlist' ) == 'yes';

	        $exists = $additional_info['exists'];
	        if( is_user_logged_in() && $show_exists_in_a_wishlist && $multi_wishlist ){
		        $user_wishlists = YITH_WCWL()->get_wishlists();

		        if( ! empty( $user_wishlists ) ){
			        foreach( $user_wishlists as $wl ){
				        if( YITH_WCWL()->is_product_in_wishlist( $product->id, $wl['ID'] ) ){
					        $exists_id = $wl['wishlist_token'];
					        $exists = true;
					        break;
				        }
			        }
		        }

		        if( isset( $exists_id ) ){
			        $additional_info['wishlist_url'] = YITH_WCWL()->get_wishlist_url( 'view' . '/' . $exists_id );
		        }
	        }

            $additional_info = array_merge( $additional_info, array(
                'template_part' => $template_part,
                'product_image' => $product_image,
                'popup_title' => $popup_title,
                'lists' => $lists,
                'label_popup' => $label,
                'available_multi_wishlist' => $multi_wishlist && is_user_logged_in(),
	            'exists' => ( $multi_wishlist && is_user_logged_in() && ! $show_exists_in_a_wishlist ) ? false : $exists,
	            'show_exists' => $show_exists_in_a_wishlist,
                'link_popup_classes' => $popup_classes,
                'disable_wishlist' => $disable_wishlist == 'yes'
            ) );

            return $additional_info;
        }
    }
}

return new YITH_WCWL_Shortcode_Premium();