<?php

//Added for enabling conditional menu
if(userpro_get_option('up_conditional_menu') == 1){
	add_filter( 'wp_nav_menu_objects', 'userpro_filter_wp_nav_menu_args', 10, 2 );
}
function userpro_filter_wp_nav_menu_args( $menu , $args) {

	$pages = get_option('userpro_pages') + get_option('userpro_sc_pages') + get_option('userpro_connections') ;
	
	foreach($menu as $k => $v){
		if(is_user_logged_in()){
			if( isset($pages['login']) && $v->object_id == $pages['login'] || isset($pages['register']) && $v->object_id == $pages['register'] ){
				unset($menu[$k]);
			}
		}else{
			if( isset($pages['connections']) && $v->object_id == $pages['connections'] || isset($pages['following']) && $v->object_id == $pages['following'] || isset($pages['followers']) && $v->object_id == $pages['followers'] || isset($pages['dashboard']) && $v->object_id == $pages['dashboard'] || isset($pages['edit']) && $v->object_id == $pages['edit'] || isset($pages['logout_page']) && $v->object_id == $pages['logout_page'] ){
				unset($menu[$k]);
			}
		}
	}
	return $menu;
}

	
	/* Overrides default avatars */
	function userpro_get_avatar( $avatar, $id_or_email, $size, $default, $alt='' ) {
		global $userpro;
 		require_once(userpro_path.'lib/BFI_Thumb.php');
		if (isset($id_or_email->user_id)){
			$id_or_email = $id_or_email->user_id;
		} elseif (is_email($id_or_email)){
			$user = get_user_by('email', $id_or_email);
			$id_or_email = $user->ID;
		}
		
		if ($id_or_email && userpro_profile_data( 'profilepicture', $id_or_email ) ) {
			
			$url = $userpro->file_uri(  userpro_profile_data( 'profilepicture', $id_or_email ), $id_or_email );
	        $params = array('width'=>$size);
			if(!userpro_get_option('aspect_ratio')){
				$params['height'] = $size;			
			}
			if(userpro_get_option('pimg')==1)
			{
				$crop=bfi_thumb($url,$params);
			}
			else 
			{
				$crop = bfi_thumb(get_site_url().(strpos($url,"http") !== false ? urlencode($url) : $url),$params);
			}
			$return = '<img src="'.$crop.'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" class="modified avatar" />';
		
		} else {
		
			if ($id_or_email && userpro_profile_data( 'gender', $id_or_email ) ) {
				$gender = strtolower( userpro_profile_data( 'gender', $id_or_email ) );
			} else {
				$gender = 'male'; // default gender
			}
		
			$userpro_default = userpro_url . 'img/default_avatar_'.$gender.'.jpg';
			$return = '<img src="'.$userpro_default.'" width="'.$size.'" height="'.$size.'" alt="'.$alt.'" class="default avatar" />';
		
		}

		if ( userpro_profile_data( 'profilepicture', $id_or_email ) != '') {
			return $return;
		} else {
			if ( userpro_get_option('use_default_avatars') == 1 ) {
				return $avatar;
			} else {
				return $return;
			}
		}
	}
	add_filter('get_avatar', 'userpro_get_avatar', 99, 5);
	
	/* shortcode allowed in sidebar */
	add_filter('widget_text', 'do_shortcode');
