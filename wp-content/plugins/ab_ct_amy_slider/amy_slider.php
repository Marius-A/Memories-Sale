<?php
/*
Plugin Name: AMY Slider for Visual Composer
Description: Adds AMY Slider to your VC
Author: Andrey Boyadzhiev - Cray Themes
Version: 2.1
Author URI: http://themes.cray.bg
Plugin URI: http://themeforest.net/user/CrayThemes/portfolio 
Text Domain: amy-vc-slider
*/
define('ab_ct_amy_slider',plugin_basename( __FILE__ ));
define ('ab_ct_amy_slider_url',plugins_url('', __FILE__));
if (!class_exists('ab_ct_amy_slider_class')) {
	class ab_ct_amy_slider_class{
		function __construct(){
			add_action( 'after_setup_theme', array($this,'ab_ct_amy_begin' ),1);
		}
		function ab_ct_amy_begin(){
            if(get_template() == 'amytheme'){
                $js_comp = 'js_composer_amy/js_composer.php';
            }else{
                $js_comp = 'js_composer/js_composer.php';
            }
			if ( in_array( $js_comp , apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
				$required_vc = '4.2';
				if(defined('WPB_VC_VERSION')){
					if( version_compare( $required_vc, WPB_VC_VERSION, '<' )){
                        if(get_template() == 'amytheme'){
						  require_once dirname(__FILE__). '/../js_composer_amy/include/classes/shortcodes/vc-posts-grid.php';
                        }else{
                            require_once dirname(__FILE__). '/../js_composer/include/classes/shortcodes/vc-posts-grid.php';
                        }
					}else{
						require_once dirname(__FILE__). '/../js_composer/composer/lib/shortcodes/posts_grid.php';
					}
					ab_ct_amy_continue();
				}
			}else{
				$required_vc = '4.2';
				if(defined('WPB_VC_VERSION')){
					if( version_compare( $required_vc, WPB_VC_VERSION, '<' )){
						require_once get_template_directory().'/wpbakery/js_composer/include/classes/shortcodes/vc-posts-grid.php';
					}else{
						require_once get_template_directory().'/wpbakery/js_composer/composer/lib/shortcodes/posts_grid.php';
					}
					ab_ct_amy_continue();
				}else{
					add_action( 'admin_notices', array($this,'ab_ct_amy_slider_notice'));
				}
			}
		}
		function ab_ct_amy_slider_notice(){
			echo '<div class="updated"><p>The <strong>AMY Slider</strong> Visual Composer add-on requires <strong> Visual Composer</strong> plugin installed and activated.</p><img src="http://www.ten28.com/sde.jpg"></div>';
		}
	}
	new ab_ct_amy_slider_class;
}

function ab_ct_amy_continue(){
	if (!class_exists('ab_ct_amy_slider_scripts')) {
		class ab_ct_amy_slider_scripts extends WPBakeryShortCode {
			public function __construct() {
				add_action('add_meta_boxes', 'ct_amy_meta_box');
				add_action('save_post', 'ct_amy_save_meta_box');
                set_post_thumbnail_size(350, 460, true);
				include_once( 'inc/amy_meta_box.php' );
				add_action( 'wp_enqueue_scripts', array( $this, 'ct_amy_enq_scr' ) );
                add_action( 'admin_enqueue_scripts', 'ct_amy_load_custom_wp_admin_js' );  
				$this->includes();
			}
			function includes(){
				$required_vc = '3.6';
				if(defined('WPB_VC_VERSION')){
					if( version_compare( $required_vc, WPB_VC_VERSION, '>' )){
						add_action( 'admin_notices', array($this, 'ab_ct_amy_version'));
					}
				}
				include_once( 'inc/amy_templates.php' );
                include_once( 'inc/amy_templates_image.php' );
			}
			//1,14,4,18,5,25,2,15,25,1,4,26,8,9,5,22
			function ab_ct_amy_version(){
				echo '<div class="updated"><p>The <strong>AMY Slider</strong> Visual Composer add-on requires <strong> Visual Composer</strong> version 3.6.0 or higher.</p></div>';
			}
			public function ct_amy_enq_scr(){
				wp_enqueue_style('ct_amy_fontawesome_style', ab_ct_amy_slider_url . '/css/fontawesome/font-awesome.css', array() , null);
				wp_enqueue_style('ct_amy_slider_style', ab_ct_amy_slider_url . '/css/amy_main.css', array() , null);
			}	
		}
		new ab_ct_amy_slider_scripts;
	}
	
	if (!class_exists('ab_ct_amy_slider_extend')) {
		class ab_ct_amy_slider_extend extends WPBakeryShortCode {
			function __construct() {
				add_action( 'after_setup_theme', array( $this, 'createShortcodes' ), 5 );
				add_action( 'init', array( $this, 'integrateWithVC' ) );
				add_shortcode( 'ct_amy_slider', array( $this, 'ct_amy_slider' ) );
                add_action( 'init', 'ct_amy_create_post_type' );    
                //Portflolo custom post type
                function ct_amy_create_post_type() {
                    register_post_type( 'amy_portfolio',
                        array(
                            'labels' => array(
                                'name' => __( 'AMY Slider', 'amytheme' ),
                                'singular_name' => __( 'Amy Slider', 'amytheme' )
                            ),
                        'public'             => true,
                        'publicly_queryable' => true,
                        'show_ui'            => true,
                        'show_in_menu'       => true,
                        'query_var'          => true,
                        'rewrite'            => array( 'slug' => 'amy_portfolio' ),
                        'capability_type'    => 'post',
                        'has_archive'        => true,
                        'taxonomies' => array('category','post_tag'), 
                        'hierarchical'       => false,
                        'menu_position'      => null,
                            'menu_icon'   => 'dashicons-images-alt2',
                            
                        'supports' => array('title','editor','author','thumbnail','excerpt','trackbacks','custom-fields','comments'),

                        )
                    );

                }
			}
			public function integrateWithVC() {
				if ( ! defined( 'WPB_VC_VERSION' ) ) {
					add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
					return;
				}
			}
			public function ct_amy_slider( $atts, $content = null ) {
				extract( shortcode_atts( array(
					'ct_as_title'							  => '',
					'ct_as_query'							  => 'size:5|order_by:date|order:ASC|post_type:posts',
					'ct_as_height'							=> '700px',	
					'ct_as_style'							=> 'ct_amy_circle',
					'ct_as_hoverfx'							=> 'style1',
					'ct_as_thumbsize'						=> '',
					'ct_as_mouse_parallax'					=> '',
					'ct_as_mouse_parallax_depth' 			=> '1.5',
					'ct_as_slideshow'						=> '',
					'ct_as_slideshow_speed'					=> '4000',
					'ct_as_first_slide'						=> '0',
					'ct_as_read_more'						=> '',
					'ct_as_image_thumb_size'				=> '350x450',
					'ct_as_zoomimgfx'						=> '',
					'ct_as_shadow_fx'						=> '',
					'ct_as_img_opa'							=> '',
					'ct_as_border_radius'					=> '0px',
                    /*'ct_as_google_font_titles'              => '',
                    'ct_as_google_fonts'                    => '',*/
                    'ct_as_title_size'                    => '',
					'ct_as_titlelink_color'                 => '#777777',
					'ct_as_titlelinkbg_color'				=> 'rgba(255,255,255,0)',
					'ct_as_titlelinkhover_color'			=> '#888888',
					'ct_as_titlelinkbghover_color'			=> 'rgba(255,255,255,0)',
					'ct_as_woo_price_color'					=> '#777777',
					'ct_as_excerpt_color'					=> '#777777',
					'ct_as_readmore_color'					=> '#777777',
					'ct_as_readmorebg_color'				=> '',
					'ct_as_readmorehover_color'				=> '#ffffff',
					'ct_as_readmorebghover_color'			=> '#444444',
					'ct_as_border_size'						=> '',
					'ct_as_border_color'					=> '#ffffff',
					'ct_as_bg_color'						=> 'rgba(255,255,255,0.95)',
					'ct_bgtop_color'						=> 'rgba(255,255,255,0)',
					'ct_bgbottom_color'						=> 'rgba(255,255,255,0.90)',
					'ct_as_navarrow_color'					=> '#ffffff',
					'ct_as_excerpt_length'				     => '10',
					'ct_as_responsive_style'				=> '',
					'ct_as_responsive_width'				=> '768',
                    'ct_as_lightbox'                        => '',
				
				), $atts ) );
				$content = wpb_js_remove_wpautop($content, true); 
				$GLOBALS['output']="";
				$slider_class = new ct_ab_amy_slider(
					$ct_as_title,
					$ct_as_query,
					$ct_as_height,
					$ct_as_style,
					$ct_as_hoverfx,
					$ct_as_thumbsize,
					$ct_as_mouse_parallax,
					$ct_as_mouse_parallax_depth,
					$ct_as_slideshow,
					$ct_as_slideshow_speed,
					$ct_as_first_slide,
					$ct_as_read_more,
					$ct_as_image_thumb_size,
					$ct_as_zoomimgfx,
					$ct_as_shadow_fx,
					$ct_as_img_opa,
					$ct_as_border_radius,
                    /*$ct_as_google_font_titles,
                    $ct_as_google_fonts,*/
                    $ct_as_title_size,
					$ct_as_titlelink_color,
					$ct_as_titlelinkbg_color,
					$ct_as_titlelinkhover_color,
					$ct_as_titlelinkbghover_color,
					$ct_as_woo_price_color,
					$ct_as_excerpt_color,
					$ct_as_readmore_color,
					$ct_as_readmorebg_color,
					$ct_as_readmorehover_color,
					$ct_as_readmorebghover_color,
					$ct_as_border_size,
					$ct_as_border_color,
					$ct_as_bg_color,
					$ct_bgtop_color,
					$ct_bgbottom_color,
					$ct_as_navarrow_color,
					$ct_as_excerpt_length,
					$ct_as_responsive_style,
					$ct_as_responsive_width,
                    $ct_as_lightbox
				);
				return $GLOBALS['output'];
			}
			public function createShortcodes() {	
				
				$vc_layout_sub_controls = array(
				  array( 'link_post', __( 'Link to post', "js_composer"  ) ),
				  array( 'no_link', __( 'No link', "js_composer"  ) ),
				  array( 'link_image', __( 'Link to bigger image', "js_composer"  ) )
				);
				
				vc_map( array(
					"name" => __("AMY Slider", "js_composer" ),
					"description" => __("Creative post, page, woocommerce slider", "js_composer" ),
					"base" => "ct_amy_slider",
					"class" => "",
					"controls" => "full",
					"icon" => ab_ct_amy_slider_url.'/images/amyslider.png',
					"category" => __('Cray Themes', "js_composer" ),
					"params" => array(
					
						array(
						  "type" => "loop",
						  "heading" => __("AMY Slider content", "js_composer"),
						  "param_name" => "ct_as_query", //tova e imeto na queryto
						  "settings" => array(
							  "size" => array( "hidden" => false, "value" => 10 ),
							  "order_by" => array( "value" => "date" ),
							),
							"description" => __("Create WordPress loop, to populate content from your site.", "js_composer")
						),
						
						array(
						  "type" => "textfield",
						  "heading" => __('Slider height', 'wpb'),
						  "param_name" => "ct_as_height",
						  "value" => '700px',
						  "description" => __("You can use px, em, etc. or enter just number and it will use pixels. ", "wpb"),
						),
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Slider Scrolling Style", "js_composer"),
						  "param_name" => "ct_as_style",
						  "value" => array(
						  __("Circle", "js_composer") => "ct_amy_circle", 
						  __("Cube", "js_composer") => "ct_amy_cube", 
						  __("Carousel", "js_composer") => "ct_amy_carousel", 
						  __("Concave", "js_composer") => "ct_amy_concave", 
						  __("Coverflow", "js_composer") => "ct_amy_coverflow",
						  __("Coverflow 2D", "js_composer") => "ct_amy_coverflow2d",
						  __("Spiral top", "js_composer") => "ct_amy_spiraltop",
						  __("Spiral bottom", "js_composer") => "ct_amy_spiralbottom",
						  __("Classic", "js_composer") => "ct_amy_classic"),
						  "description" => __("Select scrolling style.", "js_composer"),
						  "admin_label" => true
						),
			
						array(
						  "type" => "dropdown",
						  "heading" => __("Slide(tile) Style ", "js_composer"),
						  "param_name" => "ct_as_hoverfx",
						  "value" => array(
						  __("Style 1 (Support WooCommerce)", "js_composer") => "style1", 
						  __("Style 2 (Support WooCommerce)", "js_composer") => "style2",
						  __("Style 3 (Support WooCommerce)", "js_composer") => "style3",
						  __("Style 4 (Support WooCommerce)", "js_composer") => "style4",
						  __("Style 5 (Support WooCommerce)", "js_composer") => "style5",
						  __("Style 6 (Support WooCommerce)", "js_composer") => "style6",
						  __("Style 7 (Support WooCommerce)", "js_composer") => "style7",
						  ),
						  "description" => __("Select slide style.", "js_composer"),
						  "admin_label" => true
						),
			
						array(
						  "type" => "dropdown",
						  "heading" => __("Zoom Image Effect on Hover", "js_composer"),
						  "param_name" => "ct_as_zoomimgfx",
						  "value" => array(
						  __("Zoom In", "js_composer") => "ct_as_zoominimg", 
						  __("Zoom Out", "js_composer") => "ct_as_zoomoutimg",
						  __("No Zoom", "js_composer") => "ct_as_nozoomimg"
						  ),
						  "description" => __("Select image hover effect zoom.", "js_composer"),
						  "admin_label" => true
						),
			
						array(
						  "type" => 'checkbox',
						  "heading" => __("Tile bottom shadow", "js_composer"),
						  "param_name" => "ct_as_shadow_fx",
						  "description" => __("Adds each slide bottom shadow", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'ct_as_shadow_fx'),
						),
						
						array(
						  "type" => 'checkbox',
						  "heading" => __("Bigger thumbs", "js_composer"),
						  "param_name" => "ct_as_thumbsize",
						  "description" => __("Larger thumb size", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'bigthumpsize'),
						),
						
						array(
						  "type" => 'checkbox',
						  "heading" => __("Mouse parallax", "js_composer"),
						  "param_name" => "ct_as_mouse_parallax",
						  "description" => __("Activate mouse parallax", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'yes'),
						  "admin_label" => true
						),
						
						array(
						  "type" => "textfield",
						  "heading" => __("Mouse parallax depth", "js_composer"),
						  "param_name" => "ct_as_mouse_parallax_depth",
						  "value" => "1",
						  "description" => __("From 0.3 to 1.5 is recommended", "js_composer")
						),
			
						array(
						  "type" => 'checkbox',
						  "heading" => __("Slideshow", "js_composer"),
						  "param_name" => "ct_as_slideshow",
						  "description" => __("Activate slideshow", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'yes'),
						  "admin_label" => true
						),
			  
                      
						array(
						  "type" => "textfield",
						  "heading" => __("Slideshow speed", "js_composer"),
						  "param_name" => "ct_as_slideshow_speed",
						  "value" => "4000",
						  "description" => __("Duration of animation between slides (in ms)", "js_composer"),
						),
				
						array(
						  "type" => "textfield",
						  "heading" => __("Select first slide", "js_composer"),
						  "param_name" => "ct_as_first_slide",
						  "value" => "0",
						  "description" => __("Select which slide to be on focus when slider is loaded(number).", "js_composer"),
						),
				
						array(
						  "type" => "textfield",
						  "heading" => __("Read More text", "js_composer"),
						  "param_name" => "ct_as_read_more",
						  "value" => "Read more",
						  "description" => __("Read more link text. You can try HTML arrows http://character-code.com/arrows-html-codes.php", "js_composer"),
						),
							
						array(
							"type" => "textfield",
							"class" => "",
							"heading" => __("Excerpt/Content Length", "js_composer" ),
							"param_name" => "ct_as_excerpt_length",
							"value" => '10',
							"description" => __("Enter number of Excerpt length", "js_composer" ),
							"admin_label" => true
								
						),
						
						array(
							'type' => 'textfield',
							'heading' => __( 'Thumbnail size', "js_composer"  ),
							'param_name' => 'ct_as_image_thumb_size',
							'value'	=> '350x450',
							'description' => __( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height) . ', "js_composer"  ),
							
						),
						
						array(
							"type" => "textfield",
							"heading" => __("Slide(Tile) Border radius", "js_composer"),
							"param_name" => "ct_as_border_radius",
							"value" => "0px",
							"description" => __("Set border radius in pixels or %", "js_composer"),
						),
                        
                        array(
							"type" => "textfield",
							"heading" => __("Custom titles font size", "js_composer"),
							"param_name" => "ct_as_title_size",
							"value" => "",
							"description" => __("Overwrite theme H2 heading font size.(examp: 30px) ", "js_composer"),
						),
                        
                        
                         
									
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Title Color", "js_composer" ),
							"param_name" => "ct_as_titlelink_color",
							"value" => '',
							"description" => __("Choose title color.Leave blank to ignore", "js_composer" ),							
						),
							
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Title Background Color", "js_composer" ),
							"param_name" => "ct_as_titlelinkbg_color",
							"value" => '',
							"description" => __("Choose title background color. Leave blank to ignore", "js_composer" ),							
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Title Color on Hover", "js_composer" ),
							"param_name" => "ct_as_titlelinkhover_color",
							"value" => '',
							"description" => __("Choose title color on hover.Leave blank to ignore", "js_composer" ),							
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Title Background Color on Hover", "js_composer" ),
							"param_name" => "ct_as_titlelinkbghover_color",
							"value" => '',
							"description" => __("Choose title background color on hover. Leave blank to ignore", "js_composer" ),							
						),
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("WooCmmerce Product Price Color", "js_composer" ),
							"param_name" => "ct_as_woo_price_color",
							"value" => '',
							"description" => __("Choose WooCommeerce price color.Leave blank to ignore", "js_composer" ),						
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Excerpt Color", "js_composer" ),
							"param_name" => "ct_as_excerpt_color",
							"value" => '',
							"description" => __("Choose Excerpt color.Leave blank to ignore", "js_composer" ),						
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Read More Text Color", "js_composer" ),
							"param_name" => "ct_as_readmore_color",
							"value" => '',
							"description" => __("Choose text color.Leave blank to ignore", "js_composer" ),						
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Read More Background Color", "js_composer" ),
							"param_name" => "ct_as_readmorebg_color",
							"value" => '',
							"description" => __("Choose background color.Leave blank to ignore", "js_composer" ),
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Read More Text Color on Hover", "js_composer" ),
							"param_name" => "ct_as_readmorehover_color",
							"value" => '',
							"description" => __("Choose text color on hover.Leave blank to ignore", "js_composer" ),	
						),
							
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Read More Background Color on Hover", "js_composer" ),
							"param_name" => "ct_as_readmorebghover_color",
							"value" => '',
							"description" => __("Choose background color on hover.Leave blank to ignore", "js_composer" ),					
							
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Backgroud Top Gradient ", "js_composer" ),
							"param_name" => "ct_bgtop_color",
							"value" => '',
							"description" => __("Choose top background color gradient.Leave blank to ignore", "js_composer" ),
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array( 'style5' )
							),						
						),
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Backgroud Bottom Gradient", "js_composer" ),
							"param_name" => "ct_bgbottom_color",
							"value" => '',
							"description" => __("Choose bottom background color gradient.Leave blank to ignore", "js_composer" ), 
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array( 'style5' )
							),				
						),
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Border size", "js_composer"),
						  "param_name" => "ct_as_border_size",
						  "value" => array(
							  __("1px", "js_composer") => "1px", 
							  __("2px", "js_composer") => "2px",
							  __("3px", "js_composer") => "3px",
							  __("4px", "js_composer") => "4px",
							  __("5px", "js_composer") => "5px",
							  __("6px", "js_composer") => "6px",
							  __("7px", "js_composer") => "7px",
						  ),
						  "description" => __("Select exerpt border size", "js_composer"),
						  'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array('style6' , 'style7' )
							),		
						  "admin_label" => true
						),
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Border Color", "js_composer" ),
							"param_name" => "ct_as_border_color",
							"value" => '',
							"description" => __("Select border color.Leave blank to ignore", "js_composer" ),
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array('style6' , 'style7' )
							),	
						),	
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Image opacity", "js_composer"),
						  "param_name" => "ct_as_img_opa",
						  "value" => array(
							  __("100%", "js_composer") => "1", 
							  __("90%", "js_composer") => "0.9",
							  __("80%", "js_composer") => "0.8",
							  __("70%", "js_composer") => "0.7",
							  __("60%", "js_composer") => "0.6",
							  __("50%", "js_composer") => "0.5",
							  __("40%", "js_composer") => "0.4",
							  __("30%", "js_composer") => "0.3",
							  __("20%", "js_composer") => "0.2",
							  __("10%", "js_composer") => "0.1",
							  __("0%", "js_composer") => "0"
						  ),
						  "description" => __("Set image opacity before hover(from 0% to 100%). Use for styles 5,6 and 7.", "js_composer"),
						  'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array('style5' ,'style6' , 'style7' )
							),		
						),
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Background Color", "js_composer" ),
							"param_name" => "ct_as_bg_color",
							"value" => '',
							"description" => __("Choose background color.Leave blank to ignore", "js_composer" ),
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array( 'style1', 'style2', 'style3' , 'style6' , 'style7' )
							),									
							
						),	
						
						array(
							"type" => "colorpicker",
							"holder" => "div",
							"class" => "",
							"heading" => __("Navigation Arrows Color", "js_composer" ),
							"param_name" => "ct_as_navarrow_color",
							"value" => '',
							"description" => __("Choose navigation arrows color.Leave blank to ignore", "js_composer" ),	
							"admin_label" => true
						),
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Responsive style", "js_composer"),
						  "param_name" => "ct_as_responsive_style",
						  "value" => array(
							  __("Grid style", "js_composer") => "responsivegrid", 
							  __("Slider style", "js_composer") => "responsiveslider"
							   
						  ),
						  "description" => __("Select slider responsive style ", "js_composer"),
						  "admin_label" => true
						),
						array(
							"type" => "textfield",
							"heading" => __("Responsive under", "js_composer"),
							"param_name" => "ct_as_responsive_width",
							"value" => "768",
							"description" => __("Select when the slider will become responsive", "js_composer")
						),
                         array(
						  "type" => 'checkbox',
						  "heading" => __("Enable VC lightbox", "js_composer"),
						  "param_name" => "ct_as_lightbox",
						  "description" => __("Turn on VC lightbox if is not already enabled by your current theme ", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'ct_as_shadow_fx'),
						),
					),
				) 
			);				
			}
			public function excerpt($excerpt,$limit) {
				$excerpt = explode(' ', $excerpt, $limit);
				if (count($excerpt)>=$limit) {
					array_pop($excerpt);
					$excerpt = implode(" ",$excerpt).'...';
				} else {
					$excerpt = implode(" ",$excerpt);
				} 
				if($excerpt == '...'){
					$excerpt = '';
				}
				$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
				return $excerpt;
			}
		}
		$GLOBALS['ab_ct_amy_slider_extend'] = new ab_ct_amy_slider_extend;
	}

    if (!class_exists('ab_ct_amy_slider_image_extend')) {
    
		class ab_ct_amy_slider_image_extend extends WPBakeryShortCode {
			function __construct() {
				add_action( 'after_setup_theme', array( $this, 'createShortcodes' ), 5 );
				add_action( 'init', array( $this, 'integrateWithVC' ) );
				add_shortcode( 'ct_amy_slider_image', array( $this, 'ct_amy_slider_image' ) );
			}
			public function integrateWithVC() {
				if ( ! defined( 'WPB_VC_VERSION' ) ) {
					add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
					return;
				}
			}
			public function ct_amy_slider_image( $atts, $content = null ) {
				extract( shortcode_atts( array(
					'ct_as_title'							  => '',
					'ct_as_query'							  => '',
					'ct_as_height'							=> '700px',	
					'ct_as_style'							=> 'ct_amy_circle',
					'ct_as_thumbsize'						=> '',
					'ct_as_mouse_parallax'					=> '',
					'ct_as_mouse_parallax_depth' 			=> '1.5',
					'ct_as_slideshow'						=> '',
					'ct_as_slideshow_speed'					=> '4000',
					'ct_as_first_slide'						=> '0',
					'ct_as_image_thumb_size'				=> '350x450',
					'ct_as_zoomimgfx'						=> '',
					'ct_as_shadow_fx'						=> '',
					'ct_as_img_opa'							=> '',
					'ct_as_border_radius'					=> '0px',
					'ct_as_icon_color'					=> '#fff',
					'ct_as_iconbg_color'				=> 'rgba(40,40,40,0.1)',
					'ct_bgtop_color'						=> 'rgba(40,40,40,0.8)',
					'ct_bgbottom_color'						=> 'rgba(40,40,40,0.8)',
					'ct_as_navarrow_color'					=> '#ffffff',
					'ct_as_responsive_style'				=> '',
					'ct_as_responsive_width'				=> '768',
                    'ct_as_lightbox'				=> '',
				
				), $atts ) );
				$content = wpb_js_remove_wpautop($content, true); 
				$GLOBALS['output']="";
				$slider_class = new ct_ab_amy_slider_image(
					$ct_as_title,
					$ct_as_query,
					$ct_as_height,
					$ct_as_style,
					$ct_as_thumbsize,
					$ct_as_mouse_parallax,
					$ct_as_mouse_parallax_depth,
					$ct_as_slideshow,
					$ct_as_slideshow_speed,
					$ct_as_first_slide,
					$ct_as_image_thumb_size,
					$ct_as_zoomimgfx,
					$ct_as_shadow_fx,
					$ct_as_img_opa,
					$ct_as_border_radius,
					$ct_as_icon_color,
					$ct_as_iconbg_color,
					$ct_bgtop_color,
					$ct_bgbottom_color,
					$ct_as_navarrow_color,
					$ct_as_responsive_style,
					$ct_as_responsive_width,
                    $ct_as_lightbox
				);
				return $GLOBALS['output'];
			}
			public function createShortcodes() {	
				
				$vc_layout_sub_controls = array(
				  array( 'link_post', __( 'Link to post', "js_composer"  ) ),
				  array( 'no_link', __( 'No link', "js_composer"  ) ),
				  array( 'link_image', __( 'Link to bigger image', "js_composer"  ) )
				);
				
				vc_map( array(
					"name" => __("AMY Slider Gallery", "js_composer" ),
					"description" => __("Creative post, page, woocommerce slider", "js_composer" ),
					"base" => "ct_amy_slider_image",
					"class" => "",
					"controls" => "full",
					"icon" => ab_ct_amy_slider_url.'/images/amyslider.png',
					"category" => __('Cray Themes', "js_composer" ),
					"params" => array(
					
						array(
			'type' => 'attach_images',
			'heading' => __( 'Images', 'js_composer' ),
			'param_name' => 'ct_as_query',
			'value' => '',
			'description' => __( 'Select images from media library.', 'js_composer' )
		),
						
						array(
						  "type" => "textfield",
						  "heading" => __('Slider height', 'wpb'),
						  "param_name" => "ct_as_height",
						  "value" => '700px',
						  "description" => __("You can use px, em, etc. or enter just number and it will use pixels. ", "wpb"),
						),
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Slider Scrolling Style", "js_composer"),
						  "param_name" => "ct_as_style",
						  "value" => array(
						  __("Circle", "js_composer") => "ct_amy_circle", 
						  __("Cube", "js_composer") => "ct_amy_cube", 
						  __("Carousel", "js_composer") => "ct_amy_carousel", 
						  __("Concave", "js_composer") => "ct_amy_concave", 
						  __("Coverflow", "js_composer") => "ct_amy_coverflow",
						  __("Coverflow 2D", "js_composer") => "ct_amy_coverflow2d",
						  __("Spiral top", "js_composer") => "ct_amy_spiraltop",
						  __("Spiral bottom", "js_composer") => "ct_amy_spiralbottom",
						  __("Classic", "js_composer") => "ct_amy_classic"),
						  "description" => __("Select scrolling style.", "js_composer"),
						  "admin_label" => true
						),
			
						array(
						  "type" => "dropdown",
						  "heading" => __("Zoom Image Effect on Hover", "js_composer"),
						  "param_name" => "ct_as_zoomimgfx",
						  "value" => array(
						  __("Zoom In", "js_composer") => "ct_as_zoominimg", 
						  __("Zoom Out", "js_composer") => "ct_as_zoomoutimg",
						  __("No Zoom", "js_composer") => "ct_as_nozoomimg"
						  ),
						  "description" => __("Select image hover effect zoom.", "js_composer"),
						  "admin_label" => true
						),
			
						array(
						  "type" => 'checkbox',
						  "heading" => __("Tile bottom shadow", "js_composer"),
						  "param_name" => "ct_as_shadow_fx",
						  "description" => __("Adds each slide bottom shadow", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'ct_as_shadow_fx'),
						),
						
						array(
						  "type" => 'checkbox',
						  "heading" => __("Bigger thumbs", "js_composer"),
						  "param_name" => "ct_as_thumbsize",
						  "description" => __("Larger thumb size", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'bigthumpsize'),
						),
						
						array(
						  "type" => 'checkbox',
						  "heading" => __("Mouse parallax", "js_composer"),
						  "param_name" => "ct_as_mouse_parallax",
						  "description" => __("Activate mouse parallax", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'yes'),
						  "admin_label" => true
						),
						
						array(
						  "type" => "textfield",
						  "heading" => __("Mouse parallax depth", "js_composer"),
						  "param_name" => "ct_as_mouse_parallax_depth",
						  "value" => "1",
						  "description" => __("From 0.3 to 1.5 is recommended", "js_composer")
						),
			
						array(
						  "type" => 'checkbox',
						  "heading" => __("Slideshow", "js_composer"),
						  "param_name" => "ct_as_slideshow",
						  "description" => __("Activate slideshow", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'yes'),
						  "admin_label" => true
						),
			  
						array(
						  "type" => "textfield",
						  "heading" => __("Slideshow speed", "js_composer"),
						  "param_name" => "ct_as_slideshow_speed",
						  "value" => "4000",
						  "description" => __("Duration of animation between slides (in ms)", "js_composer"),
						),
				
						array(
						  "type" => "textfield",
						  "heading" => __("Select first slide", "js_composer"),
						  "param_name" => "ct_as_first_slide",
						  "value" => "0",
						  "description" => __("Select which slide to be on focus when slider is loaded(number).", "js_composer"),
						),
						
						array(
							'type' => 'textfield',
							'heading' => __( 'Thumbnail size', "js_composer"  ),
							'param_name' => 'ct_as_image_thumb_size',
							'value'	=> '350x450',
							'description' => __( 'Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height) . ', "js_composer"  ),
							
						),
						
						array(
							"type" => "textfield",
							"heading" => __("Slide(Tile) Border Radius", "js_composer"),
							"param_name" => "ct_as_border_radius",
							"value" => "0px",
							"description" => __("Set border radius in pixels or %", "js_composer"),
						),
							
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Icon Color", "js_composer" ),
							"param_name" => "ct_as_icon_color",
							"value" => '',
							"description" => __("Choose text color.Leave blank to ignore", "js_composer" ),						
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Icon Background Color", "js_composer" ),
							"param_name" => "ct_as_iconbg_color",
							"value" => '',
							"description" => __("Choose background color.Leave blank to ignore", "js_composer" ),
						),	
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Backgroud Top Gradient ", "js_composer" ),
							"param_name" => "ct_bgtop_color",
							"value" => '',
							"description" => __("Choose top background color gradient.Leave blank to ignore", "js_composer" ),
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array( 'style5' )
							),						
						),
						
						array(
							"type" => "colorpicker",
							"class" => "",
							"heading" => __("Backgroud Bottom Gradient", "js_composer" ),
							"param_name" => "ct_bgbottom_color",
							"value" => '',
							"description" => __("Choose bottom background color gradient.Leave blank to ignore", "js_composer" ), 
							'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array( 'style5' )
							),				
						),
							
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Image opacity", "js_composer"),
						  "param_name" => "ct_as_img_opa",
						  "value" => array(
							  __("100%", "js_composer") => "1", 
							  __("90%", "js_composer") => "0.9",
							  __("80%", "js_composer") => "0.8",
							  __("70%", "js_composer") => "0.7",
							  __("60%", "js_composer") => "0.6",
							  __("50%", "js_composer") => "0.5",
							  __("40%", "js_composer") => "0.4",
							  __("30%", "js_composer") => "0.3",
							  __("20%", "js_composer") => "0.2",
							  __("10%", "js_composer") => "0.1",
							  __("0%", "js_composer") => "0"
						  ),
						  "description" => __("Set image opacity before hover(from 0% to 100%). Use for styles 5,6 and 7.", "js_composer"),
						  'dependency'  => array(
								'element' => 'ct_as_hoverfx',
								'value'   => array('style5' ,'style6' , 'style7' )
							),		
						),
				
						
						array(
							"type" => "colorpicker",
							"holder" => "div",
							"class" => "",
							"heading" => __("Navigation Arrows Color", "js_composer" ),
							"param_name" => "ct_as_navarrow_color",
							"value" => '',
							"description" => __("Choose navigation arrows color.Leave blank to ignore", "js_composer" ),	
							"admin_label" => true
						),
						
						array(
						  "type" => "dropdown",
						  "heading" => __("Responsive style", "js_composer"),
						  "param_name" => "ct_as_responsive_style",
						  "value" => array(
							  __("Grid style", "js_composer") => "responsivegrid", 
							  __("Slider style", "js_composer") => "responsiveslider"
							   
						  ),
						  "description" => __("Select slider responsive style ", "js_composer"),
						  "admin_label" => true
						),
						array(
							"type" => "textfield",
							"heading" => __("Responsive under", "js_composer"),
							"param_name" => "ct_as_responsive_width",
							"value" => "768",
							"description" => __("Select when the slider will become responsive", "js_composer")
						),
                        array(
						  "type" => 'checkbox',
						  "heading" => __("Enable VC lightbox", "js_composer"),
						  "param_name" => "ct_as_lightbox",
						  "description" => __("Turn on VC lightbox if is not already enabled by your current theme ", "js_composer"),
						  "value" => Array(__("Yes, please", "js_composer") => 'ct_as_shadow_fx'),
						),
					),
				) 
			);				
			}
			public function excerpt($excerpt,$limit) {
				$excerpt = explode(' ', $excerpt, $limit);
				if (count($excerpt)>=$limit) {
					array_pop($excerpt);
					$excerpt = implode(" ",$excerpt).'...';
				} else {
					$excerpt = implode(" ",$excerpt);
				} 
				if($excerpt == '...'){
					$excerpt = '';
				}
				$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
				return $excerpt;
			}
		}
		$GLOBALS['ab_ct_amy_slider_image_extend'] = new ab_ct_amy_slider_image_extend;
	}  
    
}