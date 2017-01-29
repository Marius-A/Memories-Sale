<?php
	$showRTL = false;
	$showLTR = false;
	if(WGUtils::isLanguageRTL($this->original_l)) { //Right lo left language
		if(WGUtils::hasLanguageLTR(explode(",",$this->destination_l)))
			$showLTR = true;
	}
	else { //Left to right language	
		if(WGUtils::hasLanguageRTL(explode(",",$this->destination_l)))
			$showRTL = true;
	}
?>
<div class="wrap">
	<?php if($this->allowed==0) { ?>
	<div class="wg-status-box">
		<h3><?php echo sprintf(__( 'Weglot Translate service is not active because you reach the end of the trial period.', 'weglot' ),$this->userInfo['limit']); ?></h3>
		<p><?php _e('To reactivate the service, please <a target="_blank" href="https://weglot.com/change-plan">upgrade your plan</a>','weglot'); ?></p>
	</div>
	<?php } ?>
	
	<?php if(esc_attr(get_option('show_box'))=="on") { ?>
		<div class="wgbox-blur">
			<div class="wgbox">
				<div class="wgclose-btn"><?php _e('Close','weglot'); ?></div>
				<h3 class="wgbox-title"><?php _e('Well done! Your website is now multilingual','weglot'); ?></h3>
				<p class="wgbox-text"><?php _e('Go on your website, there is a language switcher, try it :)','weglot'); ?></p>
				<a class="wgbox-button button button-primary" href="<?php echo $this->home_dir; ?>/" target="_blank"><?php _e('Go on my front page','weglot'); ?></a>
				<p class="wgbox-subtext"><?php _e('Next step, edit your translations directly in your Weglot account.','weglot'); ?></p>
			</div>
		</div>
		<?php list($wgfirstlang) = explode(',', get_option("destination_l"));
			if(strlen($wgfirstlang)==2) { ?>
		<iframe style="visibility:hidden;" src="<?php echo $this->home_dir; ?>/<?php echo $wgfirstlang; ?>/" width=1 height =1 ></iframe>
		<?php } ?>
	<?php update_option('show_box','off'); }  ?>
	<form class="wg-widget-option-form" method="post" action="options.php">
		<?php settings_fields( 'my-plugin-settings-group' ); ?>
		<?php do_settings_sections( 'my-plugin-settings-group' ); ?>
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php _e( 'Main configuration', 'weglot' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e( 'API Key', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php echo sprintf(__( 'Login to %s to get your API key', 'weglot' ),'<a target="_blank" href="https://weglot.com/register-wordpress">Weglot</a>'); ?></p></th>
			<td><input type="text" name="project_key" value="<?php echo esc_attr( get_option('project_key') ); ?>" placeholder="wg_XXXXXXXX" required /></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Original Language', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php _e( 'What is the original (current) language of your website?', 'weglot' ); ?></p></th>
			<td>
				<select name="original_l" style="width :200px;">
					<option <?php if(esc_attr( get_option('original_l') )=="fr") { echo "selected"; } ?> value="fr"><?php _e( 'French', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="en") { echo "selected"; } ?> value="en"><?php _e( 'English', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="zh") { echo "selected"; } ?> value="zh"><?php _e( 'Simplified Chinese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="tw") { echo "selected"; } ?> value="tw"><?php _e( 'Traditional Chinese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ru") { echo "selected"; } ?> value="ru"><?php _e( 'Russian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="de") { echo "selected"; } ?> value="de"><?php _e( 'German', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="es") { echo "selected"; } ?> value="es"><?php _e( 'Spanish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sq") { echo "selected"; } ?> value="sq"><?php _e( 'Albanian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ar") { echo "selected"; } ?> value="ar"><?php _e( 'Arabic', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="hy") { echo "selected"; } ?> value="hy"><?php _e( 'Armenian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="az") { echo "selected"; } ?> value="az"><?php _e( 'Azerbaijani', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="af") { echo "selected"; } ?> value="af"><?php _e( 'Afrikaans', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="eu") { echo "selected"; } ?> value="eu"><?php _e( 'Basque', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="be") { echo "selected"; } ?> value="be"><?php _e( 'Belarusian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="bg") { echo "selected"; } ?> value="bg"><?php _e( 'Bulgarian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="bs") { echo "selected"; } ?> value="bs"><?php _e( 'Bosnian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="vi") { echo "selected"; } ?> value="vi"><?php _e( 'Vietnamese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="hu") { echo "selected"; } ?> value="hu"><?php _e( 'Hungarian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ht") { echo "selected"; } ?> value="ht"><?php _e( 'Haitian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="nl") { echo "selected"; } ?> value="nl"><?php _e( 'Dutch', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="el") { echo "selected"; } ?> value="el"><?php _e( 'Greek', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ka") { echo "selected"; } ?> value="ka"><?php _e( 'Georgian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="da") { echo "selected"; } ?> value="da"><?php _e( 'Danish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="he") { echo "selected"; } ?> value="he"><?php _e( 'Hebrew', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="id") { echo "selected"; } ?> value="id"><?php _e( 'Indonesian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ga") { echo "selected"; } ?> value="ga"><?php _e( 'Irish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="it") { echo "selected"; } ?> value="it"><?php _e( 'Italian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="is") { echo "selected"; } ?> value="is"><?php _e( 'Icelandic', 'weglot' ); ?></option>						
					<option <?php if(esc_attr( get_option('original_l') )=="kk") { echo "selected"; } ?> value="kk"><?php _e( 'Kazakh', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ca") { echo "selected"; } ?> value="ca"><?php _e( 'Catalan', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ky") { echo "selected"; } ?> value="ky"><?php _e( 'Kyrgyz', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ko") { echo "selected"; } ?> value="ko"><?php _e( 'Korean', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="lv") { echo "selected"; } ?> value="lv"><?php _e( 'Latvian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="lt") { echo "selected"; } ?> value="lt"><?php _e( 'Lithuanian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="mg") { echo "selected"; } ?> value="mg"><?php _e( 'Malagasy', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ms") { echo "selected"; } ?> value="ms"><?php _e( 'Malay', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="mt") { echo "selected"; } ?> value="mt"><?php _e( 'Maltese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="mk") { echo "selected"; } ?> value="mk"><?php _e( 'Macedonian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="mn") { echo "selected"; } ?> value="mn"><?php _e( 'Mongolian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="no") { echo "selected"; } ?> value="no"><?php _e( 'Norwegian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="fa") { echo "selected"; } ?> value="fa"><?php _e( 'Persian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="pl") { echo "selected"; } ?> value="pl"><?php _e( 'Polish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="pt") { echo "selected"; } ?> value="pt"><?php _e( 'Portuguese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ro") { echo "selected"; } ?> value="ro"><?php _e( 'Romanian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sr") { echo "selected"; } ?> value="sr"><?php _e( 'Serbian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sk") { echo "selected"; } ?> value="sk"><?php _e( 'Slovak', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sl") { echo "selected"; } ?> value="sl"><?php _e( 'Slovenian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sw") { echo "selected"; } ?> value="sw"><?php _e( 'Swahili', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="tg") { echo "selected"; } ?> value="tg"><?php _e( 'Tajik', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="th") { echo "selected"; } ?> value="th"><?php _e( 'Thai', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="tr") { echo "selected"; } ?> value="tr"><?php _e( 'Turkish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="uz") { echo "selected"; } ?> value="uz"><?php _e( 'Uzbek', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="uk") { echo "selected"; } ?> value="uk"><?php _e( 'Ukrainian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="fi") { echo "selected"; } ?> value="fi"><?php _e( 'Finnish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="hr") { echo "selected"; } ?> value="hr"><?php _e( 'Croatian', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="cs") { echo "selected"; } ?> value="cs"><?php _e( 'Czech', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="sv") { echo "selected"; } ?> value="sv"><?php _e( 'Swedish', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="et") { echo "selected"; } ?> value="et"><?php _e( 'Estonian', 'weglot' ); ?></option>				
					<option <?php if(esc_attr( get_option('original_l') )=="ja") { echo "selected"; } ?> value="ja"><?php _e( 'Japanese', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="hi") { echo "selected"; } ?> value="hi"><?php _e( 'Hindi', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('original_l') )=="ur") { echo "selected"; } ?> value="ur"><?php _e( 'Urdu', 'weglot' ); ?></option>
				</select>					
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Destination Languages', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php _e( 'Write the two-letters codes, separated by a comma. Supported languages are <a target="_blank" href="https://weglot.com/translation-api#languages_code">here</a>.', 'weglot' ); ?></p></th>
			<td><input type="text" name="destination_l" value="<?php echo esc_attr( get_option('destination_l') ); ?>" placeholder="en,es" required /><?php if($this->userInfo['plan']<=0) { ?><p class="wg-fsubtext"><?php _e( 'On the free plan, you can only chose one language with a maximum of 2000 words. If you want to use more than 1 language and 2000 words, please <a target="_blank" href="https://weglot.com/change-plan">upgrade your plan</a>.', 'weglot' ); ?></p><?php } ?><?php if($this->userInfo['plan']>=18 && $this->userInfo['plan']<=19) { ?><p class="wg-fsubtext"><?php _e( 'On the Starter plan, you can only chose one language. If you want to use more than 1 language, please <a target="_blank" href="https://weglot.com/change-plan">upgrade your plan</a>.', 'weglot' ); ?></p><?php } ?></td>
			</tr>
		</table>	
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php echo __( 'Language button appearance', 'weglot' )." ".__( '(Optional)', 'weglot' ); ?></h3>
		<p class="preview-text"><?php _e( 'Preview:', 'weglot' ); ?></p><div class="wg-widget-preview"></div>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e( 'Dropdown?', 'weglot' ); ?></th>
			<td><input id="id_is_dropdown" type="checkbox" name="is_dropdown"  <?php if(esc_attr(get_option('is_dropdown'))=="on") echo "checked"; ?>  /><label for="id_is_dropdown" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want the button to be a dropdown box.', 'weglot' ); ?></label></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'With flags?', 'weglot' ); ?></th>
			<td><input id="id_with_flags" type="checkbox" name="with_flags"  <?php if(esc_attr(get_option('with_flags'))=="on") echo "checked"; ?>  /><label for="id_with_flags" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want flags in the language button.', 'weglot' ); ?></label></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Type of flags', 'weglot' ); ?></th>
			<td>	
				<select name="type_flags" style="width :200px;">
					<option <?php if(esc_attr( get_option('type_flags') )=="0") { echo "selected"; } ?> value="0"><?php _e( 'Rectangle mat', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('type_flags') )=="1") { echo "selected"; } ?> value="1"><?php _e( 'Rectangle bright', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('type_flags') )=="2") { echo "selected"; } ?> value="2"><?php _e( 'Square', 'weglot' ); ?></option>
					<option <?php if(esc_attr( get_option('type_flags') )=="3") { echo "selected"; } ?> value="3"><?php _e( 'Circle', 'weglot' ); ?></option>
				</select>
				<div class="flag-style-openclose"><?php _e('Change country flags','weglot'); ?></div>
				<div class="flag-style-wrapper" style="display:none;">
					<select class="flag-en-type">
						<option value=0><?php _e('Choose english flag:','weglot'); ?></option>
						<option value=0><?php _e('United Kingdom (default)','weglot'); ?></option>
						<option value=1><?php _e('United States','weglot'); ?></option>
						<option value=2><?php _e('Australia','weglot'); ?></option>
						<option value=3><?php _e('Canada','weglot'); ?></option>
						<option value=4><?php _e('New Zealand','weglot'); ?></option>
						<option value=5><?php _e('Jamaica','weglot'); ?></option>
						<option value=6><?php _e('Ireland','weglot'); ?></option>
					</select>
					<select class="flag-es-type">
						<option value=0><?php _e('Choose spanish flag:','weglot'); ?></option>
						<option value=0><?php _e('Spain (default)','weglot'); ?></option>
						<option value=1><?php _e('Mexico','weglot'); ?></option>
						<option value=2><?php _e('Argentina','weglot'); ?></option>
						<option value=3><?php _e('Colombia','weglot'); ?></option>
						<option value=4><?php _e('Peru','weglot'); ?></option>
						<option value=5><?php _e('Bolivia','weglot'); ?></option>
						<option value=6	><?php _e('Uruguay','weglot'); ?></option>
						<option value=7	><?php _e('Venezuela','weglot'); ?></option>
						<option value=8	><?php _e('Chile','weglot'); ?></option>
						<option value=9	><?php _e('Ecuador','weglot'); ?></option>
						<option value=10><?php _e('Guatemala','weglot'); ?></option>
						<option value=11><?php _e('Cuba','weglot'); ?></option>
						<option value=12><?php _e('Dominican Republic','weglot'); ?></option>
						<option value=13><?php _e('Honduras','weglot'); ?></option>
						<option value=14><?php _e('Paraguay','weglot'); ?></option>
						<option value=15><?php _e('El Salvador','weglot'); ?></option>
						<option value=16><?php _e('Nicaragua','weglot'); ?></option>
						<option value=17><?php _e('Costa Rica','weglot'); ?></option>
						<option value=18><?php _e('Puerto Rico','weglot'); ?></option>
						<option value=19><?php _e('Panama','weglot'); ?></option>
					</select>
					<select class="flag-pt-type">
						<option value=0><?php _e('Choose portugese flag:','weglot'); ?></option>
						<option value=0><?php _e('Brazil (default)','weglot'); ?></option>
						<option value=1><?php _e('Portugal','weglot'); ?></option>
					</select>
					<p><?php _e('If you want to change another flag, just ask us.','weglot'); ?></p>
				</div>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'With name?', 'weglot' ); ?></th>
			<td><input id="id_with_name" type="checkbox" name="with_name"  <?php if(esc_attr(get_option('with_name'))=="on") echo "checked"; ?>  /><label for="id_with_name" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want to display the name of languages.', 'weglot' ); ?></label></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Full name?', 'weglot' ); ?></th>
			<td><input id="id_is_fullname" type="checkbox" name="is_fullname"  <?php if(esc_attr(get_option('is_fullname'))=="on") echo "checked"; ?>  /><label for="id_is_fullname" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want the name of the languge. Don\'t check if you want the language code.', 'weglot' ); ?></label></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Override CSS', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php _e( 'Don\'t change it unless you want a specific style for your button.', 'weglot' ); ?></p></th>
			<td><textarea type="text" rows = 10 cols = 30 name="override_css" placeholder="<?php _e( '.country-selector {
