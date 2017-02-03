<?php
/**
 * Popular products table class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCWL_Popular_Table' ) ) {
    /**
     * Admin view class. Create and populate "user with wishlists" table
     *
     * @since 1.0.0
     */
    class YITH_WCWL_Popular_Table extends WP_List_Table {

        /**
         * Class constructor method
         *
         * @return \YITH_WCWL_Popular_Table
         * @since 2.0.0
         */
        public function __construct(){
            global $status, $page;

            //Set parent defaults
            parent::__construct( array(
                'singular'  => 'product',     //singular name of the listed records
                'plural'    => 'products',    //plural name of the listed records
                'ajax'      => false        //does this table support ajax?
            ) );
        }
	    
	    /**
	     * Print column for product thumbnail
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_thumb( $item ){
		    $post_thumb = get_the_post_thumbnail( $item['id'], array( 150, 150 ) );
		    $product_url = get_edit_post_link( $item['id'] );

		    $column_content = sprintf( '<a href="%s">%s</a>', $product_url, $post_thumb );
		    return $column_content;
	    }

	    /**
	     * Print column for product name
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_name( $item ){
		    $product_url = get_permalink( $item['id'] );
		    $product_edit_url = get_edit_post_link( $item['id'] );
		    $product_name = $item['post_title'];

		    $actions = array(
				'ID' => $item['id'],
			    'edit' => sprintf( '<a href="%s" title="%s">%s</a>', $product_edit_url, __( 'Edit this item', 'yith-woocommerce-wishlist' ), __( 'Edit', 'yith-woocommerce-wishlist' ) ),
			    'view_users' => sprintf( '<a href="%s" title="%s">%s</a>', esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'show_users', 'product_id' => $item['id'] ), admin_url( 'admin.php' ) ) ), __( 'View users that have added this product to their wishlist', 'yith-woocommerce-wishlist' ), __( 'View users', 'yith-woocommerce-wishlist' ) ),
			    'view_product' => sprintf( '<a href="%s" title="%s" rel="permalink">%s</a>', $product_url, sprintf( __( 'View Product "%s"', 'yith-woocommerce-wishlist' ), $product_name ), __( 'View Product', 'yith-woocommerce-wishlist' ) ),
		    );
		    $row_actions = $this->row_actions( $actions );

		    $column_content = sprintf( '<strong><a class="row-title" href="%s">%s</a></strong>%s', esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'show_users', 'product_id' => $item['id'] ), admin_url( 'admin.php' ) ) ), $product_name, $row_actions );
		    return $column_content;
	    }

	    /**
	     * Print column for wishlist count
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_count( $item ){
		    $column_content = $item['wishlist_count'];
		    return sprintf( '<a href="%s">%d</a>', esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'show_users', 'product_id' => $item['id'] ), admin_url( 'admin.php' ) ) ), $column_content );
	    }

	    /**
	     * Print column for product actions
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_actions( $item ){
		    $product_url = get_permalink( $item['id'] );
		    $product_edit_url = get_edit_post_link( $item['id'] );
		    $view_users_url = esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'show_users', 'product_id' => $item['id'] ), admin_url( 'admin.php' ) ) );
		    $send_promotional_email = esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'send_promotional_email', 'product_id' => $item['id'] ), admin_url( 'admin.php' ) ) );
		    $product_name = $item['post_title'];

		    $column_content = sprintf( '<a class="button" href="%s" title="%s">%s</a> <a class="button" href="%s" title="%s">%s</a> <a class="button" href="%s" title="%s">%s</a> <a class="button" href="%s" title="%s">%s</a>', $send_promotional_email, __( 'Send promotional email', 'yith-woocommerce-wishlist' ), __( 'Send promotional email', 'yith-woocommerce-wishlist' ), $view_users_url, __( 'View users that have added this product to their wishlist', 'yith-woocommerce-wishlist' ), __( 'View users', 'yith-woocommerce-wishlist' ), $product_url, sprintf( __( 'View "%s"', 'yith-woocommerce-wishlist' ), $product_name ), __( 'View product', 'yith-woocommerce-wishlist' ), $product_edit_url, __( 'Edit title', 'yith-woocommerce-wishlist' ), __( 'Edit', 'yith-woocommerce-wishlist' ) );

		    return $column_content;
	    }

        /**
         * Default columns print method
         *
         * @param $item array Associative array of element to print
         * @param $column_name string Name of the column to print
         *
         * @return string
         * @since 2.0.0
         */
        public function column_default( $item, $column_name ){
            if( isset( $item[$column_name] ) ){
                return esc_html( $item[$column_name] );
            }
            else{
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
            }
        }

        /**
         * Returns columns available in table
         *
         * @return array Array of columns of the table
         * @since 2.0.0
         */
        public function get_columns(){
            $columns = array(
                'thumb'     => sprintf( '<span class="wc-image tips" data-tip="%s">%s</span>', __( 'Image', 'yith-woocommerce-wishlist' ), __( 'Image', 'yith-woocommerce-wishlist' ) ),
                'name'      => __( 'Name', 'yith-woocommerce-wishlist' ),
                'count'     => __( 'Wishlist count', 'yith-woocommerce-wishlist' ),
	            'actions'   => __( 'Actions', 'yith-woocommerce-wishlist' )
            );
            return $columns;
        }

        /**
         * Returns column to be sortable in table
         *
         * @return array Array of sortable columns
         * @since 2.0.0
         */
        public function get_sortable_columns() {
            $sortable_columns = array(
                'name'      => array( 'post_title', false ),     //true means it's already sorted
                'count'  => array( 'wishlist_count', true ),
            );
            return $sortable_columns;
        }

        /**
         * Prepare items for table
         *
         * @return void
         * @since 2.0.0
         */
        public function prepare_items() {
            global $wpdb; //This is used only if making any database queries

            // sets pagination arguments
            $per_page = 20;
            $current_page = $this->get_pagenum();
            $total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( DISTINCT prod_id ) FROM {$wpdb->yith_wcwl_items} AS i INNER JOIN {$wpdb->posts} AS p ON i.prod_id = p.ID WHERE p.post_status = %s", 'publish' ) );

	        // sets order by arguments
	        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'wishlist_count';
	        $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';

	        // sets search params
	        $search_string = ( ! empty( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;

            // sets columns headers
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array( $columns, $hidden, $sortable );

	        $query = "SELECT
		             DISTINCT i.prod_id AS id,
		             p.post_title AS post_title,
		             i2.wishlist_count AS wishlist_count
		             FROM {$wpdb->yith_wcwl_items} AS i
		             INNER JOIN {$wpdb->posts} AS p ON p.ID = i.prod_id
		             LEFT JOIN ( SELECT COUNT( DISTINCT user_id ) AS wishlist_count, prod_id FROM {$wpdb->yith_wcwl_items} GROUP BY prod_id ) AS i2 ON p.ID = i2.prod_id
		             WHERE 1=1 AND p.post_status = %s";

	        $query_args = array( 'publish' );

	        if( $search_string ){
		        $query .= " AND p.post_title LIKE %s";
		        $query_args = array_merge( $query_args, array( '%' . $search_string . '%' ) );
	        }

	        $query .= " ORDER BY {$orderby} {$order} LIMIT %d, %d";

	        $query_args = array_merge( $query_args, array(
		        ( ( $current_page - 1 ) * $per_page ),
		        $per_page
	        ) );

            // retrieve data for table
	        $this->items = $wpdb->get_results(
		        $wpdb->prepare( $query, $query_args ),
		        ARRAY_A
            );

            // sets pagination args
            $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items / $per_page )
            ) );
        }
    }
}