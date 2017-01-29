<?php
/*
Plugin Name: AMY Slider for Visual Composer
Description: Adds AMY Slider to your VC
Author: Andrey Boyadzhiev - Cray Themes
Version: 2.0
Author URI: http://themes.cray.bg
Plugin URI: http://themeforest.net/user/CrayThemes/portfolio
*/
?>
<?php
// Add the Meta Box
function ct_amy_meta_box() {
    add_meta_box(
		'ct_amy_custom_meta_box', // $id
		'Post Settings', // $title 
		'ct_amy_show_custom_meta_box', // $callback
		'post', // $page
		'normal', // $context
		'high'); // $priority
	add_meta_box(
		'ct_amy_custom_meta_box', // $id
		'Page Settings', // $title 
		'ct_amy_show_custom_meta_box', // $callback
		'page', // $page
		'normal', // $context
		'high'); // $priority
    add_meta_box(
		'ct_amy_custom_meta_box', // $id
		'Amy Settings', // $title 
		'ct_amy_show_custom_meta_box', // $callback
		'amy_portfolio', // $page
		'normal', // $context
		'high'); // $priority
}
function ct_amy_load_custom_wp_admin_js() {
    wp_enqueue_script( 'ct_amy_admin_js', ab_ct_amy_slider_url . '/js/amy_admin.js', false,'5.0',true);
    wp_enqueue_style('ct_amy_slider_style_options', ab_ct_amy_slider_url . '/css/amy_adminoptions.css', array() , true);
    wp_enqueue_script('admin_js');
		
}

// Field Array
$prefix = 'custom_';
global $ct_amy_custom_meta_fields, $post;
$ct_amy_custom_meta_fields = array(
    
    /*
    TIMELINE SETTINGS
    ==================================================
    */  
    
    //Title
    array(
        'cat'        => '0',
        'id'      => $prefix . 'title',
		'name'    => 'Slider Settings',
		'type'    => 'section_title'
	),
	array(
        'cat'        => '0',
		'label'	=> 'Image Source',
		'desc'	=> 'Select image source',
		'id'	=> $prefix.'amy_image_source',
		'type'	=> 'select_dropdown',
		'options' => array (
			'1' => array (
				'label' => 'Featured Image',
				'value'	=> 'featured_image'
			),
            '2' => array (
				'label' => 'Custom Image',
				'value'	=> 'custom_image'
			),
			'3' => array (
				'label' => 'No Image',
				'value'	=> 'no_image'
			)
		)
	),
    array(
        'cat'        => '0',
		'label'	=> 'Custom image',
		'desc'	=> 'Select custom image',
		'id'	=> $prefix.'custom_source_image',
		'type'	=> 'image'
	),
    
   array(
        'cat'        => '0',
		'label'	=> 'Custom url',
		'desc'	=> 'Overide default slider link to post',
		'id'	=> $prefix.'post_custom_url',
		'type'	=> 'input_text_field',
		'std' => ''
	),
    
    array(
        'cat'        => '0',
		'label'	=> 'Custom url target',
		'desc'	=> '',
		'id'	=> $prefix.'post_custom_url_target',
		'type'	=> 'select_dropdown',
		'options' => array (
			'one' => array (
				'label' => 'Same window',
				'value'	=> '_self'
			),
			'two' => array (
				'label' => 'New window',
				'value'	=> '_blank'
			)
		)
	),
    
    array(
        'cat'        => '1',
        'id'      => $prefix . 'title',
		'name'    => 'Custom Content',
		'type'    => 'section_title'
	),
	array(
        'cat'        => '1',
		'label'	=> 'Custom content',
		'desc'	=> 'If you enable this option the post content will be used for slide content',
		'id'	=> $prefix.'amy_slide_content_source',
		'type'	=> 'select_dropdown',
		'options' => array (
			'one' => array (
				'label' => 'Disabled',
				'value'	=> 'disabled'
			),
			'two' => array (
				'label' => 'Post',
				'value'	=> 'from_post'
			),
            'three' => array (
				'label' => 'Custom content field',
				'value'	=> 'from_field'
			)
			
		)
	),
     array(
        'cat'        => '1',
		'label'	=> 'Custom content field',
		'desc'	=> 'You can add shortcodes, embed scripts or whatever content you want. *Cusrom content field must be enabled. ',
		'id'	=> $prefix.'amy_slide_embed',
		'type'	=> 'input_textarea_field',
		'std' => ''
	),
    
    array(
        'cat'        => '1',
		'label'	=> 'Max width',
		'desc'	=> 'Maximum slide width',
		'id'	=> $prefix.'amy_slide_max_width',
		'type'	=> 'select_dropdown',
		'options' => array (
			'0' => array (
				'label' => 'Default',
				'value'	=> '0'
			),
			'1' => array (
				'label' => '97px (1/12)',
				'value'	=> '97'
			),
            '2' => array (
				'label' => '195px (2/12)',
				'value'	=> '195'
			),
            '3' => array (
				'label' => '292px (3/12)',
				'value'	=> '292'
			),
            '4' => array (
				'label' => '390px (4/12)',
				'value'	=> '390'
			),
            '5' => array (
				'label' => '487px (5/12)',
				'value'	=> '487'
			),
            '6' => array (
				'label' => '585px (6/12)',
				'value'	=> '585'
			),
            '7' => array (
				'label' => '682px (7/12)',
				'value'	=> '682'
			),
            '8' => array (
				'label' => '780px (8/12)',
				'value'	=> '780'
			),
            '9' => array (
				'label' => '877px (9/12)',
				'value'	=> '877'
			),
            '10' => array (
				'label' => '975px (10/12)',
				'value'	=> '975'
			),
			
		)
	),
	
	

);


