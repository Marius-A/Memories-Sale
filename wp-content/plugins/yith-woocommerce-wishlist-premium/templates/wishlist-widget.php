<?php
/**
 * Wishlist list widget
 *
 * @author Your Inspiration Themes
 * @package YITH WooCommerce Wishlist
 * @version 2.0.0
 */

if ( ! defined( 'YITH_WCWL' ) ) {
    exit;
} // Exit if accessed directly
?>

<?php echo apply_filters( 'yith_wcwl_before_wishlist_widget', $before_widget ); ?>

<?php if( ! empty( $instance['title'] ) ): ?>
    <h3 class="widget-title"><?php echo $instance['title']?></h3>
<?php endif; ?>

    <?php if( ! empty( $instance['wishlist_link'] ) ): ?>
        <a href="<?php echo $wishlist_url ?>" class="<?php echo apply_filters( 'yith_wcwl_widget_dropdown_toggle_classes', 'wishlist-dropdown-toggle' )?>" title="<?php echo $instance['wishlist_link'] ?>"><?php echo $instance['wishlist_link'] ?></a>
    <?php endif; ?>

    <ul class="<?php echo apply_filters( 'yith_wcwl_widget_main_ul_classes', 'dropdown' )?>">
        <li class="<?php echo apply_filters( 'yith_wcwl_widget_li_classes', 'dropdown-section lists-section' )?>">
            <ul class="<?php echo apply_filters( 'yith_wcwl_widget_list_ul_classes', 'lists' )?>">
                <li class="<?php echo apply_filters( 'yith_wcwl_widget_default_wishlist_classes', 'default-list list' ) ?> <?php echo ( $active == 'wishlist' && $current_wishlist == 'default' ) ? 'current' : ''?>">
                    <a title="<?php echo $default_wishlist_title ?>" class="wishlist-anchor" href="<?php echo $user_logged_in ? YITH_WCWL()->get_wishlist_url( 'user' . '/' . $current_user_id ) : YITH_WCWL()->get_wishlist_url() ?>">
                        <?php echo $default_wishlist_title ?>
                    </a>
                </li>

                <?php if( $user_logged_in && $multiple_wishlist_enabled && ! empty( $users_wishlists ) ): ?>
                    <?php foreach( $users_wishlists as $wishlist ): ?>
                        <?php if( ! $wishlist['is_default'] ): ?>
                            <li class="<?php echo apply_filters( 'yith_wcwl_widget_wishlist_classes', 'list' ) ?> <?php echo ( $active == 'wishlist' && $current_wishlist == $wishlist['wishlist_token'] ) ? 'current' : ''?>">
                                <a title="<?php echo $wishlist['wishlist_name'] ?>" class="wishlist-anchor" href="<?php echo YITH_WCWL()->get_wishlist_url( 'view' . '/' . $wishlist['wishlist_token'] ) ?>">
                                    <?php echo $wishlist['wishlist_name'] ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </li>

        <?php if( isset( $instance['show_create_link'] ) && $instance['show_create_link'] == 'yes' && $user_logged_in && $multiple_wishlist_enabled ): ?>
            <li class="<?php echo apply_filters( 'yith_wcwl_widget_li_classes', 'dropdown-section' )?> <?php echo ( $active == 'create' ) ? 'current' : ''?>">
                <a href="<?php echo YITH_WCWL()->get_wishlist_url( 'create' ) ?>" title="<?php echo $create_page_title ?>"><?php echo $create_page_title ?></a>
            </li>
        <?php endif; ?>

        <?php if( isset( $instance['show_search_link'] ) && $instance['show_search_link'] == 'yes' ): ?>
            <li class="<?php echo apply_filters( 'yith_wcwl_widget_li_classes', 'dropdown-section' )?> <?php echo ( $active == 'search' ) ? 'current' : ''?>">
                <a href="<?php echo YITH_WCWL()->get_wishlist_url( 'search' ) ?>" title="<?php echo $search_page_title ?>"><?php echo $search_page_title ?></a>
            </li>
        <?php endif; ?>

        <?php if( isset( $instance['show_manage_link'] ) && $instance['show_manage_link'] == 'yes' && $user_logged_in && $multiple_wishlist_enabled ): ?>
            <li class="<?php echo apply_filters( 'yith_wcwl_widget_li_classes', 'dropdown-section' )?> <?php echo ( $active == 'manage' ) ? 'current' : ''?>">
                <a href="<?php echo YITH_WCWL()->get_wishlist_url( 'manage' ) ?>" title="<?php echo $manage_page_title ?>"><?php echo $manage_page_title ?></a>
            </li>
        <?php endif; ?>

    </ul>

<?php echo apply_filters( 'yith_wcwl_after_wishlist_widget', $after_widget ); ?>