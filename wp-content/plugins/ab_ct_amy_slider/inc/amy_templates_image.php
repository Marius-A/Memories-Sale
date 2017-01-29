<?php 
/*
Plugin Name: AMY Slider for Visual Composer
Description: Adds AMY Slider to your VC
Author: Andrey Boyadzhiev - Cray Themes
Version: 2.0
Author URI: http://themes.cray.bg
Plugin URI: http://themeforest.net/user/CrayThemes/portfolio
*/
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('ct_ab_amy_slider_image')) {
	class ct_ab_amy_slider_image extends WPBakeryShortCode_VC_Posts_Grid {
		public  $ct_as_title,
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
                $ct_as_lightbox;
		
		function __construct($ct_as_title,
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
						) {
			
			$this->ct_as_title=$ct_as_title;
			$this->ct_as_query=$ct_as_query;
			$this->ct_as_height=$ct_as_height;
			$this->ct_as_style=$ct_as_style;
			$this->ct_as_thumbsize=$ct_as_thumbsize;
			$this->ct_as_mouse_parallax=$ct_as_mouse_parallax;
			$this->ct_as_mouse_parallax_depth=$ct_as_mouse_parallax_depth;
			$this->ct_as_slideshow=$ct_as_slideshow;
			$this->ct_as_slideshow_speed=$ct_as_slideshow_speed;
			$this->ct_as_first_slide=$ct_as_first_slide;
			$this->ct_as_border_radius=$ct_as_border_radius;
			$this->ct_as_icon_color=$ct_as_icon_color;
			$this->ct_as_iconbg_color=$ct_as_iconbg_color;
			$this->ct_as_image_thumb_size=$ct_as_image_thumb_size;
			$this->ct_as_zoomimgfx=$ct_as_zoomimgfx;
			$this->ct_as_shadow_fx=$ct_as_shadow_fx;
			$this->ct_as_img_opa=$ct_as_img_opa;
			$this->ct_as_responsive_style=$ct_as_responsive_style;
			$this->ct_as_responsive_width=$ct_as_responsive_width;
			$this->ct_bgtop_color=$ct_bgtop_color;
			$this->ct_bgbottom_color=$ct_bgbottom_color;
			$this->ct_as_navarrow_color=$ct_as_navarrow_color;
            $this->ct_as_lightbox=$ct_as_lightbox;
            
			$this->ct_amy_slider_frontend_start();
			$this->ct_amy_slider_frontend_css();
            
		}
		function ct_amy_slider_frontend_start(){
            if($this->ct_as_lightbox){
                wp_enqueue_script( 'prettyphoto' );
                wp_enqueue_style( 'prettyphoto' );
            }
			global $ab_ct_amy_slider_extend,$output, $isrun;
			
            
            $images = explode( ',', $this->ct_as_query );
$i = - 1;

foreach ( $images as $attach_id ) {
    $margin = explode( 'x', $this->ct_as_image_thumb_size );
    $post = new stdClass();
    $post->automargin = $margin[0]/2*-1;
	$i ++;
    $isrun = $isrun+1;
    $this->ct_as_id = $isrun;
	if ( $attach_id > 0 ) {
        $post_thumbnail = wpb_getImageBySize( array( 'attach_id' => $attach_id, 'thumb_size' => $this->ct_as_image_thumb_size ) );
        $post->current_img_large = $post_thumbnail['thumbnail'];
        $post_fullimg = wpb_getImageBySize( array( 'attach_id' => $attach_id, 'thumb_size' => 'full' ) );
        $post->current_img_full = $post_fullimg['p_img_large'];
	} else {
		$post_thumbnail = array();
		$post_thumbnail['thumbnail'] = '<img src="' . vc_asset_url( 'vc/no_image.png' ) . '" />';
		$post_thumbnail['p_img_large'][0] = vc_asset_url( 'vc/no_image.png' );
	}
    $posts[] = $post;
}
		  if ( !wp_is_mobile()|| wp_is_mobile() && $this->ct_as_responsive_style != 'responsivegrid'){?>
		<script>
			jQuery(document).ready(function($){
				setTimeout(function middleposition(){
					var sliderPosition = jQuery('#ct_as_amy_sliderid<?php echo $this->ct_as_id; ?> ct_amy_section').height() / 2;
                    <?php if($post->automargin != "0"){?>
					jQuery('#ct_as_amy_sliderid<?php echo $this->ct_as_id; ?> ct_amy_section').css({"margin-bottom":'-'+sliderPosition+'px', "margin-left":"<?php echo $post->automargin;?>px"});
                    <?php }else{?>
                            jQuery('#ct_as_amy_sliderid<?php echo $this->ct_as_id; ?> ct_amy_section').css("margin-bottom", '-'+sliderPosition+'px');
                    <?php } ?>
                    
				},400);
				setTimeout(function startslider (){
					window.scrollinit("<?php echo $this->ct_as_id; ?>","<?php echo $this->ct_as_first_slide;?>", "<?php if($this->ct_as_thumbsize != ''){ echo $this->ct_as_thumbsize;}else{ echo '0';}  ?>", "<?php if($this->ct_as_slideshow != ''){echo $this->ct_as_slideshow;}else{ echo '0';} ?>", "<?php if($this->ct_as_slideshow_speed != ''){ echo $this->ct_as_slideshow_speed;}else{echo "4000";}?>","<?php if($this->ct_as_mouse_parallax == 'yes'){echo $this->ct_as_mouse_parallax;}else{ echo '0';} ?>");
				},700);	
			});
		</script><?php 
        };
		$style = '';
		if( $this->ct_as_height != '' ) {
			$style .= 'height: '.(preg_match('/(px|em|\%|pt|cm)$/', $this->ct_as_height) ? $this->ct_as_height : $this->ct_as_height.'px').';';
		}?>
<div class="wpb_column vc_column_container">
    <div class="ct_amy_initloader ct_amy_animated ct_amy_fadeinup">
        <div class="ct_amy_slider_loading"></div>
    </div>  
		<div id="ct_amy_main<?php echo $this->ct_as_id; ?>" <?php echo ' style="'.$style.' "'; ?> class="<?php echo $this->ct_as_style.' '.$this->ct_as_thumbsize ?>">
			<ct_amy_article id="ct_as_amy_sliderid<?php echo $this->ct_as_id; ?>" class="ct_as_amy_gallery <?php echo $this->ct_as_zoomimgfx;?>" >
			<?php foreach($posts as $post):?>
					<ct_amy_section class="ct_amy_grid bespoke-slide  <?php if($post->automargin != "0"){ echo 'ct_amy_custom_width';}?>" >
						<div class="layer ct_amy_cn_style  center-content " data-depth="<?php echo $this->ct_as_mouse_parallax_depth;?>">
                            <ct_amy_figure class="ct_amy_effect_sadie">
									<a class="prettyphoto ct_as_readmore" rel="prettyPhoto[rel-<?php echo $this->ct_as_id; ?>]" href="<?php  echo $post->current_img_full[0]; ?>">
										<?php echo $post->current_img_large; ?>
										<ct_amy_figcaption>
											<div class="ct_amy_clickimg">
                                                <i class="fa fa-search"></i>
                                            </div>
										</ct_amy_figcaption>
                                    </a>
                            </ct_amy_figure>
						</div>
                        <?php if ($this->ct_as_shadow_fx == 'ct_as_shadow_fx'){?>
							<div class="ct_as_shadow_holder">
                            	<div class="layer ct_as_shadow_layer" data-depth="<?php echo $this->ct_as_mouse_parallax_depth;?>">
                                	<div class="ct_as_shadow_fx"></div>
                                </div>
							</div>
                            <?php
						}?>
					</ct_amy_section>
			<?php   endforeach; ?>
            </ct_amy_article>
            <div id="arrownav<?php echo $this->ct_as_id;?>" class="ct_as_arrow_nav ">
                <div class="ct_amy_arrows_next">
                    <i class="fa fa-angle-right next-arrow"></i>
                </div>
                <div class="ct_amy_arrows_prev">
                    <i class="fa fa-angle-left prev-arrow"></i>
                </div>
             </div>
		</div> 
    </div><?php
		wp_reset_query();
		}
		

		function ct_amy_slider_frontend_css() {
			$custom_css='';
			wp_enqueue_style('ct_amy_slider_custom', ab_ct_amy_slider_url . '/css/amy_custom.css', array() , null); 
			$custom_css .= '
				
				#ct_amy_main'.$this->ct_as_id.' a:hover, #ct_amy_main'.$this->ct_as_id.' a{
					color:'.$this->ct_as_icon_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_as_amy_gallery .ct_amy_grid .ct_amy_clickimg{
					background-color:'.$this->ct_as_iconbg_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_arrows_prev, #ct_amy_main'.$this->ct_as_id.' .ct_amy_arrows_next{
					color:'.$this->ct_as_navarrow_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_sadie ct_amy_figcaption::before{
					background:'.$this->ct_bgbottom_color.';
					background: -webkit-linear-gradient(top, '.$this->ct_bgtop_color.' 0%, '.$this->ct_bgbottom_color.' 75%);
					background: linear-gradient(to bottom, '.$this->ct_bgtop_color.' 0%, '.$this->ct_bgbottom_color.' 75%);
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_sadie img{
					opacity:'.$this->ct_as_img_opa.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_cn_style, #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid ct_amy_figure img, #ct_amy_main'.$this->ct_as_id.' ct_amy_section, .ct_as_amy_gallery ct_amy_figure.ct_amy_effect_sadie ct_amy_figcaption::before, .ct_as_amy_gallery .ct_amy_grid .ct_amy_clickimg{
					border-radius:'.$this->ct_as_border_radius.';
                    -webkit-border-radius:'.$this->ct_as_border_radius.';
				}';
                if ( !wp_is_mobile()|| wp_is_mobile() && $this->ct_as_responsive_style != 'responsivegrid'){
                    wp_enqueue_script( 'ct_as_jquery_classList', ab_ct_amy_slider_url . '/js/amy_classList.js', false,'1.0',true);
                    wp_enqueue_script( 'ct_as_jquery_parallax', ab_ct_amy_slider_url . '/js/amy_parallax.js', false,'1.0',true);
                    wp_enqueue_script( 'ct_as_jquery_amyslider', ab_ct_amy_slider_url . '/js/amy_slider.js', false,'1.0',true);
                    wp_enqueue_script( 'ct_as_jquery_amybs', ab_ct_amy_slider_url . '/js/amy_bs.js', false,'5.0',true);
                 }else{
                    if($this->ct_as_responsive_style == 'responsivegrid' && wp_is_mobile() ){
                        wp_enqueue_style('ct_amy_slider_style', ab_ct_amy_slider_url . '/css/amy_main_mobile.css', array() , null);
                    }
                }
				if($this->ct_as_responsive_style == 'responsivegrid'){
					$custom_css .= '
					@media screen and  (max-width: '.$this->ct_as_responsive_width.'px), screen and  (max-height: 409px)   {
						#ct_amy_main'.$this->ct_as_id.' .bespoke-parent{
							position:static;
							overflow:visible;
						}
						.ct_as_shadow_fx ct_amy_section:after, #arrownav'.$this->ct_as_id.', .ct_amy_initloader{
							display:none;
						}
						#ct_amy_main'.$this->ct_as_id.' ct_amy_section{
							position:static;
							-webkit-transform:none!important;
							-moz-transform:none!important;
							-ms-transform:none!important;
							-o-transform:none!important;
							transform:none!important;
							-webkit-transition:none!important;
							-moz-transition:none!important;
							-ms-transition:none!important;
							-o-transition:none!important;
							transition:none!important;
							margin:8px 0px!important;
							float:left;
							width:90%;
							padding-left:5%;
							opacity:1!important;
						}
						#ct_amy_main'.$this->ct_as_id.' ct_amy_section:first-child{
							margin-top:16px!important;
						}
                        #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid ct_amy_figure{
                            width:100%;
                            height:100%;
                        }
					}';
				}else{
					$custom_css .= '
					@media screen and  (max-width: '.$this->ct_as_responsive_width.'px), screen and  (max-height: 409px)   {
					
						#ct_amy_main'.$this->ct_as_id.' ct_amy_section{
							margin-left:0;
							left:0;
							width:90%;
							padding-left:5%;
						}
					}';
				}
				
				if(wp_is_mobile()){
					$custom_css .= '
					
					ct_amy_section {
						-webkit-touch-callout: none;
						-webkit-user-select: none;
					}';
				}
			
			wp_add_inline_style( 'ct_amy_slider_custom', $custom_css );
		}	
			
	}	
} 
?>