// add some custom js to the head of the page
// The Callback
function ct_amy_show_custom_meta_box() {
    global $ct_amy_custom_meta_fields, $post;
	// Use nonce for verification
	echo '<input type="hidden" name="ct_amy_custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	echo '<script>
			jQuery( document ).ready( function($){
				$("#ct_amy_custom_meta_box select").msDropDown();
				$(".adimg input:radio").addClass("input_hidden");
				$(".adimg label").click(function() {
					$(this).addClass("selected").siblings().removeClass("selected");
				});
                (function(jQuery) {
                    [].slice.call( document.querySelectorAll( ".tabs" ) ).forEach( function( el ) {
                        new CBPFWTabs( el );
                    });
                })();
			});
		  </script>';
	// Begin the field table and loop
	echo '<div class="flexbox tabs tabs-style-topline "><table class="form-table">
        <nav>
            <ul>
                <li id="section-iconbox-0" ><a href="#section-iconbox-0" class="icon icon-header"><span>Layout Settings</span></a></li>
                <li id="section-iconbox-1"><a  href="#section-iconbox-1" class="icon icon-layout"><span>Custom Content</span></a></li>
            </ul>
        </nav>
        <div class="content-wrap">';
    
    
    

	foreach ($ct_amy_custom_meta_fields as $field) {
		// get value of this field if it exists for this post
		$meta = get_post_meta($post->ID, $field['id'], true);
        $border_class = '';
        if($field['type'] == 'section_sep'){$border_class = 'ct_tt_hideborder';}
        
        echo '<ct_tt_section class="section-iconbox-'.$field['cat'].' '.$border_class.'" >';
		//$meta= get_post_meta(get_page($id), $field['id'], true);
		// begin a table row with
		
				switch($field['type']) {
            
            // text
					case 'section_title':
                    echo '<h2 class="section_title">'.$field['name'].'</h2>';
                    break;
                    
                    case 'section_sep':
                    echo '<div class="page-separator"></div>';
                    break;
                    
                    case 'element_sep':
                    echo '<div class="element-separator">'.$field['name'].'</div>';
                    break;
                    
                   
					case 'select_dropdown':
                        echo '<div class="setting_title">'.$field['label'].'</div>';
						echo '<div class="setting_value"><select name="'.$field['id'].'" id="'.$field['id'].'">';
						foreach ($field['options'] as $option) {
							echo '<option data-image ="'.$option['data-image'].'" ', $meta == $option['value'] ? ' selected="selected"' : '' , ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select> <br /><div class="description-info">'.$field['desc'].'</div></div>';
					break;
					
					
					// text
					case 'input_text_field':
                    echo '<div class="setting_title">'.$field['label'].'</div>';
					echo '<div class="setting_value"><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="', esc_attr($meta) ? esc_attr($meta) : esc_attr($field['std']), '" size="15" />
								<br /><div class="description-info">'.$field['desc'].'</div></div>';
						
					break;
                    // textarea
					case 'input_textarea_field':
                    echo '<div class="setting_title">'.$field['label'].'</div>';
						echo '<div class="setting_value"><textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="6">'.$meta.'</textarea>
						<br /><div class="description-info">'.$field['desc'].'</div></div>';
					break;
                    
					
					// image
					case 'image':
						$image = ab_ct_amy_slider_url.'/images/options/background-img.png';
                    echo '<div class="setting_title">'.$field['label'].'</div><div class="setting_value">';
						echo '<span class="custom_default_image hide_image">'.$image.'</span>';
						if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium');	$image = $image[0]; }				
						echo	'<input name="'.$field['id'].'" type="hidden" class="custom_upload_image" value="'.esc_attr($meta).'" />
									<img src="'.$image.'" class="custom_preview_image" alt="" /><br />
										<input class="custom_upload_image_button button" type="button" value="Choose Image" />
										<a href="#" class="custom_clear_image_button button " >-</a>
										<br clear="all" /><span class="description-info">'.$field['desc'].'</span></div>';
										
							
					break;
					
 					//end switch
				} //end switch
		echo '</ct_tt_section>';
	} // end foreach
	echo '</table> </div>'; // end table
}

// Save the Data
function ct_amy_save_meta_box($post_id) {
    global $ct_amy_custom_meta_fields;
	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;
	
	// loop through fields and save the data
	foreach ($ct_amy_custom_meta_fields as $field) {
		
		$old = get_post_meta($post_id, $field['id'], true);
		if(isset($_POST[$field['id']])){
            $new = sanitize_text_field($_POST[$field['id']]);
		}else{
			$new ='';
		};
		if ($new != $old) {
            if(isset($_POST[$field['id']])){
			 update_post_meta($post_id, $field['id'], $new);
            }  
		} 
	}
	// save taxonomies
	$post = get_post($post_id);
}
?>