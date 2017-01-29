/*
Plugin Name: AMY Slider for Visual Composer
Description: Adds AMY Slider to your VC
Author: Andrey Boyadzhiev - Cray Themes
Version: 2.0
Author URI: http://themes.cray.bg
Plugin URI: http://themeforest.net/user/CrayThemes/portfolio
*/
jQuery(window).load(function($) {
 	jQuery('.ct_amy_initloader').removeClass('ct_amy_fadeinup').addClass("ct_amy_fadeoutdown");
})
jQuery(document).ready(function(){
	'use strict';
	var themes,
		selectedThemeIndex,
		instructionsTimeout,
		deck;
	window.scrollinit = function (sliderID, sliderFirstSlide, sliderThumbSize, sliderSlideshow , sliderSlideshowSpeed, sliderParallax){
		var waitForFinalEvent = (function () {
			var timers = {};
			return function (callback, ms, uniqueId){
				if (!uniqueId){
				  uniqueId = "Don't call this twice without a uniqueId";
				}
				if (timers[uniqueId]) {
				  clearTimeout (timers[uniqueId]);
				}
				timers[uniqueId] = setTimeout(callback, ms);
				};
		})();
		setTimeout(function(){
			jQuery('.ct_amy_initloader').css('display','none');
			jQuery('.cq-tooltip').css('opacity','1');
			jQuery('.cq-hotspots').css('opacity','1');
		},1100)
		jQuery(window).resize(function (){
			waitForFinalEvent(function(){
                 var maxHeight = -1;
                    jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').each(function() {
                        maxHeight = maxHeight > jQuery(this).height() ? maxHeight : jQuery(this).height();
                    });
                    jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').css('margin-bottom', '-'+maxHeight/2+'px');
                
                
			}, 1000, "slider resize");
			
		});
		if(sliderParallax == 'yes'){
			var startparallax = function(){
				jQuery('#ct_as_amy_sliderid'+sliderID).ct_amy_parallax();
			}
			setTimeout(startparallax,400);
		}
		var deck = bespoke.from('#ct_as_amy_sliderid'+sliderID);
		initThemeSwitching()
		function initThemeSwitching() {
			themes = [
				'ct_amy_circle',
				'ct_amy_cube',
				'ct_amy_carousel',
				'ct_amy_concave',
				'ct_amy_coverflow',
				'ct_amy_coverflow2d',
				'ct_amy_spiraltop',
				'ct_amy_spiralbottom',
				'ct_amy_classic'
			];
			selectedThemeIndex = 0;
			deck.slide(sliderFirstSlide);
			initKeys();
			initButtons();
			initSlideGestures();
			initClickInactive();
			
		};
		
		//Navigation
		//==================================================
		function initButtons() {
			jQuery('#arrownav'+sliderID+' .next-arrow').click(function(){ gonext();});
			jQuery('#arrownav'+sliderID+' .prev-arrow').click(function(){ goprev();});
		}
		var stopnextslide = 0;
		function gonext() {
			var stopnextslide = 0;
			var n = jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').length;
			jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').each(function () {
				if (jQuery(this).hasClass('bespoke-active') && Number(jQuery(this).attr('rel'))+1 == n) {
					deck.slide(0);
					stopnextslide = 1;
				}
			});
			if(stopnextslide != 1){
				deck.next();
			}
		};
		function goprev() {
			var stopnextslide = 0;
			var n = jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').length;
			
			jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').each(function () {
				if (jQuery(this).hasClass('bespoke-active') && Number(jQuery(this).attr('rel')) == 0) {
					deck.slide(n-1);
					stopnextslide = 1;
				}
			});
			if(stopnextslide != 1){
				deck.prev();
			}
		};
		//Keyboard navigation
		//==================================================
		function initKeys(e) {
			if (/Firefox/.test(navigator.userAgent)) {
				document.addEventListener('keydown', function(e) {
					if (e.which >= 37 && e.which <= 40) {
						e.preventDefault();
					}
				});
			}
			window.gokb = function(e) {
				var key = e.which;
				if(key === 37){
					if (jQuery('#ct_as_amy_sliderid'+sliderID).is(':hover')) {
						deck.prev();
					}
				}
				if(key === 39 ){
					if (jQuery('#ct_as_amy_sliderid'+sliderID).is(':hover')) {
						deck.next();
					}
				}
				/*if(key === 38){
					if (jQuery('#ct_as_amy_sliderid'+sliderID).is(':hover')) {
						prevTheme();
					}
				}
				if(key === 40){
					if (jQuery('#ct_as_amy_sliderid'+sliderID).is(':hover')) {
					nextTheme();
					}
				}*/
				
			};
			document.addEventListener('keydown', gokb);
		}
		function extractDelta(e) {
			if (e.wheelDelta) {
				return e.wheelDelta;
			}
			if (e.originalEvent.detail) {
				return e.originalEvent.detail* -40;
			}
			if (e.originalEvent && e.originalEvent.wheelDelta) {
				return e.originalEvent.wheelDelta;
			}
		}
		//Navigation for touch devices
		//==================================================
		function initSlideGestures() {
			var start = 0;
			var main = document.getElementById('ct_amy_main'+sliderID),
				startPosition,
				delta,
				singleTouch = function(fn, preventDefault) {
					return function(e) {
						if(e.touches.length === 1){
							fn(e.touches[0].pageY);
						}
					};
				},
				touchstart = singleTouch(function(position) {
					startPosition = position;
					delta = 0;
						start = 0;
						main.addEventListener('touchend', touchend); 
				}),
	
				touchmove = singleTouch(function(position) {
					delta = position - startPosition;
				}, true),
				
				touchend = function() {	
					if (Math.abs(delta) < 50) {
						return;
					}
					if(delta > 0){
						deck.prev();
					}else{
						deck.next();
					}
				};
			window.remvoetuch = function(){
				main.removeEventListener('touchstart', touchstart);
				main.removeEventListener('touchmove', touchmove);
				main.removeEventListener('touchend', touchend);
			};
			window.addtuch = function(){
				main.addEventListener('touchstart', touchstart);
				main.addEventListener('touchmove', touchmove);
				main.addEventListener('touchend', touchend);
			};
			window.addtuch();
		}
		
		function isTouch() {
			return !!('ontouchstart' in window) || navigator.msMaxTouchPoints;
		}
	
		function modulo(num, n) {
			return ((num % n) + n) % n;
		}
		//Mouse click navigation
		//==================================================
		function initClickInactive(){
			var n = jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').length;
			jQuery('#ct_as_amy_sliderid'+sliderID+' .ct_amy_cn_style').click(function() {
				var page = jQuery(this).parent().attr('rel');
				if( jQuery(this).parent().hasClass('bespoke-inactive') ){
				deck.slide(page);
				}
			});
		}
		function selectTheme(index) {
			var theme = themes[index];
			var thestyle = theme + ' woocommerce '+sliderThumbSize;
			jQuery('#ct_amy_main'+sliderID).removeClass();
			jQuery('#ct_amy_main'+sliderID).addClass(thestyle);
			selectedThemeIndex = index;
		}
		function nextTheme() {
			offsetSelectedTheme(1);
		
		}
		function prevTheme() {
			offsetSelectedTheme(-1);
			
		}
		function offsetSelectedTheme(n) {
			selectTheme(modulo(selectedThemeIndex + n, themes.length));
		}
		//only for demo purpose 
		/*setInterval(function(){
			if(sliderID == 1){
					nextTheme();
					}
		},4000);*/
		if(sliderSlideshow == 'yes'){
			var autoslide = function(){
				stopnextslide = 0;
				var n = jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').length;
				
				jQuery('#ct_as_amy_sliderid'+sliderID+' ct_amy_section').each(function () {
					if (jQuery(this).hasClass('bespoke-active') && Number(jQuery(this).attr('rel'))+1 == n) {
						deck.slide(0);
						stopnextslide = 1;
					}
				});
				if(stopnextslide != 1){
					deck.next();
					
				}
			}
			jQuery('#ct_as_amy_sliderid'+sliderID).hover(function() {
				
				window.clearInterval(autorotateposts);
			}, function(){
					window.clearInterval(autorotateposts);
					 autorotateposts = setInterval(autoslide, sliderSlideshowSpeed);
			})
			var autorotateposts = setInterval(autoslide , sliderSlideshowSpeed);	
		};
	};
}('scrollinit', this, document));