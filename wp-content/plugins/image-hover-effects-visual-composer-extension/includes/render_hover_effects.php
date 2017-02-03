<?php 
	if ( ! defined( 'ABSPATH' ) ) exit; 
 ?>
<div class="ih-item <?php if(($caption_style=='square' && $hover_effect=='effect8')|| ($caption_style=='circle' && $hover_effect=='effect6') ){ echo 'scale_up';} ?> <?php echo $caption_style; ?> <?php echo $hover_effect; ?> <?php echo $caption_direction; ?>" >
	<a href="<?php echo $caption_url; ?>" target="<?php echo $caption_url_target; ?>">
	<?php if($caption_style=='circle' && $hover_effect=='effect1' ){ echo '<div class="spinner"></div>';} ?>
	  
	  <div class="img"><img class="responsiveimage" src="<?php echo $image_url; ?>" alt="img"></div>
	  <?php if($caption_style=='square' && $hover_effect=='effect4' ){ echo '<div class="mask1"></div><div class="mask2"></div>';} ?>

	  <?php if($caption_style=='circle' && $hover_effect=='effect8' ){?>
	  <div class="info-container" >
	    <div class="info">
	      <h3><?php echo $ihe_heading; ?></h3>
	      <?php echo $content; ?>
	    </div>
	  </div>
	  <?php }else{ ?>
		<div class="info">
	    <div class="info-back" >
	      <h3><?php echo $ihe_heading; ?></h3>
	      <?php echo $content; ?>
	    </div>
	  </div>
	  <?php } ?>
	</a>
</div>