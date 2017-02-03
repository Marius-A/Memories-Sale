<?php
/**
 * Popular users products table class
 *
 * @author  Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.6
 */

if ( ! defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_WCWL_Users_Popular_Table' ) ) {
    /**
     * Admin view class. Create and populate "users that added product to wishlist" table
     *
     * @since 2.0.6
     */
    class YITH_WCWL_Users_Popular_Table extends WP_List_Table {

	    /**
	     * Current product id
	     *
	     * @var int current product id
	     */
	    public $product_id;

        /**
         * Class constructor method
         *
         * @return \YITH_WCWL_Users_Popular_Table
         * @since 2.0.6
         */
        public function __construct(){
            global $status, $page;

	        if( isset( $_GET['product_id'] ) ){
		        $this->product_id = $_GET['product_id'];
		        $product = wc_get_product( $this->product_id );

		        $product_name = $product->get_title();
	        }
	        else{
		        $product_name = __( 'product', 'yith-woocommerce-wishlist' );
	        }

            //Set parent defaults
            parent::__construct( array(
                'singular'  => sprintf( 'user for %s', $product_name ),     //singular name of the listed records
                'plural'    => sprintf( 'users for %s', $product_name ),    //plural name of the listed records
                'ajax'      => false        //does this table support ajax?
            ) );
        }
	    
	    /**
	     * Print column for user thumbnail
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.6
	     */
	    public function column_thumb( $item ){
		    $avatar = get_avatar( $item['id'], 40 );
		    $edit_url = get_edit_user_link( $item['id'] );

		    $column_content = sprintf( '<a href="%s">%s</a>', $edit_url, $avatar );
		    return $column_content;
	    }

	    /**
	     * Print column for user name
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_name( $item ){
		    $user_edit_url = get_edit_user_link( $item['id'] );
		    $user_name = $item['user_name'];
		    $user_email = $item['user_email'];

		    $actions = array(
				'ID' => $item['id'],
			    'edit' => sprintf( '<a href="%s" title="%s">%s</a>', $user_edit_url, __( 'Edit this user', 'yith-woocommerce-wishlist' ), __( 'Edit', 'yith-woocommerce-wishlist' ) ),
			    'mail_to' => sprintf( '<a href="mailto:%s" title="%s">%s</a>', $user_email, __( 'Email this user', 'yith-woocommerce-wishlist' ), __( 'Email user', 'yith-woocommerce-wishlist' ) )
		    );
		    $row_actions = $this->row_actions( $actions );

		    $column_content = sprintf( '<strong><a class="row-title" href="%s">%s</a></strong>%s', $user_edit_url, $user_name, $row_actions );
		    return $column_content;
	    }

	    /**
	     * Print column for user name
	     *
	     * @param $item array Item for the current record
	     * @return string Column content
	     * @since 2.0.5
	     */
	    public function column_date_added( $item ){
		    $date_added = $item['date_added'];

		    return date_i18n( 'd F Y', strtotime( $date_added ) );
	    }

	    /**
	     * Print column for actions
	     *
	     * @param $item array Current item
	     * @return string Column content
	     * @since 2.0.7
	     */
	    public function column_actions( $item ){
		    $send_promotional_email = esc_url( add_query_arg( array( 'page' => 'yith_wcwl_panel', 'tab' => 'popular', 'action' => 'send_promotional_email', 'user_id' => $item['id'], 'product_id' => $this->product_id  ), admin_url( 'admin.php' ) ) );

		    $column_content = sprintf( '<a class="button" href="%s" title="%s">%s</a>', $send_promotional_email, __( 'Send Promotional Email', 'yith-woocommerce-wishlist' ), __( 'Send Promotional Email', 'yith-woocommerce-wishlist' ) );

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
	            'date_added'   => __( 'Added on', 'yith-woocommerce-wishlist' ),
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
                'date_added'  => array( 'dateadded', true ),
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
            $total_items = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( DISTINCT user_id ) FROM {$wpdb->yith_wcwl_items} WHERE prod_id = %d", $this->product_id ) );

	        // sets order by arguments
	        $orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'dateadded';
	        $order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';

	        // sets search params
	        $search_string = ( ! empty( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;

            // sets columns headers
            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array( $columns, $hidden, $sortable );

	        $query = "SELECT
		             u.ID AS id,
		             u.user_login AS user_name,
		             u.user_email AS user_email,
		             i.dateadded AS date_added
		             FROM {$wpdb->users} AS u
		             LEFT JOIN {$wpdb->yith_wcwl_items} AS i ON u.ID = i.user_id
		             WHERE i.prod_id = %d";

	        $query_args = array( $this->product_id );

	        if( $search_string ){
		        $query .= " AND ( u.user_login LIKE %s || u.user_email LIKE %s )";
		        $query_args = array_merge(
			        $query_args,
			        array(
				        '%' . $search_string . '%',
				        '%' . $search_string . '%'
			        )
		        );
	        }


	        $query .= " GROUP BY u.ID";
	        $query .= " ORDER BY {$orderby} {$order} LIMIT %d, %d";

	        $query_args = array_merge(
		        $query_args,
		        array(
		            ( ( $current_page - 1 ) * $per_page ),
		            $per_page
	            )
	        );

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