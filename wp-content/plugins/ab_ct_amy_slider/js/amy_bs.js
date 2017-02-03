/*
Plugin Name: AMY Slider for Visual Composer
Description: Adds AMY Slider to your VC
Author: Andrey Boyadzhiev - Cray Themes
Version: 2.0
Author URI: http://themes.cray.bg
Plugin URI: http://themeforest.net/user/CrayThemes/portfolio
*/

/* Modernizr 2.8.3 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-csstransforms3d
 */
;window.Modernizr=function(a,b,c){function y(a){i.cssText=a}function z(a,b){return y(l.join(a+";")+(b||""))}function A(a,b){return typeof a===b}function B(a,b){return!!~(""+a).indexOf(b)}function C(a,b){for(var d in a){var e=a[d];if(!B(e,"-")&&i[e]!==c)return b=="pfx"?e:!0}return!1}function D(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:A(f,"function")?f.bind(d||b):f}return!1}function E(a,b,c){var d=a.charAt(0).toUpperCase()+a.slice(1),e=(a+" "+n.join(d+" ")+d).split(" ");return A(b,"string")||A(b,"undefined")?C(e,b):(e=(a+" "+o.join(d+" ")+d).split(" "),D(e,b,c))}var d="2.8.3",e={},f=b.documentElement,g="modernizr",h=b.createElement(g),i=h.style,j,k={}.toString,l=" -webkit- -moz- -o- -ms- ".split(" "),m="Webkit Moz O ms",n=m.split(" "),o=m.toLowerCase().split(" "),p={},q={},r={},s=[],t=s.slice,u,v=function(a,c,d,e){var h,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:g+(d+1),l.appendChild(j);return h=["&#173;",'<style id="s',g,'">',a,"</style>"].join(""),l.id=g,(m?l:n).innerHTML+=h,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=f.style.overflow,f.style.overflow="hidden",f.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),f.style.overflow=k),!!i},w={}.hasOwnProperty,x;!A(w,"undefined")&&!A(w.call,"undefined")?x=function(a,b){return w.call(a,b)}:x=function(a,b){return b in a&&A(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=t.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(t.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(t.call(arguments)))};return e}),p.csstransforms3d=function(){var a=!!E("perspective");return a&&"webkitPerspective"in f.style&&v("@media (transform-3d),(-webkit-transform-3d){#modernizr{left:9px;position:absolute;height:3px;}}",function(b,c){a=b.offsetLeft===9&&b.offsetHeight===3}),a};for(var F in p)x(p,F)&&(u=F.toLowerCase(),e[u]=p[F](),s.push((e[u]?"":"no-")+u));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)x(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof enableClasses!="undefined"&&enableClasses&&(f.className+=" "+(b?"":"no-")+a),e[a]=b}return e},y(""),h=j=null,e._version=d,e._prefixes=l,e._domPrefixes=o,e._cssomPrefixes=n,e.testProp=function(a){return C([a])},e.testAllProps=E,e.testStyles=v,e}(this,this.document);

