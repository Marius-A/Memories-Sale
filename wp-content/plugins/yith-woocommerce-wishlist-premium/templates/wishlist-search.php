<?php
/**
 * Wishlist search template
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.5
 */

if ( ! defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly
?>

<form id="yith-wcwl-form" action="<?php echo esc_url( YITH_WCWL()->get_wishlist_url( 'search' ) ) ?>" method="post">
    <!-- TITLE -->
    <?php
    do_action( 'yith_wcwl_before_wishlist_title' );

    if( ! empty( $page_title ) ) {
        echo apply_filters( 'yith_wcwl_wishlist_title', '<h2>' . $page_title . '</h2>' );
    }

    do_action( 'yith_wcwl_before_wishlist_search' );
    ?>

    <div class="yith-wcwl-wishlist-search-form">

        <input type="text" name="wishlist_search" id="wishlist_search" placeholder="<?php _e( 'Type a name or an email address', 'yith-woocommerce-wishlist' ) ?>" value="<?php echo $search_string ?>" />
        <button class="wishlist-search-button">
            <?php echo apply_filters( 'yith_wcwl_search_button_icon', '<i class="icon-search"></i>' ) ?>
            <?php _e( 'Search', 'yith-woocommerce-wishlist' ) ?>
        </button>

    </div>

    <?php do_action( 'yith_wcwl_before_wishlist_search_results' ); ?>

    <?php if( ! empty( $search_string ) ): ?>
        <?php if( ! empty( $search_results ) ): ?>
            <ul class="yith-wcwl-search-results">
                <?php foreach( $search_results as $user ): ?>
                    <li class="yith-wcwl-search-result clear">
                        <?php
                            $user_obj = get_user_by( 'id', $user );
                            $avatar = get_avatar( $user, 70 );
                            $first_name = $user_obj->first_name;
                            $last_name = $user_obj->last_name;
                            $login = $user_obj->user_login;
                            $user_email = $user_obj->user_email;
                            $wishlists = YITH_WCWL()->get_wishlists( array( 'user_id' => $user, 'wishlist_visibility' => 'public' ) );
                        ?>
                        <div class="reuslt-details">
                            <div class="thumb">
                                <?php echo $avatar ?>
                            </div>
                            <div class="user-details">
                                <span class="name">
                                <?php
                                if( ! empty( $first_name ) || ! empty( $last_name ) ) {
                                    echo $first_name . " " . $last_name;
                                }
                                else{
                                    echo $login;
                                }
                                ?>
                                </span>
                            </div>
                        </div>
                        <div class="result-wishlists">
                            <?php _e( 'User\'s wishlists:', 'yith-woocommerce-wishlist' )?>
                            <ul class="user-wishlists">
                                <li class="user-wishlist">
                                    <a title="<?php echo $default_wishlist_title ?>" class="wishlist-anchor" href="<?php echo YITH_WCWL()->get_wishlist_url( 'user' . '/' . $user ) ?>"><?php echo $default_wishlist_title ?></a>
                                </li>
                                <?php if( ! empty( $wishlists ) ): ?>
                                    <?php foreach( $wishlists as $wishlist ): ?>
                                        <?php if( ! $wishlist['is_default'] ): ?>
                                            <li class="user-wishlist">
                                                <a title="<?php echo $wishlist['wishlist_name'] ?>" class="wishlist-anchor" href="<?php echo YITH_WCWL()->get_wishlist_url( 'view' . '/' . $wishlist['wishlist_token'] ) ?>"><?php echo $wishlist['wishlist_name'] ?></a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="yith-wcwl-search-pagination">
                <?php
                if( isset( $pages_links ) ){
                    echo $pages_links;
                }
                ?>
            </div>
        <?php else: ?>
            <p class="yith-wcwl-empty-search-result">
                <?php echo sprintf( __( '0 results for "%s" in Wishlist', 'yith-woocommerce-wishlist' ), $search_string ); ?>
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    do_action( 'yith_wcwl_after_wishlist_search_results' );

    do_action( 'yith_wcwl_after_wishlist_search' );
    ?>
</form>