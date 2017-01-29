jQuery(function($) {

generateWGWidgetCode();
$( "select[name=original_l]" ).change(function() {
	generateWGWidgetCode();
});
$( "select[name=type_flags]" ).change(function() {
	generateWGWidgetCode();
});
$( "input[name=destination_l]" ).blur(function() {
	generateWGWidgetCode();
});
$('input[name=with_flags]').change(function() {
	generateWGWidgetCode();
});
$('input[name=with_name]').change(function() {
	generateWGWidgetCode();
});
$('input[name=is_dropdown]').change(function() {
	generateWGWidgetCode();
});
$('input[name=is_fullname]').change(function() {
	generateWGWidgetCode();
});
$('input[name=is_menu]').change(function() {
	if(this.checked) {
		$('input[name=is_dropdown]').prop('checked', false);
		generateWGWidgetCode(); 
    }
});
$('textarea[name=override_css]').blur(function() {
	var style_value = $(this).val();
	if(style_value!="") {
		var style = $('<style wgstyle>'+$(this).val()+'</style>');
		$('style[wgstyle]').remove();
		$('html > body').append(style);
		generateWGWidgetCode();
	}
	else {
		$('style[wgstyle]').remove();
	}
});
$( "select.flag-en-type" ).change(function() {
	refreshFlagCSS();
	generateWGWidgetCode();
});
$( "select.flag-es-type" ).change(function() {
	refreshFlagCSS();
	generateWGWidgetCode();
});
$( "select.flag-pt-type" ).change(function() {
	refreshFlagCSS();
	generateWGWidgetCode();
});

$('.wgclose-btn').click(function() {
	$('.wgbox-blur').hide();
});
$('.flag-style-openclose').click(function() {
	$('.flag-style-wrapper').toggle();
});
$('input[name=project_key]').blur(function() {
	var key = $(this).val();
	$.getJSON( "https://weglot.com/api/user-info?api_key="+key, function( data ) {
		$('.wg-keyres').remove();
		$('input[name=project_key]').after('<span class="wg-keyres wg-okkey"></span>');
		$('.wg-widget-option-form input[type=submit]').prop('disabled', false);
	}).fail(function() {
		$('.wg-keyres').remove();
		$('input[name=project_key]').after('<span class="wg-keyres wg-nokkey"></span>')
		$('.wg-widget-option-form input[type=submit]').prop('disabled', true);
	  });
});
function generateWGWidgetCode() {
	var original = $( "select[name=original_l]" ).val();
	var destination = $( "input[name=destination_l]" ).val();
	var dests = destination.split(',');
	var list='';
	
	var flag_class =  "";
	if($('input[name=with_flags]').is(':checked')) {
		flag_class += "wg-flags ";
		flag_class += ($("select[name=type_flags]").val()=="0") ? "":"flag-"+$( "select[name=type_flags]" ).val()+" ";
	}
	
	
	if(destination.length>1) {
		list +='<ul>';
		for(var i=0;i<dests.length;i++) { 
				var d = dests[i];
				var l_name = $('input[name=with_name]').is(':checked') ? ($('input[name=is_fullname]').is(':checked') ? getLangNameFromCode(d,false):d.toUpperCase()):"";
				list += '<li class="wg-li '+flag_class+d+'"><a href="#">'+l_name+'</a></li>';
		}
		list +='</ul>';
	}
	
	var current_name = $('input[name=with_name]').is(':checked') ? ($('input[name=is_fullname]').is(':checked') ? getLangNameFromCode(original,false):original.toUpperCase()):"";
	
	if($('input[name=is_dropdown]').is(':checked')) {
		var opt_class = "wg-drop";
	}
	else { 
		var opt_class = "wg-list";
	}
	
	
	
	var style = $('<style wgstyle1>'+$('textarea[name=flag_css]').text()+'</style>');
	$('style[wgstyle1]').remove();
	$('html > body').append(style);
	
				
	var button = '<aside id="weglot_switcher" wg-notranslate class="'+opt_class+' country-selector closed" onclick="openClose(this);"><div class="wgcurrent wg-li '+flag_class+original+'"><a href="javascript:void(0);">'+current_name+'</a></div>'+list+'</aside>';
	$(".wg-widget-preview").html(button);
}

function refreshFlagCSS() {
	var en_flags = new Array();
	var es_flags = new Array();
	var pt_flags = new Array();
	
	en_flags[1] = [7200,7841,48,5256]; 
	en_flags[2] = [7008,449,3048,5400]; 
	en_flags[3] = [7040,1281,2712,4968]; 
	en_flags[4] = [6368,5217,1224,5232]; 
	en_flags[5] = [5216,3585,1944,4320]; 
	en_flags[6] = [6176,3457,2016,240]; 

	es_flags[1] = [7072,4641,3144,2016]; 
	es_flags[2] = [5824,353,2880,1704]; 
	es_flags[3] = [5248,1601,2568,5328]; 
	es_flags[4] = [896,5793,1032,4296]; 
	es_flags[5] = [4256,897,4104,3072]; 
	es_flags[6] = [4544,7905,216,4416]; 
	es_flags[7] = [4512,8065,192,3168]; 
	es_flags[8] = [5184,1473,2496,5664]; 
	es_flags[9] = [3648,2145,4677,2232]; 
	es_flags[10] = [4704,3009,3240,1848]; 
	es_flags[11] = [5312,1825,3936,5616]; 
	es_flags[12] = [4800,2081,3624,5472]; 
	es_flags[13] = [4640,3201,2160,4440]; 
	es_flags[14] = [2752,5761,3432,1776]; 
	es_flags[15] = [4672,2209,3360,3432]; 
	es_flags[16] = [224,5249,3168,4776]; 
	es_flags[17] = [6400,1729,3792,288]; 
	es_flags[18] = [1856,5953,96,4536]; 
	es_flags[19] = [4608,5697,1056,1872]; 
	
	pt_flags[1] = [6112,5921,528,5640]; 
	
	var enval = $("select.flag-en-type").val();
	var esval = $("select.flag-es-type").val();
	var ptval = $("select.flag-pt-type").val();
	
	
	var en_style = enval<=0 ? "":".wg-li.en a:before { background-position: -"+en_flags[enval][0]+"px 0; } .wg-li.flag-1.en a:before { background-position: -"+en_flags[enval][1]+"px 0; } .wg-li.flag-2.en a:before { background-position: -"+en_flags[enval][2]+"px 0; } .wg-li.flag-3.en a:before { background-position: -"+en_flags[enval][3]+"px 0; } ";
	var es_style = esval<=0 ? "":".wg-li.es a:before { background-position: -"+es_flags[esval][0]+"px 0; } .wg-li.flag-1.es a:before { background-position: -"+es_flags[esval][1]+"px 0; } .wg-li.flag-2.es a:before { background-position: -"+es_flags[esval][2]+"px 0; } .wg-li.flag-3.es a:before { background-position: -"+es_flags[esval][3]+"px 0; } ";
	var pt_style = ptval<=0 ? "":".wg-li.pt a:before { background-position: -"+pt_flags[ptval][0]+"px 0; } .wg-li.flag-1.pt a:before { background-position: -"+pt_flags[ptval][1]+"px 0; } .wg-li.flag-2.pt a:before { background-position: -"+pt_flags[ptval][2]+"px 0; } .wg-li.flag-3.pt a:before { background-position: -"+pt_flags[ptval][3]+"px 0; } ";
	
	$('textarea[name=flag_css]').text(en_style+es_style+pt_style);
}
function getLangNameFromCode (original,english) {
	switch (original) {
		case "sq":
			return english ? "Albanian":"Shqip";	
		case "en":
			return english ? "English":"English";
		case "ar":
			return english ? "Arabic":"‏العربية‏";
		case "hy":
			return english ? "Armenian":"հայերեն";			
		case "az":
			return english ? "Azerbaijani":"Azərbaycan dili";	
		case "af":
			return english ? "Afrikaans":"Afrikaans";
		case "eu":
			return english ? "Basque":"Euskara";	
		case "be":
			return english ? "Belarusian":"Беларуская";	
		case "bg":
			return english ? "Bulgarian":"български";	
		case "bs":
			return english ? "Bosnian":"Bosanski";	
		case "cy":
			return english ? "Welsh":"Cymraeg";				
		case "vi":
			return english ? "Vietnamese":"Tiếng Việt";				
		case "hu":
			return english ? "Hungarian":"Magyar";				
		case "ht":
			return english ? "Haitian":"Kreyòl ayisyen";				
		case "gl":
			return english ? "Galician":"Galego";
		case "nl":
			return english ? "Dutch":"Nederlands";		
		case "el":
			return english ? "Greek":"Ελληνικά";			
		case "ka":
			return english ? "Georgian":"ქართული";
		case "da":
			return english ? "Danish":"Dansk";
		case "he":
			return english ? "Hebrew":"עברית";
		case "id":
			return english ? "Indonesian":"Bahasa Indonesia";				
		case "ga":
			return english ? "Irish":"Gaeilge";	
		case "it":
			return english ? "Italian":"Italiano";	
		case "is":
			return english ? "Icelandic":"Íslenska";	
		case "es":
			return english ? "Spanish":"Español";			
		case "kk":
			return english ? "Kazakh":"Қазақша";				
		case "ca":
			return english ? "Catalan":"Català";	
		case "ky":
			return english ? "Kyrgyz":"кыргызча";	
		case "zh":
			return english ? "Simplified Chinese":"中文 (简体)";
		case "tw":
			return english ? "Traditional Chinese":"中文 (繁體)";
		case "ko":
			return english ? "Korean":"한국어";
		case "lv":
			return english ? "Latvian":"Latviešu";	
		case "lt":
			return english ? "Lithuanian":"Lietuvių";	
		case "mg":
			return english ? "Malagasy":"Malagasy";	
		case "ms":
			return english ? "Malay":"Bahasa Melayu";
		case "mt":
			return english ? "Maltese":"Malti";	
		case "mk":
			return english ? "Macedonian":"Македонски";	
		case "mn":
			return english ? "Mongolian":"Монгол";	
		case "de":
			return english ? "German":"Deutsch";
		case "no":
			return english ? "Norwegian":"Norsk";
		case "fa":
			return english ? "Persian":"فارسی";	
		case "pl":
			return english ? "Polish":"Polski";
		case "pt":
			return english ? "Portuguese":"Português";		
		case "ro":
			return english ? "Romanian":"Română";	
		case "ru":
			return english ? "Russian":"Русский";		
		case "sr":
			return english ? "Serbian":"Српски";	
		case "sk":
			return english ? "Slovak":"Slovenčina";	
		case "sl":
			return english ? "Slovenian":"Slovenščina";	
		case "sw":
			return english ? "Swahili":"Kiswahili";	
		case "tg":
			return english ? "Tajik":"Тоҷикӣ";	
		case "th":
			return english ? "Thai":"ภาษาไทย";	
		case "tl":
			return english ? "Tagalog":"Tagalog";	
		case "tt":
			return english ? "Tatar":"Tatar";
		case "tr":
			return english ? "Turkish":"Türkçe";
		case "uz":
			return english ? "Uzbek":"O'zbek";	
		case "uk":
			return english ? "Ukrainian":"Українська";	
		case "fi":
			return english ? "Finnish":"Suomi";
		case "fr":
			return english ? "French":"Français";	
		case "hr":
			return english ? "Croatian":"Hrvatski";	
		case "cs":
			return english ? "Czech":"Čeština";	
		case "sv":
			return english ? "Swedish":"Svenska";	
		case "et":
			return english ? "Estonian":"Eesti";	
		case "ja":
			return english ? "Japanese":"日本語";	
		case "hi":
			return english ? "Hindi":"हिंदी";
		case "ur":
			return english ? "Urdu":"اردو";
	}
}

});