(function(moduleName, window, document) {
    console.log('active');
	if(window.isrunedonce !=1){
	var from = function(selector, selectedPlugins) {;
		
			
			var parent = document.querySelector(selector),
				slides = [].slice.call(parent.children, 0),
				activeSlide = slides[0],
				deckListeners = {},
				relnum = 0,
				isfirst = '0',
				activate = function(index, customData) {
					
					if (!slides[index]) {
						return;
					}
					//var isfirst = '0';
					fire(deckListeners, 'deactivate', createEventData(activeSlide, customData));

					activeSlide = slides[index];

					slides.map(deactivate);
                    
					
					fire(deckListeners, 'activate', createEventData(activeSlide, customData));				
					addClass(activeSlide, 'active');
					removeClass(activeSlide, 'inactive');
                    
                    if (!Modernizr.csstransforms3d) { 
					jQuery( " ct_amy_section.bespoke-before-3" ).animate({ "margin-left": "-1400px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-2" ).animate({ "margin-left": "-966px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-1" ).animate({ "margin-left": "-570px" }, 300, "linear" );
					jQuery( " ct_amy_section.bespoke-active" ).animate({ "margin-left": "-175px" }, 300, "linear" );
					jQuery( " ct_amy_section" ).css("opacity","1");
					if(isfirst !=1){
					jQuery( " ct_amy_section.bespoke-after" ).css("margin-left","2000px");
					 isfirst = 1
					}
					jQuery( " ct_amy_section.bespoke-after-1" ).animate({ "margin-left": "220px" },300, "linear");
					jQuery( " ct_amy_section.bespoke-after-2" ).animate({ "margin-left": "616px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-after-3" ).animate({ "margin-left": "1200px" }, 300, "linear" );
					}
				},

				deactivate = function(slide, index) {
					
					var offset = index - slides.indexOf(activeSlide),
						offsetClass = offset > 0 ? 'after' : 'before';

					['before(-\\d+)?', 'after(-\\d+)?', 'active', 'inactive'].map(removeClass.bind(null, slide));
					slide !== activeSlide &&
						['inactive', offsetClass, offsetClass + '-' + Math.abs(offset)].map(addClass.bind(null, slide));
			
				
						
				},

				slide = function(index, customData) {
					fire(deckListeners, 'slide', createEventData(slides[index], customData)) && activate(index, customData);
					
				},

				next = function(customData) {
					var nextSlideIndex = slides.indexOf(activeSlide) + 1;
					
			
					fire(deckListeners, 'next', createEventData(activeSlide, customData)) && activate(nextSlideIndex, customData);
					 if (!Modernizr.csstransforms3d) {
				//	jQuery( " ct_amy_section.bespoke-before-3" ).animate({ "margin-left": "-1900px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-3" ).animate({ "margin-left": "-1400px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-2" ).animate({ "margin-left": "-966px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-1" ).animate({ "margin-left": "-570px" }, 300, "linear" );
					
					jQuery( " ct_amy_section.bespoke-after-1" ).animate({ "margin-left": "220px" },300, "linear");
					jQuery( " ct_amy_section.bespoke-after-2" ).animate({ "margin-left": "616px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-after-3" ).animate({ "margin-left": "1200px" }, 300, "linear" );
					jQuery( " ct_amy_section.bespoke-after-4" ).animate({ "margin-left": "1700px" }, 300, "linear" );
					}
					
					// window.location.hash = '!slide-'+jQuery( "section.bespoke-active" ).attr("rel");
				},

				prev = function(customData) {
					var prevSlideIndex = slides.indexOf(activeSlide) - 1;

					fire(deckListeners, 'prev', createEventData(activeSlide, customData)) && activate(prevSlideIndex, customData);
					 if (!Modernizr.csstransforms3d) {
					jQuery( " ct_amy_section.bespoke-before-3" ).animate({ "margin-left": "-1400px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-2" ).animate({ "margin-left": "-966px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-before-1" ).animate({ "margin-left": "-570px" }, 300, "linear" );
					
					jQuery( " ct_amy_section.bespoke-after-1" ).animate({ "margin-left": "220px" },300, "linear");
					jQuery( " ct_amy_section.bespoke-after-2" ).animate({ "margin-left": "616px" },300, "linear" );
					jQuery( " ct_amy_section.bespoke-after-3" ).animate({ "margin-left": "1200px" }, 300, "linear" );
					jQuery( " ct_amy_section.bespoke-after-4" ).animate({ "margin-left": "1700px" }, 300, "linear" );
					}
					
					// window.location.hash = '!slide-'+jQuery( "section.bespoke-active" ).attr("rel");

				},

				createEventData = function(slide, eventData) {
					eventData = eventData || {};
					eventData.index = slides.indexOf(slide);
					eventData.slide = slide;
					return eventData;
				},

				deck = {
					on: on.bind(null, deckListeners),
					off: off.bind(null, deckListeners),
					fire: fire.bind(null, deckListeners),
					slide: slide,
					next: next,
					prev: prev,
					parent: parent,
					slides: slides
				};
			
			addClass(parent, 'parent');
			
			slides.map(function(slide) {
				
				addClass(slide, 'slide');
				jQuery(slide).attr('rel', relnum);
				relnum++;
			});

			Object.keys(selectedPlugins || {}).map(function(pluginName) {
				var config = selectedPlugins[pluginName];
				config && plugins[pluginName](deck, config === true ? {} : config);
				
			});

			activate(0);

			decks.push(deck);
			
			return deck;
		},

		decks = [],

		bespokeListeners = {},

		on = function(listeners, eventName, callback) {
			(listeners[eventName] || (listeners[eventName] = [])).push(callback);
		},

		off = function(listeners, eventName, callback) {
			listeners[eventName] = (listeners[eventName] || []).filter(function(listener) {
				return listener !== callback;
			});
		},

		fire = function(listeners, eventName, eventData) {
			return (listeners[eventName] || [])
				.concat((listeners !== bespokeListeners && bespokeListeners[eventName]) || [])
				.reduce(function(notCancelled, callback) {
					return notCancelled && callback(eventData) !== false;
				}, true);
		},

		addClass = function(el, cls) {
		
			el.classList.add(moduleName + '-' + cls);
		},

		removeClass = function(el, cls) {
			el.className = el.className
				.replace(new RegExp(moduleName + '-' + cls +'(\\s|$)', 'g'), ' ')
				.replace(/^\s+|\s+$/g, '');
		},

		callOnAllInstances = function(method) {
			return function(arg) {
				decks.map(function(deck) {
					deck[method].call(null, arg);
				});
			};
		},

		bindPlugin = function(pluginName) {
			return {
				from: function(selector, selectedPlugins) {
					(selectedPlugins = selectedPlugins || {})[pluginName] = true;
					return from(selector, selectedPlugins);
				}
			};
		},

		makePluginForAxis = function(axis) {
			return function(deck) {
				var startPosition,
					delta;

				document.addEventListener('keydown', function(e) {
					var key = e.which;

					if (axis === 'X') {
						key === 37 && deck.prev();
						(key === 32 || key === 39) && deck.next();
					} else {
						key === 38 && deck.prev();
						(key === 32 || key === 40) && deck.next();
					}
				});

				deck.parent.addEventListener('touchstart', function(e) {
					if (e.touches.length) {
						startPosition = e.touches[0]['page' + axis];
						delta = 0;
					}
				});

				deck.parent.addEventListener('touchmove', function(e) {
					if (e.touches.length) {
						e.preventDefault();
						delta = e.touches[0]['page' + axis] - startPosition;
					}
				});

				deck.parent.addEventListener('touchend', function() {
					Math.abs(delta) > 50 && (delta > 0 ? deck.prev() : deck.next());
				});
			};
		},

		plugins = {
			horizontal: makePluginForAxis('X'),
			vertical: makePluginForAxis('Y')
		};

	window[moduleName] = {
		from: from,
		slide: callOnAllInstances('slide'),
		next: callOnAllInstances('next'),
		prev: callOnAllInstances('prev'),
		horizontal: bindPlugin('horizontal'),
		vertical: bindPlugin('vertical'),
		on: on.bind(null, bespokeListeners),
		off: off.bind(null, bespokeListeners),
		plugins: plugins
	};
	window.isrunedonce =1
	}
}('bespoke', this, document));