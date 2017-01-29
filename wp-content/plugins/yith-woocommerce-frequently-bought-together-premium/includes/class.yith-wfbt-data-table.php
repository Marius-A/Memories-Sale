<?php
if ( ! defined( 'YITH_WFBT' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Displays the data table in YITH_WFBT plugin admin tab
 *
 * @class   YITH_WFBT_Data_Table
 * @package YITH WooCommerce Frequently Bought Together Premium
 * @since   1.0.0
 * @author  Yithemes
 *
 */
if( ! class_exists( 'YITH_WFBT_Data_Table' ) ) {

	class YITH_WFBT_Data_Table {

		/**
		 * Single instance of the class
		 *
		 * @var \YITH_WFBT_Data_Table
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WFBT_Data_Table
		 * @since 1.0.0
		 */
		public static function get_instance(){
			if( is_null( self::$instance ) ){
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since 1.0.0
		 */
		public function __construct() {

			add_action( 'admin_init', array( $this, 'table_actions' ) );
		}

		/**
		 * Outputs the waitlist data table template
		 *
		 * @since   1.0.0
		 * @author  Francesco Licandro <francesco.licandro@yithemes.com>
		 * @return  string
		 */
		public function prepare_table() {

			if ( ! empty( $_GET['view'] ) && 'linked' == $_GET['view'] ) {
				$table = $this->linked_table();
			} else {
				$table = $this->data_table();
			}

			return $table;
		}


		public function table_actions() {

			$page    = isset( $_GET['page'] ) ? $_GET['page'] : '';
			$tab     = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
			$action  = isset( $_GET['action'] ) ? $_GET['action'] : '';

			if( $page != 'yith_wfbt_panel' || $tab != 'data' || $action == '' ) {
				return;
			}

			// remove linked
			if ( 'delete' ==  $action ) {

				$ids = isset( $_GET['id'] ) ? $_GET['id'] : array();
				if ( ! is_array( $ids ) ) {
					$ids = explode( ',', $ids );
				}
				// delete post meta
				foreach( $ids as $id ) {
					delete_post_meta( $id, YITH_WFBT_META );
					delete_post_meta( $id, YITH_WFBT_META_VARIATION );
					delete_post_meta( $id, YITH_WFBT_META_NUM );
				}
				// add message
				if( empty( $ids ) ) {
					$mess = 1;
				}
				else {
					$mess = 2;
				}
			}
			// remove single from meta
			elseif ( 'remove_linked' == $action ) {

				if ( ! isset( $_GET['post_id'] ) && ! isset( $_GET['id'] ) ) {
					$mess = 1;
				}
				else {
					$ids = is_array( $_GET['id'] ) ? $_GET['id'] : array( $_GET['id'] );
					// get meta
					$meta = get_post_meta( $_GET['post_id'], YITH_WFBT_META, true );
					// remove
					$diff = array_diff( $meta, $ids );
					// delete if empty
					if ( empty( $diff ) ) {
						delete_post_meta( $_GET['post_id'], YITH_WFBT_META );
						delete_post_meta( $_GET['post_id'], YITH_WFBT_META_VARIATION );
						delete_post_meta( $_GET['post_id'], YITH_WFBT_META_NUM );
					} // else update meta
					else {
						update_post_meta( $_GET['post_id'], YITH_WFBT_META, $diff );
					}
					$mess = 2;
				}
			}

			$list_query_args = array(
				'page'          => $page,
				'tab'           => $tab,
			);
			// Set users table
			if( isset( $_GET['view'] ) && isset( $_GET['post_id'] ) ) {
				$list_query_args['view']    = $_GET['view'];
				$list_query_args['post_id'] = $_GET['post_id'];
			}
			// Add message
			if( isset( $mess ) && $mess != '' ) {
				$list_query_args['wfbt_mess'] = $mess;
			}

			$list_url = add_query_arg( $list_query_args, admin_url( 'admin.php' ) );

			wp_redirect( $list_url );
			exit;

		}

		public function data_table(){

			global $wpdb;

			$table = new YITH_WFBT_Custom_Table( array(
				'singular' => __( 'Main list of products', 'yith-woocommerce-frequently-bought-together' ),
				'plural'   => __( 'Main lists of products', 'yith-woocommerce-frequently-bought-together' )
			) );

			$table->options = array(
				'select_table'     => $wpdb->prefix . 'postmeta a',
				'select_columns'   => array(
					'a.post_id',
					'a.meta_value'
				),
				'select_where'     => 'a.meta_key = "' . YITH_WFBT_META . '" AND a.meta_value NOT LIKE "a:0:{}"',
				'select_group'     => 'a.post_id',
				'select_order'     => 'a.post_id',
				'select_limit'     => 10,
				'count_table'      => '( SELECT COUNT(*) FROM ' . $wpdb->prefix . 'postmeta a WHERE a.meta_key = "' . YITH_WFBT_META . '" AND a.meta_value NOT LIKE "a:0:{}" GROUP BY a.post_id ) AS count_table',
				'key_column'       => 'post_id',
				'view_columns'     => array(
					'cb'        => '<input type="checkbox" />',
					'product'   => __( 'Product', 'yith-woocommerce-frequently-bought-together' ),
					'thumb'     => __( 'Thumbnail', 'yith-woocommerce-frequently-bought-together' ),
					'linked'    => __( 'Amount of linked products', 'yith-woocommerce-frequently-bought-together' ),
					'actions'   => __( 'Actions', 'yith-woocommerce-frequently-bought-together' )
				),
				'hidden_columns'   => array(),
				'sortable_columns' => array(
					'product' => array( 'post_title', true )
				),
				'custom_columns'   => array(
					'column_product' => function ( $item, $me, $product ) {

						$product_query_args = array(
							'post'   => $item['post_id'],
							'action' => 'edit'
						);
						$product_url        = add_query_arg( $product_query_args, admin_url( 'post.php' ) );

						return sprintf( '<strong><a class="tips" target="_blank" href="%s" data-tip="%s">%s</a></strong>', esc_url( $product_url ), __( 'Edit product', 'yith-woocommerce-frequently-bought-together' ), get_the_title( $item['post_id'] ) );
					},
					'column_thumb'   => function ( $item, $me, $product ) {

						return get_the_post_thumbnail( $item['post_id'], 'shop_thumbnail' );
					},
					'column_linked'   => function ( $item, $me, $product ) {

						$view_query_args = array(
							'page'      => $_GET['page'],
							'tab'       => $_GET['tab'],
							'view'      => 'linked',
							'post_id'   => $item['post_id']
						);
						$view_url   = add_query_arg( $view_query_args, admin_url( 'admin.php' ) );
						$linked     = maybe_unserialize( $item['meta_value'] );

						return '<a href="' . esc_url( $view_url ) . '">' . count( $linked ) . '</a>';
					},
					'column_actions'  => function ( $item, $me, $product ) {

						$delete_query_args = array(
							'page'   => $_GET['page'],
							'tab'    => $_GET['tab'],
							'action' => 'delete',
							'id'     => $item['post_id']
						);
						$delete_url        = add_query_arg( $delete_query_args, admin_url( 'admin.php' ) );
						$actions_button    = '<a href="' . esc_url( $delete_url ) . '" class="button">' . __( 'Delete All', 'yith-woocommerce-frequently-bought-together' ) . '</a>';

						$view_query_args = array(
							'page'      => $_GET['page'],
							'tab'       => $_GET['tab'],
							'view'      => 'linked',
							'post_id'   => $item['post_id']
						);
						$view_url        = add_query_arg( $view_query_args, admin_url( 'admin.php' ) );
						$actions_button .= '<a href="' . esc_url( $view_url ) . '" class="button">' . __( 'View Linked', 'yith-woocommerce-frequently-bought-together' ) . '</a>';

						return $actions_button;
					}
				),
				'bulk_actions'     => array(
					'actions'   => array(
						'delete' => __( 'Delete', 'yith-woocommerce-frequently-bought-together' )
					)
				),
			);

			return $table;
		}

		public function linked_table() {

			global $wpdb;

			$table = new YITH_WFBT_Custom_Table( array(
				'singular' => __( 'linked', 'yith-woocommerce-frequently-bought-together' ),
				'plural'   => __( 'linked', 'yith-woocommerce-frequently-bought-together' )
			) );

			$table->options = array(
				'select_table'     => $wpdb->prefix . 'postmeta a',
				'select_columns'   => array(
					'a.meta_value AS post_id'
				),
				'select_where'     => 'a.meta_key = "' . YITH_WFBT_META . '" AND a.post_id = "' . $_GET['post_id'] . '"',
				'select_limit'     => 10,
				'key_column'       => 'post_id',
				'unserialized'     => true,
				'get_product'      => true,
				'view_columns'     => array(
					'cb'            => '<input type="checkbox" />',
					'product'       => __( 'Product', 'yith-woocommerce-frequently-bought-together' ),
					'variation'     => __( 'Variation', 'yith-woocommerce-frequently-bought-together' ),
					'thumb'         => __( 'Thumbnail', 'yith-woocommerce-frequently-bought-together' ),
					'status'        => __( 'Stock Status', 'yith-woocommerce-frequently-bought-together' ),
					'actions'       => __( 'Actions', 'yith-woocommerce-frequently-bought-together' ),
				),
				'hidden_columns'   => array(),
				'sortable_columns' => array(),
				'custom_columns'   => array(
					'column_product'     => function ( $item, $me, $product ) {

						$product_query_args = array(
							'post'   => $product->id,
							'action' => 'edit'
						);
						$product_url        = add_query_arg( $product_query_args, admin_url( 'post.php' ) );

						return sprintf( '<strong><a class="tips" target="_blank" href="%s" data-tip="%s">%s</a></strong>', esc_url( $product_url ), __( 'Edit product', 'yith-woocommerce-frequently-bought-together' ), $product->get_title() );
					},
					'column_variation' => function ( $item, $me, $product ) {

						if( $product->product_type == 'variation' ) {

							$variations = $product->get_variation_attributes();

							$html = '<ul>';

							foreach( $variations as $key => $value ) {
								$key = ucfirst( str_replace( 'attribute_pa_' , '', $key ) );
								$html .= '<li>' . $key . ': ' . $value . '</li>';
							}

							$html .= '</ul>';

							echo $html;
						}
						else {
							echo '-';
						}
					},
					'column_thumb'   => function ( $item, $me, $product ) {
						return get_the_post_thumbnail( $item['post_id'], 'shop_thumbnail' );
					},
					'column_status'  => function ( $item, $me, $product ) {

						$status = $product->get_availability();

						return ( $status['availability'] != '' ) ? '<span class="' . $status['class'] . '">' . $status['availability'] . '</span>' : ' - ';
					},
					'column_actions'   => function ( $item, $me, $product ) {

						$delete_query_args = array(
							'page'      => $_GET['page'],
							'tab'       => $_GET['tab'],
							'view'      => 'linked',
							'action'    => 'remove_linked',
							'post_id'   => $_GET['post_id'],
							'id'        => $item['post_id']
						);
						$delete_url        = add_query_arg( $delete_query_args, admin_url( 'admin.php' ) );

						return '<a href="' . esc_url( $delete_url ) . '" class="button">' . __( 'Delete', 'yith-woocommerce-frequently-bought-together' ) . '</a>';
					}
				),
				'bulk_actions'     => array(
					'actions'   => array(
						'remove_linked' => __( 'Delete', 'yith-woocommerce-frequently-bought-together' )
					)
				),
			);

			return $table;
		}
	}
}
/**
 * Unique access to instance of YITH_WFBT_Data_Table class
 *
 * @return \YITH_WFBT_Data_Table
 * @since 1.0.0
 */
function YITH_WFBT_Data_Table(){
	return YITH_WFBT_Data_Table::get_instance();
}