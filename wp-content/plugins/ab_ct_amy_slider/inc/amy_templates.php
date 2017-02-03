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

if (!class_exists('ct_ab_amy_slider')) {
	class ct_ab_amy_slider extends WPBakeryShortCode_VC_Posts_Grid {
		public  $ct_as_title,
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
                //$ct_as_google_font_titles,//coming soon
                //$ct_as_google_fonts,//coming soon
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
                $ct_as_lightbox;
		
		function __construct($ct_as_title,
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
                            //$ct_as_google_font_titles, //coming soon
                            //$ct_as_google_fonts, //coming soon
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
						) {
			
			$this->ct_as_title=$ct_as_title;
			$this->ct_as_query=$ct_as_query;
			$this->ct_as_height=$ct_as_height;
			$this->ct_as_style=$ct_as_style;
			$this->ct_as_hoverfx=$ct_as_hoverfx;
			$this->ct_as_thumbsize=$ct_as_thumbsize;
			$this->ct_as_mouse_parallax=$ct_as_mouse_parallax;
			$this->ct_as_mouse_parallax_depth=$ct_as_mouse_parallax_depth;
			$this->ct_as_slideshow=$ct_as_slideshow;
			$this->ct_as_slideshow_speed=$ct_as_slideshow_speed;
			$this->ct_as_first_slide=$ct_as_first_slide;
			$this->ct_as_border_radius=$ct_as_border_radius;
            
            /*$this->ct_as_google_font_titles=$ct_as_google_font_titles;
            $this->ct_as_google_fonts=$ct_as_google_fonts;*/
            $this->ct_as_title_size = $ct_as_title_size;
			$this->ct_as_titlelink_color = $ct_as_titlelink_color;
			$this->ct_as_titlelinkbg_color = $ct_as_titlelinkbg_color;
			$this->ct_as_titlelinkhover_color = $ct_as_titlelinkhover_color;
			$this->ct_as_titlelinkbghover_color = $ct_as_titlelinkbghover_color;
			$this->ct_as_read_more=$ct_as_read_more;
			$this->ct_as_woo_price_color=$ct_as_woo_price_color;
			$this->ct_as_excerpt_color=$ct_as_excerpt_color;
			$this->ct_as_readmore_color=$ct_as_readmore_color;
			$this->ct_as_readmorebg_color=$ct_as_readmorebg_color;
			$this->ct_as_readmorehover_color=$ct_as_readmorehover_color;
			$this->ct_as_readmorebghover_color=$ct_as_readmorebghover_color;
			$this->ct_as_border_size=$ct_as_border_size;
			$this->ct_as_border_color=$ct_as_border_color;
			$this->ct_as_image_thumb_size=$ct_as_image_thumb_size;
			$this->ct_as_zoomimgfx=$ct_as_zoomimgfx;
			$this->ct_as_shadow_fx=$ct_as_shadow_fx;
			$this->ct_as_img_opa=$ct_as_img_opa;
			$this->ct_as_responsive_style=$ct_as_responsive_style;
			$this->ct_as_responsive_width=$ct_as_responsive_width;
			$this->ct_as_bg_color=$ct_as_bg_color;
			$this->ct_bgtop_color=$ct_bgtop_color;
			$this->ct_bgbottom_color=$ct_bgbottom_color;
			$this->ct_as_navarrow_color=$ct_as_navarrow_color;
			$this->ct_as_excerpt_length=$ct_as_excerpt_length;
            $this->ct_as_lightbox=$ct_as_lightbox;
            
			$this->ct_amy_slider_frontend_start();
			$this->ct_amy_slider_frontend_css();
		}
		function ct_amy_slider_frontend_start(){
			global $ab_ct_amy_slider_extend,$output, $isrun;
			$loop=$this->ct_as_query;
			$grid_link = $grid_layout_mode = $title = $filter= '';
			$posts = array();
			if(empty($loop)) return;
			$this->getLoop($loop);
			$my_query = $this->query;
			$args = $this->loop_args;
			$img_id=array();
			$output = '';
			$isrun = $isrun+1;
			$this->ct_as_id = $rand_id = $isrun;
			$img_numb = 0;
            
            if($this->ct_as_lightbox){
                wp_enqueue_script( 'prettyphoto' );
                wp_enqueue_style( 'prettyphoto' );
            }
            
		while ( $my_query->have_posts() ) {
			//wp_reset_query();
			$my_query->the_post();
			$post = new stdClass();
			$post->id = get_the_ID();
			$postexcerpt = get_post( $post->id );
            $post->title = the_title("", "", false);
            $post->custom_margin = 0;
            $post->custom_max_width = 0;
            $post->shortcodes_custom_css = '';
            
            $post->content_source =  get_post_meta( get_the_ID(), 'custom_amy_slide_content_source', true );
            $post->image_source =  get_post_meta( get_the_ID(), 'custom_amy_image_source', true );
            
			$post->custom_url =  get_post_meta( get_the_ID(), 'custom_post_custom_url', true );
			$post->custom_url_target =  get_post_meta( get_the_ID(), 'custom_post_custom_url_target', true );
			$post->link = get_permalink($post->id);
			
			$post->post_type = get_post_type();
            
			/*$post->content = $this->getPostContent();*/
			$post->excerpt = $ab_ct_amy_slider_extend->excerpt(get_the_excerpt(),$this->ct_as_excerpt_length);
			if($post->excerpt == '' && $this->ct_as_excerpt_length != 0){
				$post->excerpt = $ab_ct_amy_slider_extend->excerpt($postexcerpt->post_excerpt,$this->ct_as_excerpt_length);
			}
            if($post->image_source =='custom_image'){
                $img =  get_post_meta( get_the_ID(), 'custom_custom_source_image', true );
                $img_size = explode( 'x', $this->ct_as_image_thumb_size ); 
                $post_thumbnail = wp_get_attachment_image( $img, array( $img_size[0],$img_size[1]), true ); 
                $post->current_img_large = $post_thumbnail;
                $post->current_img_full = wp_get_attachment_image_src( $img,'full', true ); 
            }else{
                $post_thumbnail = wpb_getImageBySize(array( 'post_id' => $post->id, 'thumb_size' => $this->ct_as_image_thumb_size ));
                $post->current_img_large = $post_thumbnail['thumbnail'];
                $img_id[]=get_post_meta( $post->id , '_thumbnail_id' ,true );
                $post->current_img_full = wp_get_attachment_image_src( $img_id[$img_numb++] , 'full' );
            }
            if($post->content_source != 'disabled'){
                if($post->content_source == 'from_field'){
                    $post->content = get_post_meta( get_the_ID(), 'custom_amy_slide_embed', true );
                }else{
                    $post->content = $this->getPostContent();
                }
                $post->custom_max_width =  get_post_meta( get_the_ID(), 'custom_amy_slide_max_width', true );
                $post->custom_margin = $post->custom_max_width/2*-1;
                $post->shortcodes_custom_css = get_post_meta( get_the_ID(), '_wpb_shortcodes_custom_css', true );
            }
			$post->the_permalink = get_permalink($post->id);
			global $product;
			if(isset( $product)){
				$post->get_price_html = $product->get_price_html(); 
				$post->get_rating_html = $product->get_rating_html(); 
				$post->the_permalink = get_permalink($post->id);
				$post->add_to_cart_url = $product->add_to_cart_url();
				$post->get_sku = $product->get_sku(); 
				$post->is_purchasable = $product->is_purchasable() ? 'add_to_cart_button' : ''; 
				$post->product_type = $product->product_type;
				$post->add_to_cart_text = $product->add_to_cart_text(); 
				$post->get_categories = $product->get_categories( ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', sizeof( get_the_terms( $post->id, 'product_cat' ) ), 'woocommerce' ) . ' ', '.</span>' );								
			}
			$posts[] = $post;
		}
		  if ( !wp_is_mobile()|| wp_is_mobile() && $this->ct_as_responsive_style != 'responsivegrid'){?>
		<script>
			jQuery(document).ready(function($){
				setTimeout(function middleposition(){
					var maxHeight = -1;
                    jQuery('#ct_as_amy_sliderid<?php echo $this->ct_as_id; ?> ct_amy_section').each(function() {
                        maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
                    });
                    jQuery('#ct_as_amy_sliderid<?php echo $this->ct_as_id; ?> ct_amy_section').css("margin-bottom", '-'+maxHeight/2+'px');
                   
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

        <div class="ct_amy_initloader ct_amy_animated ct_amy_fadeinup">
            <div class="ct_amy_slider_loading"></div>
        </div>  
		<div id="ct_amy_main<?php echo $this->ct_as_id; ?>" <?php echo ' style="'.$style.' "'; ?> class="<?php echo $this->ct_as_style; if(function_exists( 'is_woocommerce' ) &&  isset($post->product_type)){ echo " woocommerce ";}; echo ' '.$this->ct_as_thumbsize ?>">
			<ct_amy_article id="ct_as_amy_sliderid<?php echo $this->ct_as_id; ?>" class="<?php echo $this->ct_as_zoomimgfx;?>" >
			<?php 
            foreach($posts as $post):?>
					<ct_amy_section class="ct_amy_grid bespoke-slide " <?php if($post->custom_margin != "0"){ echo "style='max-width:".$post->custom_max_width."px; width:".$post->custom_max_width."px; margin-left:".$post->custom_margin."px;'";};?> >
                  
						<div class="layer ct_amy_cn_style  center-content " data-depth="<?php echo $this->ct_as_mouse_parallax_depth;?>"><?php 
                            if($post->content_source == 'from_post' || $post->content_source == 'from_field'){
                                if ( ! empty( $post->shortcodes_custom_css ) ) {
                                    echo '<style type="text/css" data-type="vc_shortcodes-custom-css">'.$post->shortcodes_custom_css.'</style>';
                                }
                                echo do_shortcode($post->content);
                            }else{
							if($this->ct_as_hoverfx == 'style1' || $this->ct_as_hoverfx == 'style2' || $this->ct_as_hoverfx == 'style3' || $this->ct_as_hoverfx == 'style4'){
								if($this->ct_as_hoverfx == 'style2'){
									$as_hfx  = "M180,0v117.9V147v29.1h-60V157H60v-19.5H0V0H180z";
								}else if($this->ct_as_hoverfx == 'style3'){
									$as_hfx  = "M0-2h180v186.8c0,0-44,21-90-12.1c-48.8-35.1-90,12.1-90,12.1V-2z";
								}else if($this->ct_as_hoverfx == 'style4'){
									$as_hfx  = "none";
								}else{
									$as_hfx  = "M 0 0 L 0 182 L 90 156.5 L 180 182 L 180 0 L 0 0 z ";
								}
								if(function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style1' || function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style2' || function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style3' ){ 
									global  $product;?>
									<div class="ct_amy_slelement <?php if(function_exists( 'is_woocommerce' ) &&  isset($post->product_type)){ echo " woocommerce ";};?>">
										<ct_amy_figure>
											<?php echo  $post->current_img_large;?> 
											<svg viewBox="0 0 180 280" preserveAspectRatio="none"><path d="<?php echo $as_hfx ?>"/></svg>
											<ct_amy_figcaption rel="<?php the_permalink(); ?>">
												<h2 class="ct_amy_content_title" ><a href="<?php echo $post->the_permalink; ?>"><?php echo $post->title; ?></a></h2> 
												<div class="hideifneed">
													<div class="ct_amy_priceholder ct_amy_wooprice">
														<?php echo $post->get_price_html ?>
													</div>
													<p><?php echo $post->get_categories; ?></p>
													<?php 
													echo apply_filters( 'woocommerce_loop_add_to_cart_link',
													sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s product_type_%s ct_as_readmore"><div class="ct_amy_clickimg ct_amy_clickaddtocart"><i class="fa fa-shopping-cart"></i></div></a>',
														esc_url( $post->add_to_cart_url ),
														esc_attr( $post->id ),
														esc_attr( $post->get_sku ),
														$post->is_purchasable ? 'add_to_cart_button' : '',
														esc_attr( $post->product_type ),
														esc_html( $post->add_to_cart_text )
														),
													$product );?>
													<a class="ct_as_readmore" href="<?php echo $post->the_permalink; ?>"> 
														<div class="ct_amy_clicklink">
															<div><?php echo $post->title ?></div>
														</div>
													</a>
													<a class="ct_as_readmore" href="<?php echo $post->the_permalink; ?>">
														<div class="ct_amy_clickimg"><?php 
															if($rating_html = $post->get_rating_html){ 
																echo $rating_html;
															}else{?>
																<span class="star-rating" title="Rated 0.00 out of 0">
																	<span class="no-rating"></span>
																</span><?php 
															}?>
														</div>
													</a>
												</div>
											</ct_amy_figcaption>            
										</ct_amy_figure>
									</div><?php
								}else if(function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style4' ){
									global  $product;?>
									<div class="ct_amy_slelement woocommerce">
										<ct_amy_figure class="ct_amy_nobgcolor">
											<?php echo $post->current_img_large;?> 
										
											<ct_amy_figcaption class="layer" rel="<?php the_permalink(); ?>">
											<h2 class="ct_amy_content_title"><a href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>" ><?php echo  $post->title ?></a></h2>
												<div class="hideifneed">
													<?php 
													echo apply_filters( 'woocommerce_loop_add_to_cart_link',
													sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s product_type_%s"><div class="ct_amy_clickimg ct_amy_clickaddtocart ct_as_readmore"><i class="fa fa-shopping-cart"></i></div></a>',
														esc_url( $post->add_to_cart_url ),
														esc_attr( $post->id ),
														esc_attr( $post->get_sku ),
														$post->is_purchasable ? 'add_to_cart_button' : '',
														esc_attr( $post->product_type ),
														esc_html( $post->add_to_cart_text )
														),
													$product );?>
													<a class="ct_as_readmore" href="<?php echo $post->the_permalink; ?>"> 
														<div class="ct_amy_clicklink">
															<div><?php echo $post->title ?></div>
														</div>
													</a>
													
													<a class="ct_as_readmore" href="<?php echo $post->the_permalink; ?>">
													<div class="ct_amy_clickimg">
														<?php echo $post->get_price_html; ?>
													</div>
													</a>
												</div>
											</ct_amy_figcaption>            
										</ct_amy_figure>
									</div><?php
								}else{
									if($this->ct_as_hoverfx != 'style4'){?>
										<div class="ct_amy_slelement">
											<ct_amy_figure>
												<?php echo  $post->current_img_large;?>
												<svg viewBox="0 0 180 280" preserveAspectRatio="none"><path d="<?php echo $as_hfx;?>"/></svg>
												<ct_amy_figcaption class="layer" rel="<?php the_permalink(); ?>"> 
													<h2 class="ct_amy_content_title"><?php echo  $post->title ?></h2>
													<div class="hideifneed">
													<p><?php
												echo $post->excerpt;
													?></p>
														
														<a class="ct_as_readmore" href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>"> 
															<div class="ct_amy_clicklink">
																<i class="fa fa-link"></i>
															</div>
														</a>
														<a class="prettyphoto ct_as_readmore"  href="<?php echo $post->current_img_full[0]; ?>"   rel="prettyPhoto[rel-<?php echo $post->id; ?>]">
															<div class="ct_amy_clickimg">
																<i class="fa fa-search"></i>
															</div>
														</a>
													</div>
												</ct_amy_figcaption>            
											</ct_amy_figure>
										</div>	<?php
									}else{?>
										<div class="ct_amy_slelement">
											<ct_amy_figure>
												<?php echo  $post->current_img_large;?>
												<ct_amy_figcaption class="layer" rel="<?php the_permalink(); ?>"> 
													<h2 class="ct_amy_content_title"><a href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>"><?php echo  $post->title ?></a></h2>
													<div class="hideifneed">
											 
														
														<a class="ct_as_readmore" href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>"> 
															<div class="ct_amy_clicklink">
																<i class="fa fa-link"></i>
															</div>
														</a>
														<a class="prettyphoto ct_as_readmore"  href="<?php echo $post->current_img_full[0]; ?>"   rel="prettyPhoto[rel-<?php echo $post->id; ?>]">
															<div class="ct_amy_clickimg">
																<i class="fa fa-search"></i>
															</div>
														</a>
													</div>
												</ct_amy_figcaption>            
											</ct_amy_figure>
										</div>	<?php
									}
								}
							}else if($this->ct_as_hoverfx == 'style5' || $this->ct_as_hoverfx == 'style6' || $this->ct_as_hoverfx == 'style7'){
								if(function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style5' || function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style6' || function_exists( 'is_woocommerce' ) && $post->post_type=='product' && $this->ct_as_hoverfx == 'style7'){
									global  $product;
									if($this->ct_as_hoverfx == 'style5'){
										$fxclass= 'ct_amy_effect_sadie';
									}else if($this->ct_as_hoverfx == 'style6'){
										$fxclass= 'ct_amy_effect_ruby';
									}else{
										$fxclass= 'ct_amy_effect_dexter';
									}?>
									<ct_amy_figure class="<?php echo $fxclass;?> <?php if($post->image_source == 'no_image'){echo ' ct_amy_no_image';};?>">
									
										<?php echo $post->current_img_large; ?>
										<ct_amy_figcaption <?php if($post->image_source == 'no_image'){echo 'class="ct_amy_no_image"';};?>>
											<h2 class="ct_amy_content_title" ><a href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>"><?php echo  $post->title ?></a></h2>
											<div class="hideifneed">
												<p>
												<span class=" ct_amy_wooprice">
													<?php echo $post->get_price_html ?>
												</span>
                                                <br /><?php
													echo $post->excerpt; ?>
                                                <br /><br />
													<a class="ct_as_readmore" href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>">
													<?php echo $this->ct_as_read_more; ?></a>
													<?php 
												  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
													sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="%s product_type_%s ct_as_readmore ct_as_addtocart"><i class="fa fa-shopping-cart"></i><i class="fa fa-refresh fa-spin"></i></a>',
														esc_url( $post->add_to_cart_url ),
														esc_attr( $post->id ),
														esc_attr( $post->get_sku ),
														$post->is_purchasable ? 'add_to_cart_button' : '',
														esc_attr( $post->product_type ),
														esc_html( $post->add_to_cart_text )
														),
													$product );?>
												</p>              
											</div>
										</ct_amy_figcaption>			
									</ct_amy_figure>
									<?php
									
								}else{
									if($this->ct_as_hoverfx == 'style5'){
										$fxclass= 'ct_amy_effect_sadie';
									}else if($this->ct_as_hoverfx == 'style6'){
										$fxclass= 'ct_amy_effect_ruby';
									}else{
										$fxclass= 'ct_amy_effect_dexter';
									}?>
									<ct_amy_figure class="<?php echo $fxclass;?> <?php if($post->image_source == 'no_image'){echo ' ct_amy_no_image';};?>">
									
										<?php echo $post->current_img_large; ?>
										<ct_amy_figcaption>
											<h2 class="ct_amy_content_title" ><a href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>"><?php echo  $post->title ?></a></h2>
											<div class="hideifneed">
											
												<p><?php
													echo $post->excerpt;
													?><br /><br />
													<a class="ct_as_readmore" href="<?php if($post->custom_url !=''){echo $post->custom_url;}else{ echo $post->link; }?>" target="<?php if($post->custom_url !=''){ echo $post->custom_url_target;}else{echo '_self';}?>">
													<?php echo $this->ct_as_read_more; ?></a>
												</p>              
											</div>
										</ct_amy_figcaption>			
									</ct_amy_figure><?php
								}
							}
                            }?>
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
		</div> <?php
		wp_reset_query();
		}
		function ct_amy_slider_frontend_css() {
			$custom_css='';
			wp_enqueue_style('ct_amy_slider_custom', ab_ct_amy_slider_url . '/css/amy_custom.css', array() , null); 
            
            if($this->ct_as_title_size != ''){
            $custom_css .= '
                #ct_amy_main'.$this->ct_as_id.' .ct_amy_content_title a, #ct_amy_main'.$this->ct_as_id.' .ct_amy_content_title{
                    font-size:'.$this->ct_as_title_size.';
				}';
            }
			$custom_css .= '
				
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_figure:hover .ct_amy_cn_style{
					border-radius:'.$this->ct_as_border_radius.';
				}
				
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_content_title a, #ct_amy_main'.$this->ct_as_id.' .ct_amy_content_title{
					color:'.$this->ct_as_titlelink_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_content_title a{
					background-color:'.$this->ct_as_titlelinkbg_color.';
					box-shadow: 14px 0 0 '.$this->ct_as_titlelinkbg_color.', -14px 0 0 '.$this->ct_as_titlelinkbg_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure:hover .ct_amy_content_title a, #ct_amy_main'.$this->ct_as_id.' ct_amy_figure:hover .ct_amy_content_title{
					color:'.$this->ct_as_titlelinkhover_color.'!important;
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure:hover .ct_amy_content_title a{
					background-color:'.$this->ct_as_titlelinkbghover_color.';
  					box-shadow: 14px 0 0 '.$this->ct_as_titlelinkbghover_color.', -14px 0 0 '.$this->ct_as_titlelinkbghover_color.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_as_readmore, #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .star-rating:before,  #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .star-rating, #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .ct_amy_clickimg .amount{
					color:'.$this->ct_as_readmore_color.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_as_readmore, #ct_amy_main'.$this->ct_as_id.' .ct_amy_clickimg,  #ct_amy_main'.$this->ct_as_id.' .ct_amy_clicklink{
					background-color:'.$this->ct_as_readmorebg_color.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_as_readmore:hover, #ct_amy_main'.$this->ct_as_id.' .ct_amy_clickimg:hover, #ct_amy_main'.$this->ct_as_id.' .ct_amy_clicklink:hover, #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .ct_amy_clickimg:hover .star-rating:before , #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .ct_amy_clickimg:hover .star-rating, #ct_amy_main'.$this->ct_as_id.' .ct_as_readmore .ct_amy_clickimg:hover .amount{
					color:'.$this->ct_as_readmorehover_color.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_as_readmore:hover, #ct_amy_main'.$this->ct_as_id.' .ct_amy_clickimg:hover, #ct_amy_main'.$this->ct_as_id.' .ct_amy_clicklink:hover{
					background-color:'.$this->ct_as_readmorebghover_color.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_grid svg path {
					fill:'.$this->ct_as_bg_color.';
				}
				 #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid ct_amy_figure.ct_amy_effect_dexter, #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid ct_amy_figure.ct_amy_effect_ruby {
					background-color:'.$this->ct_as_bg_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_grid .ct_amy_clicklink:before, #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid .ct_amy_clickimg:before, #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid .ct_amy_clickaddtocart:before{
					background-color:'.$this->ct_as_bg_color.';
					opacity:0.7;
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figcaption p, #ct_amy_main'.$this->ct_as_id.' ct_amy_figcaption p a{
					color:'.$this->ct_as_excerpt_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figcaption p a{
					color:'.$this->ct_as_excerpt_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_arrows_prev, #ct_amy_main'.$this->ct_as_id.' .ct_amy_arrows_next{
					color:'.$this->ct_as_navarrow_color.';
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_sadie ct_amy_figcaption::before{
					background:'.$this->ct_bgbottom_color.';
					background: -webkit-linear-gradient(top, '.$this->ct_bgtop_color.' 0%, '.$this->ct_bgbottom_color.' 75%);
					background: linear-gradient(to bottom, '.$this->ct_bgtop_color.' 0%, '.$this->ct_bgbottom_color.' 75%);
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_dexter img, #ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_ruby img , #ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_sadie img{
					opacity:'.$this->ct_as_img_opa.';
					
				}
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_dexter:hover img, #ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_ruby:hover img {
					opacity:0.1;
	
				}
				#ct_amy_main'.$this->ct_as_id.' .ct_amy_cn_style, #ct_amy_main'.$this->ct_as_id.' .ct_amy_grid ct_amy_figure img, #ct_amy_main'.$this->ct_as_id.' ct_amy_section{
					border-radius:'.$this->ct_as_border_radius.';
				}
				
				#ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_ruby p, #ct_amy_main'.$this->ct_as_id.' ct_amy_figure.ct_amy_effect_dexter ct_amy_figcaption::after{
					border-color:'.$this->ct_as_border_color.';
					border-width:'.$this->ct_as_border_size.';
					
				}
				
				#ct_amy_main'.$this->ct_as_id.' ct_amy_section p ins .amount, #ct_amy_main'.$this->ct_as_id.' ct_amy_section p .amount, #ct_amy_main'.$this->ct_as_id.' ct_amy_section .ct_amy_wooprice ins .amount, #ct_amy_main'.$this->ct_as_id.' ct_amy_section .ct_amy_wooprice .amount{
					color:'.$this->ct_as_woo_price_color.'!important;
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
							width:90%!important;
                            max-width:100%!important;
							padding-left:5%;
							opacity:1!important;
						}
						#ct_amy_main'.$this->ct_as_id.' ct_amy_section:first-child{
							margin-top:16px!important;
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
