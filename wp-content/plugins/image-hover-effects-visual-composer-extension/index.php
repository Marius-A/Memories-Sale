<?php 

	/*
	Plugin Name: Image Hover Effects - Visual Composer Free Version
	Description: Add attractive hover effects to images in visual composer
	Plugin URI: http://webdevocean.com
	Author: Labib Ahmed
	Author URI: http://webdevocean.com
	Version: 1.0
	License: GPL2
	Text Domain: vc-image-hover
	*/
	
	/*
	
	    Copyright (C) 2016  Labib Ahmed  labib@najeebmediagroup.com
	*/
	if ( ! defined( 'ABSPATH' ) ) exit; 
	include 'plugin.class.php';
	if (class_exists('VC_Image_Hover_Effects_Free')) {
	    $obj_init = new VC_Image_Hover_Effects_Free;
	}
 ?>