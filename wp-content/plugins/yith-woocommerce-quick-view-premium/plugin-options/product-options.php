<?php

$link_wczm = '<a href="http://wordpress.org/plugins/yith-woocommerce-zoom-magnifier/">YITH WooCommerce Zoom Magnifier</a>';

$product_settings = array(

	'product'  => array(

		10 => array(
			'title' => __( 'Content Options', 'yith-woocommerce-quick-view' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcqv-content-options'
		),

		20 => array(
			'id'            => 'yith-wcqv-product-show-thumb',
			'name'          => __( 'Select Element to Show', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Show Product Image', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => 'start'
		),

		30 => array(
			'id'            => 'yith-wcqv-product-show-title',
			'desc'          => __( 'Show Product Name', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => ''
		),

		40 => array(
			'id'            => 'yith-wcqv-product-show-rating',
			'desc'          => __( 'Show Product Rating', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => ''
		),

		50 => array(
			'id'            => 'yith-wcqv-product-show-price',
			'desc'          => __( 'Show Product Price', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => ''
		),

		60 => array(
			'id'            => 'yith-wcqv-product-show-excerpt',
			'desc'          => __( 'Show Product Excerpt', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => ''
		),

		70 => array(
			'id'            => 'yith-wcqv-product-show-add-to-cart',
			'desc'          => __( 'Show Product Add To Cart', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => ''
		),

		72 => ( defined( 'YITH_WCWL' ) && YITH_WCWL ) ? array(
				'id'            => 'yith-wcqv-product-show-wishlist',
				'desc'          => __( 'Show Wishlist Button', 'yith-woocommerce-quick-view' ),
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => ''
		) : false,

		74 => ( defined( 'YITH_WOOCOMPARE' ) && YITH_WOOCOMPARE ) ? array(
				'id'            => 'yith-wcqv-product-show-compare',
				'desc'          => __( 'Show Compare Button', 'yith-woocommerce-quick-view' ),
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => ''
		) : false,

		76 => ( defined( 'YITH_YWRAQ_VERSION' ) ) ? array(
				'id'            => 'yith-wcqv-product-show-quote',
				'desc'          => __( 'Show Request Quote Button', 'yith-woocommerce-quick-view' ),
				'type'          => 'checkbox',
				'default'       => 'yes',
				'checkboxgroup' => ''
		) : false,

		80 => array(
			'id'            => 'yith-wcqv-product-show-meta',
			'desc'          => __( 'Show Product Meta', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'yes',
			'checkboxgroup' => 'end'
		),

		90 => array(
			'id'            => 'yith-wcqv-product-full-description',
			'name'          => __( 'Show full description', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Show full description instead of short description', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		100 => array(
			'id'       => 'yith-quick-view-product-image-width',
			'title'    => __( 'Product Image Width', 'yith-woocommerce-quick-view' ),
			'desc'     => __( 'Set width of product image.', 'yith-woocommerce-quick-view' ),
			'type'     => 'number',
			'default'  => 500,
			'custom_attributes' => array( 'min' => 1 )
		),
		110 => array(
			'id'       => 'yith-quick-view-product-image-height',
			'title'    => __( 'Product Image Height', 'yith-woocommerce-quick-view' ),
			'desc'     => __( 'Set height of product image.', 'yith-woocommerce-quick-view' ),
			'type'     => 'number',
			'default'  => 500,
			'custom_attributes' => array( 'min' => 1 )
		),

		120 => array(
			'id'            => 'yith-wcqv-product-images-mode',
			'name'          => __( 'Select Thumbnails Type', 'yith-woocommerce-quick-view' ),
			'type'          => 'select',
			'options'       => array(
				'none'      => __( 'Don\'t show', 'yith-woocommerce-quick-view' ),
				'slider'    => __( 'Slider mode', 'yith-woocommerce-quick-view' ),
				'classic'   => __( 'Classic mode', 'yith-woocommerce-quick-view' )
			),
			'default'   => 'classic',
			'class'           => 'yith-wcqv-chosen'
		),

		130 => array(
			'id'            => 'yith-wcqv-custom-images-html',
			'name'          => __( 'Use custom HTML thumbnails', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Use custom HTML thumbnails instead of WooCommerce standard ones.', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		140 => array(
			'id'            => 'yith-wcqv-enable-zoom-magnifier',
			'name'          => __( 'Enable image zoom', 'yith-woocommerce-quick-view' ),
			'desc'          => sprintf( __( 'Enable the plugin YITH WooCommerce Zoom Magnifier on quick view (not available for slider mode). You need %s installed.', 'yith-woocommerce-quick-view' ), $link_wczm ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		150 => array(
			'id'            => 'yith-wcqv-details-button',
			'name'          => __( 'Add \'View Details\' Button', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Check this option to add a button to go to the single product page.', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		160 => array(
			'id'            => 'yith-wcqv-details-button-label',
			'name'          => __( '\'View Details\' Button Label', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Set label for \'View Details\' button', 'yith-woocommerce-quick-view' ),
			'type'          => 'text',
			'default'       => __( 'View Details', 'yith-woocommerce-quick-view' ),
			'custom_attributes' => array(
				'data-deps'			=> 'yith-wcqv-details-button',
				'data-deps_value'	=> 'yes'
			)
		),

		170 => array(
			'id'            => 'yith-wcqv-ajax-add-to-cart',
			'name'          => __( 'Enable Ajax Add To Cart', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Check this option to enable add to cart in ajax', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		180 => array(
			'id'            => 'yith-wcqv-ajax-redirect-to-checkout',
			'name'          => __( 'Redirect to checkout after add to cart', 'yith-woocommerce-quick-view' ),
			'desc'          => __( 'Check this option to redirect to checkout after add to cart', 'yith-woocommerce-quick-view' ),
			'type'          => 'checkbox',
			'default'       => 'no'
		),

		190 => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcqv-product-options-end'
		),

		200 => array(
			'title' => __( 'Share Options', 'yith-woocommerce-quick-view' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcqv-share-options'
		),

		210 => array(
			'id'        => 'yith-wcqv-enable-share',
			'name'      => __( 'Enable Share', 'yith-woocommerce-quick-view' ),
			'desc'      => __( 'Check this option if you want to show the share link for products in quick view', 'yith-woocommerce-quick-view' ),
			'type'      => 'checkbox',
			'default'   => 'yes',
		),

		220 => array(
			'id'              => 'yith-wcqv-share-socials',
			'name'            => __( 'Select Socials', 'yith-woocommerce-quick-view' ),
			'type'            => 'multiselect',
			'options'         => array(
				'facebook'      => __( 'Facebook', 'yith-woocommerce-quick-view' ),
				'twitter'       => __( 'Twitter', 'yith-woocommerce-quick-view' ),
				'google-plus'   => __( 'Google+', 'yith-woocommerce-quick-view' ),
				'pinterest'     => __( 'Pinterest', 'yith-woocommerce-quick-view' ),
				'mail'          => __( 'eMail', 'yith-woocommerce-quick-view' ),
			),
			'class'           => 'yith-wcqv-chosen',
			'default'         => array( 'facebook', 'twitter', 'google-plus', 'pinterest', 'mail' )
		),

        230 => array(
            'id'        => 'yith-wcqv-facebook-appid',
            'name'      => __( 'Facebook App ID', 'yith-woocommerce-quick-view' ),
            'desc'      => sprintf( __( 'Facebook App ID necessary to share contents. Read more in the official Facebook <a href="%s">documentation</a>', 'yith-woocommerce-quick-view' ), 'https://developers.facebook.com/docs/apps/register' ),
            'type'      => 'text',
            'std'       => '',
            'default'   => ''
        ),

		240 => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcqv-share-options-end'
		),

	)
);

return apply_filters( 'yith_wcqv_panel_product_settings', $product_settings );