margin-bottom : 20px;
background-color : green!important;
}
.country-selector a {
color : blue!important;
}', 'weglot' ); ?>"><?php echo esc_attr( get_option('override_css') ); ?></textarea><textarea type="text" name="flag_css"  style="display:none;" ><?php echo esc_attr( get_option('flag_css') ); ?></textarea></td>
			</tr>
		</table>
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php echo __( 'Language button position', 'weglot' )." ".__( '(Optional)', 'weglot' ); ?></h3>
		<h4 style="font-size:14px;line-height: 1.3;font-weight: 600;"><?php _e( 'Where will be the language button on my website? By default, bottom right.','weglot'); ?></h4>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e( 'In menu?', 'weglot' ); ?></th>
			<td><input id="id_is_menu" type="checkbox" name="is_menu"  <?php if(esc_attr(get_option('is_menu'))=="on") echo "checked"; ?>  /><label for="id_is_menu" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want to display the button in the naviguation menu.', 'weglot' ); ?></label></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'As a widget?', 'weglot' ); ?></th>
			<td><p style="font-weight: normal;font-style: italic;display: inline-block;"><?php _e( 'You can place the button in a Widget area. Go in Appearance -> Widget and drag and drop Weglot Translate widget where you want.', 'weglot' ); ?></p></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'With a shortcode?', 'weglot' ); ?></th>
			<td><p style="font-weight: normal;font-style: italic;display: inline-block;"><?php _e( 'You can use weglot short code [weglot_switcher] wherever you want to place the button.', 'weglot' ); ?></p></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'In the sourcecode?', 'weglot' ); ?></th>
			<td><p style="font-weight: normal;font-style: italic;display: inline-block;"><?php _e( 'You can add the code &lt;div id=&quot;weglot_here&quot;&gt;&lt;/div&gt; wherever you want in the source code of your HTML page. The button will appear at this place.', 'weglot' ); ?></p></td>
			</tr>
		</table>
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php echo __( 'Translation Exclusion', 'weglot' )." ".__( '(Optional)', 'weglot' );; ?></h3>
		<p><?php _e( 'By default, every page is translated. You can exclude parts of a page or a full page here.', 'weglot' ); ?></p>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e( 'Exclude URL here', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php _e( 'You can write regex.', 'weglot' ); ?><p></th>
			<td><textarea type="text" rows = 3 cols = 30 name="exclude_url" placeholder=""><?php echo esc_attr( get_option('exclude_url') ); ?></textarea></td>
			</tr>
			<tr valign="top">
			<th scope="row"><?php _e( 'Exclude blocks', 'weglot' ); ?><p style="font-weight:normal;margin-top:2px;"><?php _e( 'Enter CSS selectors, separated by commas', 'weglot' ); ?><p></th>
			<td><textarea type="text" rows = 3 cols = 30 name="exclude_blocks" placeholder="#top-menu,footer a,.title-3"><?php echo esc_attr( get_option('exclude_blocks') ); ?></textarea></td>
			</tr>
		</table>
		<?php if($this->userInfo['plan']>0) { ?>
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php echo __( 'Other options', 'weglot' )." ".__( '(Optional)', 'weglot' );; ?></h3>
		<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e( 'Auto redirect?', 'weglot' ); ?></th>
			<td><input id="id_auto_switch"  type="checkbox" name="wg_auto_switch"  <?php if(esc_attr(get_option('wg_auto_switch'))=="on") echo "checked"; ?>  /><label for="id_auto_switch" style="font-weight: normal;margin-left: 20px;font-style: italic;display: inline-block;"><?php _e( 'Check if you want to redirect users based on their browser language.', 'weglot' ); ?></label></td>
		</tr>
		</table>
		<?php } ?>
		<?php if($showLTR || $showRTL) { 
			$ltrOrRtl = $showLTR ? __('Left to Right languages','weglot'):__('Right to Left languages','weglot');
		?>
		<h3 style="border-bottom:1px solid #c0c0c0;padding-bottom:10px;max-width:800px;margin-top:40px;"><?php echo __( 'Customize style for ', 'weglot' ).$ltrOrRtl." ".__( '(Optional)', 'weglot' );; ?></h3>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php echo sprintf(__( 'Write CSS rules to apply for on %s page', 'weglot' ),$ltrOrRtl); ?><p style="font-weight:normal;margin-top:2px;"><p></th>
			<td><textarea type="text" rows = 5 cols = 30 name="rtl_ltr_style" placeholder="<?php _e( 'body {
text-align :right;
}', 'weglot' ); ?>"><?php echo esc_attr( get_option('rtl_ltr_style') ); ?></textarea></td>
			</tr>
		</table>
		<?php } ?>
		<?php submit_button(); ?>
	</form><?php if(esc_attr(get_option('show_box'))=="off") { ?><div class="wginfobox">
		<h3><?php _e('Where are my translations?','weglot'); ?></h3>
		<div>
			<p><?php _e('You can find all your translations in your Weglot account:','weglot'); ?></p>
			<a href="<?php _e('https://weglot.com/dashboard','weglot'); ?>" target="_blank" class="wg-editbtn"><?php _e('Edit my translations','weglot'); ?></a>
		</div>
	</div><?php } ?>
	<br>
	<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/weglot?rate=5#postform">
		<?php _e( 'Love Weglot? Give us 5 stars on WordPress.org :)', 'weglot' ); ?>
	</a>
	<br><br>
	<i class="fa fa-question-circle" aria-hidden="true" style="font-size : 17px;"></i><p style="display:inline-block; margin-left:5px;"><?php _e('If you need any help, you can contact us on our live chat on <a href="https://weglot.com/" target="_blank">weglot.com</a> or email us at support@weglot.com','weglot'); ?></p>
	<br><br><br>
	<h2></h2>
</div>