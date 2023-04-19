(function($, Drupal, window) {
	//////////////////
	//CONFIG: allows you to enable/disable different bits of code.  Could be useful for debugging, and ensuring each feature is not too dependent on the other.  You can also adjust a few settings, like duration.
	//////////////////
	if (!$.fn.on) {
		window.console.error('nih.behaviors.js: invalid jQuery version detected.  jQuery "on" must be supported ( http://api.jquery.com/on/ )');
		return;
	}
	var dataTableStyles = ['responsive-data-table','table-data','table-data-tight','table-data-fancy', 'table-data-plain'];
	var prodHost = 'nih.gov';
	var prodSubdomainNCF = 'commonfund';
	var prodSubdomainDPCPSI = 'dpcpsi';
	var devHost = 'plethoradesign.com';
	var devSubdomainNCF = 'ncf';
	var devSubdomainDPCPSI = 'dpcpsi';
	var hostname = window.location.hostname;
	var isDev = hostname.indexOf(devHost) > -1;
	var isNCF = isDev ? hostname.indexOf(devSubdomainNCF + '.') === 0 : hostname.indexOf(prodSubdomainNCF + '.') === 0;
	var isDPCPSI = isDev ? hostname.indexOf(devSubdomainDPCPSI + '.') === 0 : hostname.indexOf(prodSubdomainDPCPSI + '.') === 0;
	var config = {
		log: {
			enabled: true //if true, this file may spit out console.log messages
		},
		ensureSiteMenuStructure: {
			enabled: true //this is really important for other functions to work, only disable to debug
		},
		siteSearchToggle: {
			enabled: true,
			duration: 300
		},
		siteMenuToggle: {
			enabled: true,
			duration: 300
		},
		siteSubMenuToggle: {
			enabled: true,
			duration: 300
		},
		subNavToggle: {
			enabled: true,
			duration: 300
		},
		autoWrapTabs: {
			enabled: true //if this is false, the markup should always include a div wrapper for the main menu links with class "tab"
		},
		megamenus: {
			enabled: true, //enable this to show/hide the megamenus on hover/focus
			aria: true, // only needed/applied if megamenus are enabled
			hoverDelay: true,
			skipLinks: true
		},
		detectNonLinks: {
			enabled: true //if enabled, any link with href starting with javascript: will get role=button, if it doesn't already have a role set
		},
		activetrail: {
			enabled: true //if enabled, the active-trail class will be added to the main menu tab that contains a link for the current page 
		},
		removeEmptyNodeTitles: {
			enabled: true //if enabled, any html nodes with class node__title that have no text will be removed, to prevent display issues
		},
		autoLinkSideMenuTitle: {
			enabled: true //if enabled, the main side menu title will automatically get linked to the first link in the main menu that has the same text
		},
		mobileSideMenu: {
			enabled: true //if enabled, the main side menu will be used to create a mobile side menu
		},
		ncf: {
			enabled: true
		},
		resetPage: {
			enabled: true
		},
		masonryGrid: {
			enabled: true
		},
		pageSection: {
			enabled: true
		},
		scrollToTop: {
			enabled: true
		},
		/*
		tableStripe: {
			enabled: true
		}, */
		jqueryCollapse: {
			enabled: true
		},
		teaserThumbails: {
			enabled: true
		},
		/*boxes: {
			enabled: true
		},*/
		fixTooltipOnlyLinks: {
			enabled: true
		},
		tablecloth: {
			enabled: true
		},
		mobileBelowContent: {
			enabled: true
		},
		mobileAboveContent: {
			enabled: true
		},
		moveResponsiveClasses: {
			enabled: true
		},
		appendToMobileMenu: {
			enabled: true
		},
		prependToMobileMenu: {
			enabled: true
		},
		/*replaceFloatImageStylesWithClass: {
			enabled: true
		},*/
		contentCollapsible: {
			enabled: true
		},
		inlineReadMore: {
			enabled: true
		},
		slideshowPlaceholders: {
			enabled: true
		},
		slideshowAutoFitText: {
			enabled: true
		},
		imagePlaceholders: {
			enabled: true
		},
		moveResponsiveDataTableClasses: {
			enabled: true
		},
		styleGuide: {
			enabled: true
		},
		tablesawConfig: {
			enabled: true
		},
		slideshowAccessibility: {
			enabled: true
		},
		movePageTitleToHeaderImage: {
			enabled: true
		},
		customizeSlideFields: {
			enabled: true
		},
		viewOrHideAll: {
			enabled: true
		},
		siteMapMenu: {
			enabled: true
		},
		linksPage: {
			enabled: true
		},
		equalHeights: {
			enabled: true
		},
		tableHelpers: {
			enabled: true
		},
		menuClone: {
			enabled: true
		},
		clickableTeaser: {
			enabled: true
		},
		devURLReplacement: {
			enabled: isDev
		}
	};
	
	

	//////////////////////////////////////////////////////
	//		ADD GLOBAL FUNCTIONS
	//////////////////////////////////////////////////////
	if(!window.toggleMe){
		window.toggleMe = function(a){
			if(!a) return true;
			var $e=$('#' + a);
			if(!$e.length) return true;
			$e.slideToggle('fast');
			return false;
		};

	}

	//////////////////////////////////////////////////////
	//		DEBUGGING
	//////////////////////////////////////////////////////
	function log(msg) {
		if (config.log.enabled && window.console && window.console.log) window.console.log(msg);
	}



	//////////////////////////////////////////////////////
	// 		SMALL HELPER jQuery PLUGINS
	//////////////////////////////////////////////////////
	if (!$.fn.uniqueId) { // this is a jquery-ui script, see https://raw.githubusercontent.com/jquery/jquery-ui/master/ui/unique-id.js
		var uuid = 0;
		$.fn.uniqueId = function() {
			return this.each(function() {
				if (!this.id) {
					this.id = "ui-id-" + (++uuid);
				}
			});
		}
	}
	if (!$.fn.safeCloneN) { 
		var safeCloneN = 0;
		$.fn.safeClone = function(){
			var $c = this.clone();
			safeCloneN++;
			$c.find('.contextual-links-wrapper').remove();
			$c.removeClass('contextual-links-region');
			$c.find('[id]').add($c[0]).each(function(){
				if (this.id) {
					this.id = this.id + '-safe-clone-' + safeCloneN;
				}
			});
			$c.find('[for]').add($c[0]).each(function(){
				if ($(this).attr('for')) {
					$(this).attr('for', $(this).attr('for') + '-safe-clone-' + safeCloneN);
				}
			});
			return $c;
		}
	}
	
	$.fn.makeRoleButton = function(){
		return this.each(function(){
			$(this)
				.attr('role','button')
				.keydown(function(e){
				   //log("keydown: " + e.which);
				   switch(e.which){
					   case 32: //space
						   e.preventDefault();
						   return false;
				   }
				})
				.keyup(function(e){
				   //log("keyup: " + e.which);
				   switch(e.which){
					   case 32: //space
						   $(this).trigger('click');
						   e.preventDefault();
						   return false;
				   }
				});
		});
	};
	
	function addCSSFile(fileName) {
	   var link = '<link rel="stylesheet" type="text/css" href="' + fileName + '">'
	   $('head').append(link);
	}
	function addStyle(css){
	   var s = '<style>' + css + '</style>';
	   $('head').append(s);
	}
	function hexToRgb(hex) {
		// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
		var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
		hex = hex.replace(shorthandRegex, function(m, r, g, b) {
			return r + r + g + g + b + b;
		});

		var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
		return result ? [
			parseInt(result[1], 16),
			parseInt(result[2], 16),
			parseInt(result[3], 16)
		] : null;
	}

	// function stubs
	var showTab = function() {};
	var hideTab = function() {};
	
	// Grabs value of width property
	var $responsiveState;
	var responsive_state = function() {
		if (!$responsiveState){
			$responsiveState = $('<div id="responsive-state" class="responsive-state"></div>');
			$('body').append($responsiveState);
		}
		return $responsiveState.css('width');
	};
	var menuIsMobile = function(){
		return responsive_state() == "480px";
	};
	var menuIsDesktop = function(){
		return responsive_state() == "800px";
	};


	//////////////////////////////////////////////////////
	// 		Global listeners
	//////////////////////////////////////////////////////
	$(document).on('content-expanded', function(){
		$(this).find('.flexslider').each(function(){ jQuery(this).data('flexslider').resize() });
	});

	//////////////////////////////////////////////////////
	// 		Drupal behaviors
	//////////////////////////////////////////////////////

	if(config.menuClone.enabled){
		Drupal.behaviors.menuClone = {
			attach: function(context, settings) {
				$('.block-bean-menu-clone').not('.block-bean-menu-clone-processed').each(function(){ 
					var $block = $(this).addClass('block-bean-menu-clone-processed');
					var sel = $.trim($block.find('.menu-selector').text());
					var $clone = $(sel).safeClone().show();
					
					if($clone.length) { 
						var $content = $(this).children('div.content:first').empty();
						$content.append($clone); 
						$content.find('*').each(function(){
							var $el = $(this);
							if($el.is('.megamenu-skip-link')) {
								$el.remove();
							}
							else {
								if(!$el.closest('.menu-clone-keep-attributes').length){ //is this efficient?
									$el
										.removeAttr('class')
										.removeAttr('style')
										.removeAttr('role')
										.removeAttr('aria-expanded')
										.removeAttr('aria-hidden')
										.removeAttr('aria-labelledby')
										.removeAttr('aria-haspopup')
										.removeAttr('aria-owns');
								}
									
								if ($el.is('ul')) {
									$el.addClass('menu');
								};
							}
						});
					} else console.error("menu-clone: cannot find element with selector " + sel);
					
					if(Drupal.behaviors.autoLinkSideMenu && Drupal.behaviors.autoLinkSideMenu.attach) Drupal.behaviors.autoLinkSideMenu.attach($('body'));
				})
	
			}
		};
	}//menuClone

	if (config.ensureSiteMenuStructure.enabled) {
		// this is required...
		Drupal.behaviors.ensureSiteMenuStructure = {
			attach: function(context, settings) {
				$('.site-menu').not('.site-menu-ensure-structure-processed').addClass('site-menu-ensure-structure-processed').each(function() {
					var $sm = $(this).addClass('l-menu-wrapper');
					$('<h2 class="element-invisible">Site Menu</h2>').insertBefore($sm);
					$sm.append('<div class="tab-bkgnd"></div>'); //For pixel rounding issue
					$sm
						.children('ul:first')
						.addClass('l-menu')
						.children('li')
						.addClass('tab-wrapper'); //just in case someone forgets to add this class in the HTML - would cause jumpiness though...
				});
			}
		}
	}
	if (config.autoWrapTabs.enabled) {
		//with a div.tab wrapper, several other things will break, such as hover/focus state and the mobile menu submenu toggle
		Drupal.behaviors.autoWrapTabs = {
			attach: function(context, settings) {
				$('.tab-wrapper').not('.autowraptabs-processed').addClass('autowraptabs-processed').each(function() {
					$(this).find('> a').wrap('<div class="tab"></div>');
				});
			}
		}
	}
	if (config.megamenus.enabled) {
		$.fn.nihSiteMenuTabShow = function() {
			return this.each(function() {
				showTab.apply(this);
			});
		}
		$.fn.nihSiteMenuTabHide = function() {
			return this.each(function() {
				hideTab.apply(this);
			});
		}
		if (config.megamenus.aria) {
			$.fn.nihSiteMenuTabAddAria = function() {
				return this.each(function() {
					var $tabWrapper = $(this);
					var $tab = $tabWrapper.children('.tab, a').first();
					var $megamenu = $tabWrapper.find('.megamenu');
					var hasPopup = $megamenu.length > 0;
					if (hasPopup) {
						var $tabLink = $tab.is("a") ? $tab : $tab.find('a').first();
						$tabLink.uniqueId();
						$megamenu.uniqueId();
						var initialShow = $(this).hasClass('show');
						$megamenu.attr('role', 'group')
							.attr('aria-labelledby', $tabLink.attr('id'))
							.attr('aria-hidden', !initialShow)
							.attr('aria-expanded', initialShow);
						if($megamenu.attr('aria-hidden') == 'false') $megamenu.removeAttr('aria-hidden');
						$tabLink.attr('aria-haspopup', true);
						$tabLink.attr('aria-expanded', initialShow);
						$tabLink.attr('aria-owns', $megamenu.attr('id')).attr('aria-controls', $megamenu.attr('id'));
						$tabWrapper.on('nih-site-menu-tab-show', function() {
							$tabLink.attr('aria-expanded', true);
							$megamenu.removeAttr('aria-hidden').attr('aria-expanded', true);
						});
						$tabWrapper.on('nih-site-menu-tab-hide', function() {
							$tabLink.attr('aria-expanded', false);
							$megamenu.attr('aria-hidden', true).attr('aria-expanded', false);
						});
					}
				});
			};
		}
		if (config.megamenus.skipLinks) {
			$.fn.nihSiteMenuTabAddSkipLinks = function() {
				return this.each(function() {
					var $tabWrapper = $(this);
					//var $tab = $tabWrapper.children('.tab, a').first();
					var $megamenu = $tabWrapper.find('.megamenu');
					var hasPopup = $megamenu.length > 0;
					if (hasPopup) {
						$megamenu.prepend('<button type="button" role="button" class="megamenu-skip-link megamenu-skip-link-last" aria-label="Skip to the end of this menu">Skip Menu <i class="fa fa-chevron-down" aria-hidden="true"></i></button>');
						$megamenu.append('<button type="button" role="button" class="megamenu-skip-link megamenu-skip-link-first" aria-label="Skip to the beginning of this menu">Skip Menu <i class="fa fa-chevron-up" aria-hidden="true"></i></button>');
					}
				});
			};
			$(document).on('click', '.megamenu-skip-link', function(e){
				var $tabWrapper = $(this).closest('.tab-wrapper');
				/*var $nextTabwrapper = $tabWrapper.next('li.tab-wrapper');
				if (false && $nextTabwrapper.length){
					var $nextTab = $nextTabwrapper.find('.tab > a').first();
					$nextTab.focus();
				}else {*/
					var $l = $tabWrapper.find('.megamenu-skip-link').not(this);
					//var $l = $tabWrapper.find('a,button')[$(this).hasClass('megamenu-skip-link-first') ? 'first' : 'last']();
					$l.focus();
				//}
				e.preventDefault();
				return false;
			});
		}
		Drupal.behaviors.megamenus = {
			attach: function(context, settings) {
				function setSiteMenuWidths() {
					// automatically determines the best width for all of the menu tabs based on text width

					//save which tabs are currently showing...
					var $activeTabs = $('.tab-wrapper.show');
					// hide all the open tabs, since the width of the submenu would otherwise interfere with the calculations
					$('.tab-wrapper').nihSiteMenuTabHide();
					$('.site-menu').each(function() {
						var $sm = $(this);
						var $ul = $sm.children('ul:first');
						if ($ul.children().first().css('float') == 'left') { // detects that this is not mobile display
							//set each tab width to auto temporarily so we can figure out their 'natural' widths...
							$ul.children().css({
								'width': 'auto',
								'white-space': 'nowrap'
							});
							/*
						  $ul.children().css({
							  'width': 'auto',
							  'white-space':'nowrap'
						  });
						  $ul.css('float','left');
						  */
							$ul.addClass('site-menu-table-layout');

							var ulWidth = getRealWidth($ul[0]);
							var newWidths = [];
							var totalPercent = 0;
							$ul.children().each(function() {
								//round down to the nearest integer to avoid rounding errors that lead to > 100% total.
								//if we end up with < 100%, we'll distribute the difference later via fractionalPercent
								var nw = Math.floor((getRealWidth(this) / ulWidth) * 100);
								newWidths.push(nw);
								totalPercent += nw;
							});
							var fractionalPercent = 0;
							if (totalPercent < 100) {
								//log("initial totalPercent was " + totalPercent + "%");
								var diff = (100 - totalPercent);
								fractionalPercent = diff / $ul.children().length;
								//log("adding " + fractionalPercent + "% to each width");
							}
							//totalPercent = 0;
							$ul.children().each(function(i) {
								var fnw = newWidths[i] + fractionalPercent;
								$(this).css('width', fnw + '%').css('white-space', '');
								//totalPercent += fnw;
							});
							//log("FINAL totalPercent: " + totalPercent);
							//$ul.css('float','');
							$ul.removeClass('site-menu-table-layout');

						} else {
							$ul.children().css('width', '').css('white-space', '');
						}

						// re-show all previously opened submenus
						$activeTabs.nihSiteMenuTabShow();
					});
				}

				var menuWidthsTimeout = null;

				function invalidateSiteMenuWidths() {
					clearTimeout(menuWidthsTimeout);
					menuWidthsTimeout = setTimeout(setSiteMenuWidths, 100);
				}

				// PREPARE THE SITE MENU, WHICH REQUIRES SOME EXTRA CLASSES NOT PUT INTO THE HTML IN THE BLOCKS
				$('.site-menu').not('.megamenus-processed').addClass('megamenus-processed').each(function() {
					var $sm = $(this);
					var $ul = $sm.find('> ul').addClass('show-megamenus');
					var $tabs = $sm.find('> ul > li');
					//var n = $tabs.length;
					//$sm.addClass('site-menu-' + n);
					setSiteMenuWidths();
					invalidateSiteMenuWidths();
					if(config.megamenus.aria) $tabs.nihSiteMenuTabAddAria();
					if(config.megamenus.skipLinks) $tabs.nihSiteMenuTabAddSkipLinks();
					$(window).on('resize orientationchange', invalidateSiteMenuWidths);


				});


			}
		};
			
		// HOVER DELAY FOR MEGA MENUS /////////////////////
		if (config.megamenus.hoverDelay) {
			var getRealWidth = function(el) { //gets the real, floating point width of an element (jQuery only returns rounded value
				if (!el) return 0;
				var rect = el.getBoundingClientRect();
				if (!rect) return $(el).width();

				var width;
				if (rect.width) {
					// `width` is available for IE9+
					width = rect.width;
				} else {
					// Calculate width for IE8 and below
					width = rect.right - rect.left;
				}
				return width || $(el).width();
			}

			//override hide and show tab
			showTab = function() {
				//log('showTab!');
				if (!$(this).closest('.l-menu').length) return; // the JS hasn't yet run - we don't want to show the tab unless this is done, because it would mean the styles aren't ready to actually show the megamenu properly
				if ($(this).hasClass('collapsed')) {
					$(this).addClass('show').removeClass('has-megamenu').removeClass('collapsed');
					var $mm = $(this).find('.megamenu');
					if ($mm.length) {
						var tw = getRealWidth(this);
						var mmw = getRealWidth($mm[0]);
						if (Math.abs(tw - mmw) <= 2) {
							$mm.css('width', Math.round(tw) + 'px');
						}

						var $menu = $(this).closest('ul.l-menu');
						if ($menu.length) {
							//detect if the right edge is too far to the right
							var menuOffset = $menu.offset();
							var mmOffset = $mm.offset();
							var menuLeft = menuOffset.left;
							var menuRight = menuLeft + $menu.outerWidth();
							var mmLeft = mmOffset.left;
							var mmRight = mmLeft + $mm.outerWidth();

							if (mmRight > menuRight) {
								$mm.css('left', (menuRight - mmRight) + 'px');
							}
							/*
							//detect if the left edge is too far to the left
							menuOffset = $menu.offset();
							mmOffset = $mm.offset();
							menuLeft = menuOffset.left;
							menuRight = menuLeft + $menu.outerWidth();
							mmLeft = mmOffset.left;
							mmRight = mmLeft + $mm.outerWidth();
						
							if (mmLeft < menuLeft){
								$mm.css('left', (menuLeft - mmLeft) + 'px');
							}
							*/



						}
					}
					$(this).trigger('nih-site-menu-tab-show').trigger('nih-site-menu-tab-toggle');
				}
			};
			hideTab = function() {
				//log('hideTab!');
				if ($(this).hasClass('show')) {
					$(this).removeClass('show').addClass('has-megamenu').addClass('collapsed').removeClass('active');
					$(this).find('.megamenu')
						.css({
							'width': '',
							'display': 'none',
							'right': '',
							'left': ''
						});
					$(this).trigger('nih-site-menu-tab-hide').trigger('nih-site-menu-tab-toggle');
				}
			};
			Drupal.behaviors.showHideMegaMenus = {
				attach: function(context, settings) {
					var $tabs = $('li.tab-wrapper')
						.not('.show-hide-megamenus-processed')
						.addClass('show-hide-megamenus-processed collapsed').each(function() {
							var $thisTab = $(this);
							$a = $thisTab.find('> .tab > a, > a');
							$a.on('focus mouseover', function() {
								$(this).closest('li').addClass('active');
							}).on('blur mouseout', function() {
								$(this).closest('li').removeClass('active');
							});
						});
					var $tabsWithMenus = $tabs.filter(function() {
							return $(this).find('.megamenu').length > 0;
						})
						.addClass('has-megamenu')
						.hoverIntent({
							interval: 150,
							timeout: 150,
							sensitivity: 5,
							over: function(){
								//log('hoverIntent over, menuIsDesktop=' + menuIsDesktop());
								if(menuIsDesktop()) showTab.apply(this);
							},
							out: function(){
								//log('hoverIntent out, menuIsDesktop=' + menuIsDesktop());
								if(menuIsDesktop()) hideTab.apply(this);
							}
						});
						
					$tabsWithMenus.each(function() {
						var $thisTab = $(this);
						var docFocusinHandler = function(e) {
							if (menuIsDesktop()){
								var $li = $(e.target).closest('li.tab-wrapper')
								if (!$li.length || $li[0] !== $thisTab[0]) {
									$thisTab.nihSiteMenuTabHide();
									$(document).off('focusin click', docFocusinHandler);
								}
							}
						};
						$thisTab.focusin(function() {
							if (menuIsDesktop()){
								//$(this).closest('ul').children().nihSiteMenuTabHide();
								$(this).closest('li').nihSiteMenuTabShow();
								$(document).on('focusin click', docFocusinHandler);
							}
						});
					});


					var $tabsWithoutMenus = $tabs.not('.has-megamenu').addClass('no-megamenu');


				}
			};
		}//showHideMegaMenus
	}
	if (config.siteSearchToggle.enabled) {
		Drupal.behaviors.siteSearchToggle = {
			attach: function(context, settings) {
				// Toggle site search
				$('.l-banner').append('<button type="button" class="site-search-toggle icon-search"><span>Search</span></button>');
				$(".site-search-toggle").click(function() {
					$(".site-search,#search-block-form").first().slideToggle({
						duration: config.siteSearchToggle.duration
					});
					$(this).toggleClass("active");
				});
			}
		}
	}
	if (config.siteMenuToggle.enabled) {
		Drupal.behaviors.siteMenuToggle = {
			attach: function(context, settings) {
				// Toggle site menu
				$('.l-banner').append('<button type="button" class="menu-button menu-toggle"><span>Menu</span></button>');
				$(".menu-toggle").on("click", function() {
					if ($(this).hasClass("active")) {
						// Hide site menu if visible
						$(this).removeClass("active");
						$(".l-menu-wrapper").slideUp({
							duration: config.siteMenuToggle.duration
						});
					} else {
						// Hide all submenus and display site menu
						$(".megamenu").hide();
						$(".sub-menu-toggle").removeClass("sub-active");
						$(this).addClass("active");
						var $notMobileSubnav = $('.l-sub-navigation-wrapper:not(.mobile-subnav)');
						if($notMobileSubnav.length) {
							$notMobileSubnav.replaceWith('<span id="desktop_subnav_placholder"></span>');
							$('.l-navigation-wrapper').append($notMobileSubnav);
							$notMobileSubnav.addClass('mobile-subnav');
						}
						$(".l-menu-wrapper").slideDown({
							duration: config.siteMenuToggle.duration
						});
					}
				});
			}
		}
	}
	if (config.siteSubMenuToggle.enabled) {
		Drupal.behaviors.siteSubMenuToggle = {
			attach: function(context, settings) {
				if ($('body').hasClass('site-sub-menu-toggle-processed')) return;
				$('body').addClass('site-sub-menu-toggle-processed');
				// Toggle site sub-menu
				$('.tab-wrapper .tab')
					.filter(function() {
						return $(this).closest('.tab-wrapper').find('.megamenu').length > 0;
					})
					.each(function(){
						var $megamenu = $(this).closest('.tab-wrapper').children('.megamenu:first').uniqueId();
						//var $tabLink = $(this).closest('.tab-wrapper').children('.tab > a');
						$(this).append('<button type="button" class="sub-menu-toggle" aria-owns="' + $megamenu.attr('id') + '" aria-controls="' + $megamenu.attr('id') + '" role="button"><span>Show/hide sub-menu</span></button>')
					});
				// $('.megamenu-col a').append('<span class="sub-menu-pointer"></span>');
				$(document).on('click', ".sub-menu-toggle", function() {
					//log('sub-menu-toggle clicked!!!');
					var $toggleButton = $(this);
					if ($toggleButton.hasClass("sub-active")) {
						// Hide current submenu if visible
						//log("Hide current submenu if visible");
						$toggleButton.removeClass("sub-active");
						$toggleButton.parent().next(".megamenu").slideUp({
							duration: config.siteSubMenuToggle.duration
						});
					} else {
						// Hide all submenus and display current submenu
						//log("Hide all submenus and display current submenu");
						var $newActiveMegaMenu = $toggleButton.parent().next(".megamenu");
						$(".megamenu").not($newActiveMegaMenu[0]).slideUp({
							duration: config.siteSubMenuToggle.duration
						});
						$(".sub-menu-toggle").removeClass("sub-active");
						$toggleButton.addClass("sub-active");
						$newActiveMegaMenu.slideDown({
							duration: config.siteSubMenuToggle.duration,
							complete: function(){
								$newActiveMegaMenu.find(':input, a').not('.megamenu-skip-link').filter(':visible').first().focus();
							}
						});
					}
				});
			}
		}
	}

	if (config.subNavToggle.enabled) {
		Drupal.behaviors.subNavToggle = {
			attach: function(context, settings) {
				if ($('body').hasClass('sub-nav-toggle-processed')) return;
				$('body').addClass('sub-nav-toggle-processed');
				// Toggle subnav section links
				if ($("nav").hasClass("subnav")) {
					$('.sectionheader').append('<button type="button" class="subnav-toggle"><span>Show/hide section links</span></button>');
				}
				$(".subnav-toggle").on("click", function() {
					if ($(this).hasClass("subnav-active")) {
						// Hide current submenu if visible
						$(this).removeClass("subnav-active");
						$(".subnav").slideUp({
							duration: config.subNavToggle.duration
						});
					} else {
						// Hide all submenus and display current submenu
						$(this).addClass("subnav-active");
						$(".subnav").slideDown({
							duration: config.subNavToggle.duration
						});
					}
				});

			}
		}
	}


	if (config.detectNonLinks.enabled) {
		Drupal.behaviors.detectNonLinks = {
			attach: function(context, settings) {
				//detects any javascript: main menu links, changes their role to button 
				$('a[href^="javascript:"]', context).not('[role]').attr('role', 'button');
			}
		}
	}
	if (config.resetPage.enabled) {
		// RESET SITE SEARCH AND MENU VISIBILITY ON BROWSER WINDOW RESIZE /////////////////////


		Drupal.behaviors.resetPage = {
			attach: function(context, settings) {
				if ($('body').hasClass('reset-page-processed')) return;
				$('body').addClass('reset-page-processed');

				var reset_page = function() {
					// Put value of .responsive-state div width in a variable

					// For desktop view
					if (menuIsDesktop()) {
						$(".site-search-toggle").removeClass("active"); // Hide site search toggle
						$(".site-search,#search-block-form").first().show(); // Show search form
						$(".menu-toggle").removeClass("active"); // Hide site menu toggle
						$(".sub-menu-toggle").removeClass("sub-active"); // Hide sub-menu toggle
						$(".l-menu-wrapper").show(); // Show site menu
						$(".megamenu").hide(); // Hide megamenu
						$(".tab-wrapper.show .megamenu").show(); // Show megamenu on tab hover
						$(".subnav-toggle").removeClass("subnav-active"); // Hide subnav toggle
						$(".subnav").show(); // Show subnav
						var $mobileSubNav = $('.l-sub-navigation-wrapper.mobile-subnav');
						if($mobileSubNav.length) $('#desktop_subnav_placholder').replaceWith($mobileSubNav.removeClass('mobile-subnav'));
					};
					// For mobile view
					if (menuIsMobile()) {
						if (!$(".site-search-toggle").hasClass("active")) {
							// If site search toggle is not active
							$(".site-search-toggle").removeClass("active"); // Do not highlight site search toggle
							$(".site-search,#search-block-form").first().hide(); // Hide site search
						};
						if (!$(".menu-toggle").hasClass("active")) {
							// If site menu toggle is not active
							$(".l-menu-wrapper").hide(); // Hide site menu
						};
						$(".tab.home .sub-menu-toggle").hide(); // hide sub-menu toggle on home tab
						if (!$(".subnav-toggle").hasClass("subnav-active")) {
							// If subnav toggle is not active
							$(".subnav-toggle").removeClass("subnav-active"); // Do not highlight subnav toggle
							$(".subnav").hide(); // Hide subnav
						};
					};
				};

				$(window).on('resize orientationchange', reset_page);
				reset_page();

			}
		};
	}
	if (config.activetrail.enabled) {
		Drupal.behaviors.addActiveTrail = {
			attach: function(context, settings) {
				var $tabs = $('li.tab-wrapper');
				//add the active trail class...
				var curPath = $.trim(window.location.pathname.toLowerCase());
				var curPathAlt = curPath.lastIndexOf('/index') === curPath.length - '/index'.length ? curPath.substr(0, curPath.lastIndexOf('/index')) : curPath;
				if (!curPath || curPath == '/') {
					$tabs.find('.tab a, > a').filter('[href="/"]').addClass('active-trail').closest('.tab-wrapper').addClass('active-trail').attr('data-active-trail-method', 'home');
				} else {

					var foundExactMatch = false;

					$tabs.find('a').each(function() {
						var href = $.trim($(this).attr('href').toLowerCase());

						if (href && href != '/' && (curPath == href || curPathAlt == href)) {
							$(this).addClass('active-trail').closest('.tab-wrapper').addClass('active-trail').attr('data-active-trail-method', 'exact');
							foundExactMatch = true;
							return false; //return false means we only find the first one... which is better for performance but will miss if there are duplicate links within the menu
						}
					});

					if (!foundExactMatch) {
						var foundPartialMatch = false;
						$tabs.find('a').each(function() {
							var href = $.trim($(this).attr('href').toLowerCase()).replace('/index','');
							//log(href);
							if (href && href != '/' && (curPath.indexOf(href) === 0)) {
								$(this).addClass('active-trail-partial').closest('.tab-wrapper').addClass('active-trail').attr('data-active-trail-method', 'partial');
								foundPartialMatch = true;
								return false;
							}
						});

						if (!foundPartialMatch) {
							// last attempt: try to find it via the breadrumb...
							var activeHREF = $('.breadcrumb a').not('[href="/"]').first().attr('href');
							$tabs.find('[href="' + activeHREF + '"]').closest('.tab-wrapper').addClass('active-trail').attr('data-active-trail-method', 'breadcrumb');
						}
					}
				}


			}
		};
	}
	if (config.removeEmptyNodeTitles.enabled) {
		Drupal.behaviors.removeEmptyNodeTitles = {
			attach: function(context, settings) {
				$('.node__title').not('.remove-empty-node-title-processed').addClass('remove-empty-node-title-processed').each(function() {
					if (!$.trim($(this).text())) {
						$(this).remove();
					}
				});
			}
		}
	}
	
	if (config.autoLinkSideMenuTitle.enabled) {
		Drupal.behaviors.autoLinkSideMenuTitle = {
			attach: function(context, settings) {
				$('.side-menu').not('.auto-link-side-menu-processed').addClass('auto-link-side-menu-processed').each(function() {
					var $block = $(this);
					var $menu = $(this).find('ul.menu:first');
					var $title = $block.find('.block__title:first');
					var title = $title.html();
					if (!title) return;
					
					var $bannerLink = $('.block-bean-banner a.group-banner-link[href]');
					if ($bannerLink.length) $title.wrap($('<a/>').addClass('side-menu-title-link side-menu-title-link-from-banner').attr('href', $bannerLink.attr('href') ));
					else {
						var titleTextLC = $.trim($title.text().toLowerCase());
						$('.site-menu a[href]').each(function(){
							if($.trim($(this).text().toLowerCase()) === titleTextLC){
								$title.wrap($('<a/>').addClass('side-menu-title-link side-menu-title-link-from-menu').attr('href', $(this).attr('href') ));
							}
						});
					}

				});
			}
		}
	}
	if (config.mobileSideMenu.enabled) {
		Drupal.behaviors.mobileSideMenu = {
			attach: function(context, settings) {
				$('.side-menu-program').not('.mobile-side-menu-processed').addClass('mobile-side-menu-processed').each(function() {
					var $block = $(this);
					var $menu = $(this).find('ul.menu:first');
					var $title = $block.find('.block__title:first');
					var title = $title.html();
					if (!title) {
						return;
					}
					
					var $mobileMenuWrapper = $('<div class="mobile-side-menu-wrapper collapsed"></div>');
					var $toggleButton = $('<button type="button" class="mobile-side-menu-toggle-button collapsed"></button>').html(title);
					$mobileMenuWrapper.append($toggleButton);
					var $mobileMenu = $menu.clone();
					$mobileMenu.attr('style', 'overflow: hidden; display: none;');
					$mobileMenuWrapper.append($mobileMenu);
					$('.l-navigation-wrapper').append($mobileMenuWrapper);
					var $banner = $('.block-bean-banner');
					if ($banner.length){
						//if the banner title is the same as the mobile side menu title, 
						if ($.trim($banner.find('h1').text()).toLowerCase() == $.trim($title.text()).toLowerCase()){
							//then hide the banner on mobile
							$banner.addClass('bp-non-mobile');
						}
					}
					$toggleButton.click(function() {
						if ($mobileMenuWrapper.hasClass("expanded")) {
							$mobileMenuWrapper.add($toggleButton).removeClass('expanded').addClass('collapsed');

							$mobileMenu.stop(true, true).slideUp();
						} else {
							$mobileMenuWrapper.add($toggleButton).addClass('expanded').removeClass('collapsed');
							$mobileMenu.stop(true, true).slideDown();
						}
					});

				});
			}
		}
	}
	
	
	if(config.clickableTeaser.enabled) {
	  // USE LINK IN TEASER TO MAKE ENTIRE TEASER CLICKABLE /////////////////////
	  Drupal.behaviors.clickableTeaser = {
		attach: function (context, settings) {
				$('.teaser-clickable').click(function(e) {	
					if($(e.target).is('a,:input')) {
						return;
					}			
					// First look for link in teaser-title
					if ($(this).find('.teaser-title a').length) {
						var titleLink = $(this).find('.teaser-title a').attr('href');
						// If link is external then open in a new window
						if (($(this).find('.teaser-title a').attr('target') == '_blank') || (event.ctrlKey) || (event.altKey)) { 
							window.open(titleLink); 
						} else {
							window.location = titleLink; 
					  }			  
					// Otherwise look for link in teaser-description
				  } else if ($(this).find('.teaser-description a').length) {
						var descriptionLink = $(this).find('.teaser-description a').attr('href');
						// If link is external then open in a new window
						if (($(this).find('.teaser-description a').attr('target') == '_blank') || (event.ctrlKey) || (event.altKey)) { 
							window.open(descriptionLink); 
						} else {
							window.location = descriptionLink; 
					  }	  
				  }
				  return false;
				});
		
		}
	  };
	} 

	if (config.masonryGrid.enabled) {
		// MASONRY CASCADING GRID LAYOUT /////////////////////
		// From http://masonry.desandro.com
		Drupal.behaviors.masonryGrid = {
			attach: function(context, settings) {
				// Trigger Masonry after all the media on the page has loaded – images, fonts, external scripts and stylesheets, etc.
				$(window).load(function() {

					// Flip card functionality inserted here (not a part of Masonry)

					// Show flip buttons
					$('.flipcard-button-wrapper').show();


					// Determine whether front or back is higher and set height to that
					$('.masonry-container .box').not('.masonry-box').each(function(i, n) {
						// First find cards with more than one side
						$(this).addClass('masonry-box');
						// Change z value for hover box 
						$(this).hover(function() {
							$(this).css('z-index', '20');
						}, function() {
							$(this).css('z-index', '10');
						});
						var flipcardSides = $(n).find('.flipcard-content').length;
						if (flipcardSides > 1) {
							// Add .flipcard-enabled class and put height of each side into a variable
							$(n).addClass('flipcard-enabled');
							var frontHeight = $(n).find('.flipcard-front').height();
							var backHeight = $(n).find('.flipcard-back').height();
							// Make .box container and both sides all the same height
							if (frontHeight > backHeight) {
								$(n).css('height', frontHeight);
								$(n).find('.flipcard-back').css('height', frontHeight);
							} else {
								$(n).css('height', backHeight);
								$(n).find('.flipcard-front').css('height', backHeight);
							}
						}
					});
					//move some contextual links wrappers so they are positioned within the section...
					$('.block-bean-right-sidebar-box > .contextual-links-wrapper').each(function(){$(this).parent().find('section.box:first').append(this); });

					// Click to flip card to back
					$('.flipcard-front a.flipcard-button').click(function() {
						$(this).parent().prev().find('object').hide();
						$('.flipcard').removeClass('flipcard-flipped');
						$(this).parents('.flipcard').addClass('flipcard-flipped');
					});
					// Click to flip card to front
					$('.flipcard-back a.flipcard-button').click(function() {
						$(this).parents('.flipcard').removeClass('flipcard-flipped');
						$(this).parents('.flipcard-back').prev().find('object').show();
					});

					// Initialize Masonry with jQuery
					$('.masonry-container').each(function() {
						if ($(this).find('.masonry-box').length) {
							
							$(this).masonry({
								// options
								columnWidth: '.masonry-box',
								gutter: 20,
								itemSelector: '.masonry-box'
							});
						}
					});

				});

			}
		};
	} //end masonrygrid
	if (config.pageSection.enabled) {
		Drupal.behaviors.pageSection = {
			attach: function(context, settings) {
				$('.page-section').not('.page-section-processed').each(function() {
					var $e = $(this).addClass('page-section-processed');
					if ($.trim($e.children('.group-right').html())) {
						$e.addClass('has-group-right');
					}
				});
			}
		};
	} //end pageSection
	if (config.scrollToTop.enabled) {
		Drupal.behaviors.scrollTop = {
			attach: function(context, settings) {
				if ($('body').hasClass('scrolltop-behavior-processed')) return;
				$('body').addClass('scrolltop-behavior-processed')
				var offset = 700;
				var duration = 1000;
				$('.back-to-top a').makeRoleButton();
				$(window).scroll(function() {
					if ($(this).scrollTop() > offset) {
						$('.back-to-top').fadeIn(duration);
					} else {
						$('.back-to-top').fadeOut(duration);
					}
				});

				$('.back-to-top').click(function(event) {
					event.preventDefault();
					$('html, body').animate({
						scrollTop: 0
					}, duration
					, function(){
						//$('.l-region--ribbon .l-ribbon a:first').focus();
						$('#query').focus();
					});
					return false;
				});

			}
		};
	} //scrollToTop
/*
	if (config.tableStripe.enabled) {
		// TABLE STRIPING /////////////////////
		Drupal.behaviors.tableStripe = {
			attach: function(context, settings) {

				$(document).ready(function() {
					$("table tr:even").addClass('even');
					$("table tr:odd").addClass('odd');
				});

			}
		};
	}
*/

	if (config.jqueryCollapse.enabled) {
		// JQUERY COLLAPSE PLUGIN /////////////////////
		// From https://github.com/danielstocks/jQuery-Collapse/tree/523c08f9747b42251e8a1fd84154ed0a2e1b979a
		Drupal.behaviors.jqueryCollapse = {
			attach: function(context, settings) {

				$(".collapsible-with-controls").prepend('<ul class="collapsible-control-all"><li><button class="collapsible-open-all">Open all</button></li><li><button class="collapsible-close-all">Close all</button></li></ul>');
				$(".collapsible").collapse({
					open: function() {
						this.slideDown(500);
					},
					close: function() {
						this.slideUp(500);
					},
					query: '.collapsible-trigger',
					accordion: false,
					// persist: true
				});
				var el = $(".collapsible");
				$(".collapsible-open-all").click(function() {
					$(".collapsible").trigger("open")
				});
				$(".collapsible-close-all").click(function() {
					$(".collapsible").trigger("close")
				});

			}
		};
	} //jqueryCollapse

	if (config.teaserThumbails.enabled) {
		Drupal.behaviors.teaserThumbails = {
			attach: function(context, settings) {
				$('.teaser').filter(function() {
					return $(this).find('.teaser-thumbnail').length === 0
				}).addClass('teaser-no-thumbnail');
			}
		}
	} //teaserThumbails
	/*
	if (config.boxes.enabled) {
		Drupal.behaviors.boxes = {
			attach: function(context, settings) {
				$('.bean-boxes', context).not('.boxes-processed').each(function() {
					var $box = $(this);
					$box.addClass('boxes-processed');
					$box.find('.field--name-title a').each(function() {
						var $link = $(this);
						var $entity = $link.closest('.entity');
						$link.parent().html($link.html());
						$link.empty();
						$entity.wrap($link);
					});
				});

			}

		};
	} //boxes
*/

	if (config.ncf.enabled) {
		Drupal.behaviors.ncf = { //parts of this script used to be global.js in the old NCF theme
			attach: function(context, settings) {
				if (!$('body').hasClass('section-ncf')) return;
				if ($('body').hasClass('ncf-processed')) return;
				$('body').addClass('ncf-processed');
				//$('table tr:even').addClass('even');
				//$('table tr:odd').addClass('odd');

				//When page loads...
				var $fundingTabContent = $('.fundingopp_tab_content');
				if ($fundingTabContent.length) {
					$fundingTabContent.hide(); //Hide all content
					$(".tabs_result ul li:first").addClass("active").show(); //Activate first tab
					$fundingTabContent.first().show(); //Show first tab content
				}

				//On Click Event
				var $tabResultListItems = $(".tabs_result ul li");
				$tabResultListItems.click(function() {

					$tabResultListItems.removeClass("active"); //Remove any "active" class
					$(this).addClass("active"); //Add "active" class to selected tab
					$fundingTabContent.hide(); //Hide all tab content

					var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
					var newstring = activeTab.substring(1);
					$("." + newstring).fadeIn();
					//return false;
				});


				// for post back and hash changes
				if ($(".tabs_result").length) {

					$(window).hashchange(function() {
						var thehash = window.location.hash;
						if (thehash.length != 0) {
							$(".tabs_result ul li").removeClass("active"); //Remove any "active" class
							$(".tabs_result ul li a[href=" + thehash + "]").parent('li').addClass("active");
							$(".fundingopp_tab_content").hide(); //Hide all tab content
							var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
							var newstring = thehash.substring(1);
							$("." + newstring).fadeIn();
						}
					});
					$(window).hashchange();
				}



				//blue bg content in body area starts here
				if ($('.program_content_bg').length > 0) {
					$('.program_content_bg').wrap('<div class="program" />');
					$('.program').prepend('<div class="top_bg" />');
					$('.program').append('<div class="bottom_bg" />');
				}
				//ends here


				//blue bg content in body area starts here
				if ($('.program_green_border_bg').length > 0) {
					$('.program_green_border_bg').wrap('<div class="program_green" />');
					$('.program_green').prepend('<div class="top_green_bg" />');
					$('.program_green').append('<div class="bottom_green_bg" />');
				}
				//ends here



				//orange bg content in body area starts here
				if ($('.program_orange_bg').length > 0) {
					$('.program_orange_bg').wrap('<div class="program_orange" />');
					$('.program_orange').prepend('<div class="top_orange_bg" />');
				}
				//ends here



				//blue bg content in body area starts here
				if ($('.program_content_bg_right').length > 0) {
					$('.program_content_bg_right').wrap('<div class="program_right" />');
					$('.program_right').prepend('<div class="top_bg_right" />');
					$('.program_right').append('<div class="bottom_bg_right" />');
				}
				//ends here


				//blue bg content in body area starts here
				if ($('.program_big_bg').length > 0) {
					$('.program_big_bg').wrap('<div class="program_big" />');
					$('.program_big').prepend('<div class="program_big_top" />');
					$('.program_big').append('<div class="program_big_bottom" />');
				}
				//ends here

				$('.main_program_links > ul').hide();
				$('.main_program_links ul:first').show();
				$('a.program:first').addClass('programactive');
				$('a.program').click(function() {
					if (!$(this).parent().next('ul').is(':visible')) {
						$('.main_program_links > ul').slideUp('fast');
						$(this).parent().next('ul').slideDown('fast');

						$('a.program').removeClass('programactive');
						$(this).addClass('programactive');
						return false;
					}
				});


				//menu highlight meetings

				//menu code
				var pgurl = window.location.pathname;
				if ($('.light_grey').length > 0) {
					$('.light_grey ul li a[href$="' + pgurl + '"]').addClass("active");
				}
				if ($('.main_nav').length > 0) {
					$('.main_nav ul li a[href$="' + pgurl + '"]').addClass("active");
				}

				//form Yes, No Slide function
				$('.res_fund').slideUp();
				$('.prim_fund').slideUp();

				$('input[name="funded_nih"]').click(function() {
					if ($(this).val() == "yes") {
						$('.res_fund').slideDown("fast");
						$('.prim_fund').slideUp("fast");
					} else {
						$('.res_fund').slideUp("fast");
						$('.prim_fund').slideDown("fast");
					}
				});


				//Login Page navigation condition
				if ($('.human_panel').hasClass('int')) {
					$('#navigation_sub').css('margin-top', '-46px');
				}

				if ($('div').hasClass('light_grey')) {
					$('.program_desc').css('width', '750px');
				}


				$('.block-workbench').removeClass('contextual-links-region');

				$("ul.left_menu li").click(function() {
					var id = $(this).index();
					$("ul.left_menu li a").removeClass('active');
					$(this).children("a").addClass('active');
					t = setTimeout("fnscroll(" + id + ")", 800);
					return false;
				});


				var n = $(".sub_nav > ul > li").length;
				var mwdt = $(".sub_nav > ul").width();
				var liwdt = ((mwdt / n) - 30);

				$(".sub_nav > ul > li").children("a").css("min-width", liwdt);
			}
		};
	} //end ncf behaviors
	
	
	if (config.fixTooltipOnlyLinks.enabled) {
	
		Drupal.behaviors.fixTooltipOnlyLinks = {
			attach: function(context, settings) {
				if(!$('body').hasClass('section-ncf')) return;
				$('a.tooltip,a.tooltip1', context).not('.tooltip-only-link-processed').each(function(){
					var $link = $(this);
					$link.addClass('tooltip-only-link-processed');
					if($link.attr('href') == '#'){
							$link.attr('role','button').click(function(e){
							e.preventDefault();
							return false;
						});
					}
					if (!$.trim($link.text())){
						//sometimes no text is provided in the content for these -- let's add some for screen readers if we can use the title...
						var txt = $link.attr('title') || 'Go to ' + $link.attr('href');
						$link.removeAttr('title'); //for consistency, we will use the custom tooltip instead of browser one, since custom tooltip at least is tab-accessible for keyboard users
						var $screenReaderText = $('<span/>').text(txt);
						$link.append($screenReaderText);
					}
					var $tt = $link.children('span').first().uniqueId();
					$link.attr('aria-describedby', $tt.attr('id'));
				});
			}
		};
	}//fixTooltipOnlyLinks
	if (config.tablecloth.enabled) {
		Drupal.behaviors.tablecloth = {
			attach: function(context, settings) {
				$('table.tablecloth', context).not('.tablecloth-processed').each(function(){
					var $table = $(this);
					$table.addClass('tablecloth-processed');
					//$table.find('tr:even').addClass('even');
					//$table.find('tr:odd').addClass('odd');
					$table.find('td,th').on('mouseenter', function(){
						$table.find('td.over,th.over,tr.over').removeClass('over');
						var $tr = $(this).parent();
						$tr.addClass('over').children().addClass('over over-row');
						$table.find('th,td').filter(':nth-child(' + ($(this).index() + 1) + ')').addClass('over over-col');
						
					}).on('mouseleave', function(){
						$table.find('td,th,tr').removeClass('over over-col over-row');
					});
				});
			}
		};
	}//tablecloth
	if (config.moveResponsiveClasses.enabled) {
		var responsiveClasses = [
			'bp-mobile',
			'bp-non-mobile',
			'mobile-above-content',
			'mobile-below-content',
			'bp-tiny',
			'bp-tiny-only',
			'bp-small',
			'bp-small-only',
			'bp-medium-small',
			'bp-medium-small-only',
			'bp-large',
			'bp-large-only',
			'bp-large-extra',
			'bp-large-extra-only',
			'bp-max-width'
		];
		var responsiveClassesSel = '.' + responsiveClasses.join(',.');
		Drupal.behaviors.moveResponsiveClasses = {
			attach: function(context, settings) {
				//content-based... can we do away with this?
				$('section, section .field--name-field-section-body > .field__items > .field__item', context).children(responsiveClassesSel).not('.copy-responsive-classes-processed').each(function(){
					var $d = $(this);
					for(i=0; i<responsiveClasses.length; i++){
						var c = responsiveClasses[i];
						if ($d.hasClass(c)) {
							$d.removeClass(c).closest('section').addClass(c);
						}
					}
					
					
				});
				
				//field based...
				$('.field--name-field-section-css-class', context).each(function(){
					var cssClasses = $.trim($(this).text());
					if (cssClasses) $(this).closest('section').addClass(cssClasses);
					$(this).remove();
				});
				$('.field--name-field-id-attribute', context).each(function(){
					var id = $.trim($(this).text());
					if (id) $(this).closest('section').attr('id', id.replace(/\W/g,'_'));
					$(this).remove();
				});
			}
		};
	}//moveResponsiveClasses
	if (config.mobileBelowContent.enabled) {
		Drupal.behaviors.mobileBelowContent = {
			attach: function(context, settings) {
				$('.mobile-below-content', context).not('.mobile-below-content-processed').each(function(){
					var $d = $(this);
					var $clone = $d.clone().removeClass('mobile-below-content').addClass('mobile-below-content-clone bp-mobile clearfix');
					$d.addClass('mobile-below-content-processed bp-non-mobile');
					var $bc = $('#below-content');
					if (!$bc.length){
						var $bc = $('<div id="below-content" class="clearfix"></div>');
						var $content = $('.l-content-wrapper .l-content');
						var $lu = $content.find('.lastupdated');
						if ($lu.length) $bc.insertBefore($lu);
						else $content.append($bc);
					}
					$('#below-content').append($clone);
				});
				$('#below-content').children().removeClass('box');
			}
		};
	}//mobileBelowContent
	if (config.mobileAboveContent.enabled) {
		Drupal.behaviors.mobileAboveContent = {
			attach: function(context, settings) {
				$('.mobile-above-content', context).not('.mobile-above-content-processed').each(function(){
					var $d = $(this);
					var $clone = $d.clone().removeClass('mobile-above-content').addClass('mobile-above-content-clone bp-mobile clearfix');
					$d.addClass('mobile-above-content-processed bp-non-mobile');
					var $bc = $('#above-content');
					if (!$bc.length){
						var $bc = $('<div id="above-content" class="clearfix"></div>');
						var $content = $('.l-content-wrapper .l-content');
						$content.prepend($bc);
					}
					$('#above-content').append($clone);
				});
				$('#above-content').children().removeClass('box');
			}
		};
	}//mobileAboveContent
	if (config.appendToMobileMenu.enabled) {
		Drupal.behaviors.appendToMobileMenu = {
			attach: function(context, settings) {
				$('.append-to-mobile-menu', context).not('.append-to-mobile-menu-processed').each(function(){
					var $el = $(this);
					var $clone = $el.safeClone().removeClass('append-to-mobile-menu');
					$el.addClass('append-to-mobile-menu-processed');
					$clone.appendTo('.site-menu');
					$clone.removeClass('bp-non-mobile-only').addClass('bp-mobile-only');
					
				});
			}
		};
	}//appendToMobileMenu
	if (config.prependToMobileMenu.enabled) {
		Drupal.behaviors.prependToMobileMenu = {
			attach: function(context, settings) {
				$('.prepend-to-mobile-menu', context).not('.prepend-to-mobile-menu-processed').each(function(){
					var $el = $(this);
					var $clone = $el.safeClone().removeClass('prepend-to-mobile-menu');
					$el.addClass('prepend-to-mobile-menu-processed');
					$clone.prependTo('.site-menu');
					$clone.removeClass('bp-non-mobile-only').addClass('bp-mobile-only');
					
				});
			}
		};
	}//prependToMobileMenu
	if (config.contentCollapsible.enabled) {
		Drupal.behaviors.contentCollapsible = {
			attach: function(context, settings) {
				var moreButtonClasses = 'content-collapsible-more blue';
				var lessButtonClasses = 'content-collapsible-less white';
				var defMoreButtonText = 'More';
				var defLessButtonText = 'Less';
				var speed = 'fast';
				$('.content-collapsible', context)
					.not('.content-collapsible-processed')
					.each(function(){
						var $el = $(this).addClass('content-collapsible-processed');
						$el.wrapInner('<div class="content-collapsible-content" />');
						$el.prepend('<span role="presentation" class="content-collapsible-top"/>');
						$el.append('<span role="presentation" class="content-collapsible-bottom"/>');
						var moreButtonText = defMoreButtonText;
						var $moreButtonLabel = null; // this will override moreButtonText if set.
						if($el.attr('data-content-collapsible-more')) moreButtonText = $el.attr('data-content-collapsible-more');
						else {
							var $readMoreLabel = $el.find('.read-more-label');
							if($readMoreLabel.length){
								$readMoreLabel.detach();
								$moreButtonLabel = $readMoreLabel;
							}
						}
						var lessButtonText = defLessButtonText;
						var $lessButtonLabel = null; // this will override lessButtonText if set.
						if($el.attr('data-content-collapsible-less')) lessButtonText = $el.attr('data-content-collapsible-less');
						else {
							var $readlessLabel = $el.find('.read-less-label');
							if($readlessLabel.length){
								$readlessLabel.detach();
								$lessButtonLabel = $readlessLabel;
							}
						}
						var $toggle = $('<button type="button" class="content-collapsible-button button"></button>').click(function(){
							if ($el.hasClass('collapsed')){
								$el.removeClass('collapsed');
								$toggle.removeClass(moreButtonClasses).addClass(lessButtonClasses).empty();
								if($lessButtonLabel && $lessButtonLabel.length) $toggle.append($lessButtonLabel.html());
								else $toggle.text(lessButtonText);
								$el.slideDown(speed, function(){
									$el.trigger('content-expanded');
								});
							}
							else {
								$el.slideUp(speed, function(){
									$el.addClass('collapsed');
									$toggle.removeClass(lessButtonClasses).addClass(moreButtonClasses).empty();
									if($moreButtonLabel && $moreButtonLabel.length) $toggle.append($moreButtonLabel.html());
									else $toggle.text(moreButtonText);
									$el.trigger('content-collapsed')
								});
							}
						});
						if ($el.hasClass('collapsed')){
							$el.hide();
							$toggle.removeClass(lessButtonClasses).addClass(moreButtonClasses).empty();
							if($moreButtonLabel && $moreButtonLabel.length) $toggle.append($moreButtonLabel.html());
							else $toggle.text(moreButtonText);
						}
						else {
							$toggle.removeClass(moreButtonClasses).addClass(lessButtonClasses).empty();
							if($lessButtonLabel && $lessButtonLabel.length) $toggle.append($lessButtonLabel.html());
							else $toggle.text(lessButtonText);
						}
						$toggle.insertBefore($el);
						
					
				});
			}
		};
	}//contentCollapsible
	if (config.inlineReadMore.enabled) {
		Drupal.behaviors.inlineReadMore = {
			attach: function(context, settings) {
				var moreButtonClasses = 'inline-read-more link';
				var defMoreButtonText = 'Read More…';
				var speed = 'fast';
				$('.inline-read-more', context)
					.not('.inline-read-more-processed')
					.each(function(){
						var $el = $(this).addClass('inline-read-more-processed');
						var moreButtonText = defMoreButtonText;
						var $moreButtonLabel = null; // this will override moreButtonText if set.
						if($el.attr('data-inline-read-more-label')) moreButtonText = $el.attr('data-inline-read-more-label');
						else {
							var $readMoreLabel = $el.find('.read-more-label');
							if($readMoreLabel.length){
								$readMoreLabel.detach();
								$moreButtonLabel = $readMoreLabel;
							}
						}
						var $toggle = $('<button type="button" class="inline-read-more-button button"></button>').click(function(){
							
							$toggle.slideUp(speed);
							$el.removeClass('collapsed').slideDown(speed, function(){
								$el.trigger('content-expanded');
								$el.children().unwrap();
							});
						});
						$el.hide().addClass('collapsed');
						$toggle.addClass(moreButtonClasses).empty();
						if($moreButtonLabel && $moreButtonLabel.length) $toggle.append($moreButtonLabel.html());
						else $toggle.text(moreButtonText);
						var $prev = $el.prev();
						if (!$prev.is('p')){
							$prev = $('<p/>');
							$prev.insertBefore($el);
						}
						if($prev.text().trim()) {
							$prev.append('<span class="inline-read-more-spacer"> </span>');
							$prev.append($toggle);
						}
						else $prev.prepend($toggle);
						
					
				});
			}
		};
	}//contentCollapsible
	
	if (config.slideshowPlaceholders.enabled) {
		//float left and float right images should get some padding
		//This JS fix needed ONLY because of lack of the following fix for D7: https://www.drupal.org/node/936316
		//Otherwise the CSS would be sufficient
		Drupal.behaviors.slideshowPlaceholders = {
			attach: function(context, settings) {
				$('.slideshow-placeholder', context)
					.not('.slideshow-placeholder-processed')
					.each(function(){
						var $placeholder = $(this).addClass('slideshow-placeholder-processed').empty();
						var forceOptionset = null;
						if($placeholder.hasClass('force-optionset-program')) forceOptionset = 'optionset-program-slideshow';
						else if ($placeholder.hasClass('force-optionset-home-page')) forceOptionset = 'optionset-home-page-slideshow';
						var $flexSlider = $('div.flexslider').first();
						var $slideshowBlock = $flexSlider.closest('.block');
						//enforce use of program slideshow styles
						if(forceOptionset) $flexSlider.removeClass('optionset-home-page-slideshow optionset-program-slideshow').addClass(forceOptionset);
						if ($slideshowBlock.length || $flexSlider.length){
							var $region = $flexSlider.closest('.l-region');
							if ($slideshowBlock.length) $placeholder.append($slideshowBlock);
							else $placeholder.append($flexSlider);
							if ($region.length && !$.trim($region.html())){
								$region.remove(); //remove empty region, just in case it has margins or padding or something.
							}
						}
					});
			}
		};
	}//slideshowPlaceholders
	
	
	if (config.slideshowAutoFitText.enabled) {
		//automatically calculates and applies the largest size for the slideshow text when placed left (or right) of the image.
		$.fn.slideshowAutoFitText = function(){
			return this.each(function(){
			  if (!$(this).is('.field-collection-item-field-slide')) return;
			  
			  var $groupText = $(this).find('.group-right');
			  $groupText.css('font-size','');
			  $(this).removeClass('has-auto-fit-text');
			  if($(this).is('.suppress-auto-fit-text')) return;
			  
			  var $groupImage = $(this).find('.group-left');
			  if(Math.abs($groupText.offset().left - $groupImage.offset().left) > 1){ //if the image and text groups aren't stacked on top of each other...
					
				  var groupImageMaxHeight = 0;
				  $(this).closest('ul.slides').find('.group-left').each(function(){
					  var h = $(this).outerHeight();
					  if(h && h > groupImageMaxHeight) groupImageMaxHeight = h;
				  });
				  //log("text: " + $.trim($groupText.text()));
				  //log("groupImageMaxHeight: " + groupImageMaxHeight);
				
				  var fs = 24;
				  var fsunits = 'px';
				  var decAmount = .5;
				  var minFontSize = 10;
				  var textHeight = $groupText.outerHeight();
				  var lastFontSize = fs;
				  
				  
				  do {
					$groupText.css('font-size', fs + fsunits);
					lastFontSize = fs;
					 textHeight = $groupText.outerHeight();
					 //log("fs: " + fs + ", textHeight: " + textHeight);
					 fs -= decAmount;
				  }
				  while(fs > minFontSize && textHeight > groupImageMaxHeight);
				  
				  
				  /*
				  var decreaseFontSize = $(this).attr('data-decrease-font-size');
				  if(decreaseFontSize){
					  $groupText.css('font-size', (fs - parseFloat(decreaseFontSize)) + fsunits);
				  }*/
				  var standardAdjustFontSize = .9;
				  if(standardAdjustFontSize){
					lastFontSize *= standardAdjustFontSize;
				    $groupText.css('font-size', lastFontSize + fsunits);
				  } 
				  var adjustFontSize = $(this).attr('data-adjust-font-size');
				  if(adjustFontSize){
					  lastFontSize *= parseFloat(adjustFontSize)/100;
					  $groupText.css('font-size', lastFontSize + fsunits);
				  }
				  
				  $(this).addClass('has-auto-fit-text');
				  //log("final font size: " + lastFontSize + fsunits);
			  }
			  
			});
					
		};
		
		var slideshowAutoFitTextNow = function(){
			$('.field-collection-item-field-slide').slideshowAutoFitText();
		};
		var slideshowAutoFitTextLaterTimeout = null;
		var slideshowAutoFitTextLater = function(){
			clearTimeout(slideshowAutoFitTextLaterTimeout);
			slideshowAutoFitTextLaterTimeout = setTimeout(function(){
				slideshowAutoFitTextNow();
				
			}, 100);
		};
		$(window).on('load resize orientationchange', function(){
				slideshowAutoFitTextLater();
			
		});
		Drupal.behaviors.slideshowAutoFitText = {
			attach: function(context, settings) {
				slideshowAutoFitTextNow();
				slideshowAutoFitTextLater();
					
			}
		};
	}//slideshowAutoFitText
	
	
	if (config.imagePlaceholders.enabled) {
		//float left and float right images should get some padding
		//This JS fix needed ONLY because of lack of the following fix for D7: https://www.drupal.org/node/936316
		//Otherwise the CSS would be sufficient
		Drupal.behaviors.imagePlaceholders = {
			attach: function(context, settings) {
				$('.l-content .image-placeholder', context)
					.not('.image-placeholder-processed')
					.addClass('image-placeholder-processed')
					.empty()
					.each(function(){
						$(this).append($('.l-content .field--name-field-image').removeClass('field--name-field-image'))
					});
			}
		};
	}//imagePlaceholders
	if (config.moveResponsiveDataTableClasses.enabled) {
		var dataTableStylesSelector = '.' + dataTableStyles.join(',.');
		//move responsive data table styles from view to table generated by that view
		Drupal.behaviors.moveResponsiveDataTableClasses = {
			attach: function(context, settings) {
				if ($(context).is('.view')) context = $(context).parent()[0];
				$(dataTableStylesSelector, context)
					.not('table')
					.each(function(){
						var $el = $(this);
						$el.find('table').each(function(){
							var $table = $(this);
							$.each(dataTableStyles, function(index, c){
								if($el.hasClass(c)){
									$el.removeClass(c);
									$table.addClass(c);
								}
							});
						});
					});
			}
		};
	}//moveResponsiveDataTableClasses
	if (config.styleGuide.enabled) {
		var dataTableStylesStr = dataTableStyles.join(' ');
		Drupal.behaviors.styleGuide = {
			attach: function(context, settings) {
				$('table.style-guide-flavor-switcher', context).each(function(){
					var $table = $(this).removeClass('style-guide-flavor-switcher').uniqueId();
					
					//var origClasses = $table.attr('class');
					var $select = $('<select><option value="">(none)</option></select>')
						.uniqueId()
						.change(function(){
							$table.removeClass(dataTableStylesStr).addClass($(this).val());
						});
					var $label  = $('<label>Table style class: </label>').attr('for', $select.attr('id'));
					for(var i=0; i<dataTableStyles.length; i++){
						var $option = $('<option />').attr('value', dataTableStyles[i]).text(dataTableStyles[i]);
						if ($table.hasClass($option.attr('value'))) {
							$option.attr('selected','selected');
							//$table.removeClass($option.attr('value'));
							//origClasses = $table.attr('class');
							//$table.addClass($option.attr('value'));
						}
						
						$select.append($option);
					}
					var $caption = $table.children('caption:first');
					if (!$caption.length) {
						$caption = $('<caption/>');
						$table.prepend($caption);
					}
					var $switcher = $('<div class="style-guide-flavor-switcher-container"/>')
					$switcher.append($label);
					$switcher.append($select);
					$caption.append($switcher);
				});
			}
		};
	}//imagePlaceholders
	if (config.tablesawConfig.enabled) {
		// the tablesaw plugin uses data-tablesaw-mode attribute to determine behavior - ckeditor doesn't provide a way to edit this, so we've got a series of classes instead
		Drupal.behaviors.tablesawConfig = {
			attach: function(context, settings) {
				if ($(context).is('.view')) context = $(context).parent()[0];
				var modes = ['stack','columntoggle','swipe'];
				var pref = 'table-';
				var tableSortableClass = pref + 'sortable'
				
				var anyNodeTypeSelector = "." + pref + modes.join(',.' + pref);
				var ourSelector = anyNodeTypeSelector;// 'table.' + pref + modes.join(',table.' + pref);// 'table.tablesaw-stack,table.tablesaw-columntoggle,table.tablesaw-swipe';
				var ourClasses = pref + modes.join(' ' + pref);// 'tablesaw-stack tablesaw-columntoggle tablesaw-swipe';
				var defaultMode = 'columntoggle';
				var defaultMinimapMode = 'columntoggle';
				
				$('.minimap', context).each(function(){
					var $el = $(this);
					var $table;
					if ($el.is('table')) {
						$table = $el;
					}
					else {
						$table = $el.find('table');
					}
					$el.removeClass('minimap')
					
					$table.attr('data-tablesaw-minimap','');
					if (!($el.is(ourSelector))) $el.addClass(pref + defaultMinimapMode);
				});
				$(ourSelector, context).each(function(){
					var $el = $(this);
					var $table;
					if ($el.is('table')) {
						$table = $el;
					}
					else {
						$table = $el.find('table');
					}
					$el.addClass('add-tablesaw');
					var mode = defaultMode;
					for(var i=0; i<modes.length; i++){
						if ($el.hasClass(pref + modes[i])) mode = modes[i];
					}
					
					$table.attr('data-tablesaw-mode', mode);
					$el.removeClass(ourClasses);
				});
				
				
				$('.add-tablesaw', context).each(function(){
					var $el = $(this);
					var $table;
					if ($el.is('table')) {
						$table = $el;
					}
					else {
						$table = $el.find('table');
					}
					
					$table.each(function(){
						var $curTable = $(this);
						$curTable
							.filter(function(){
								var $cells = $(this).find('> * > tr > *')
								return $cells.filter('[headers]').length == 0 && $cells.filter('[colspan]').length == 0 && $cells.filter('[rowspan]').length == 0;
							})
							.find('> tbody > tr > th:not([scope])').each(function(){
								$(this).attr('scope','row');
							});
						if($el.hasClass(tableSortableClass)){
							$curTable.attr('data-tablesaw-sortable','').attr('data-tablesaw-sortable-switch','');
							$curTable.find('thead th').attr('data-tablesaw-sortable-col','');//.first().attr('data-tablesaw-sortable-default-col','');
						}
						if ($curTable.attr('data-tablesaw-mode') == 'columntoggle' && !$curTable.find('> thead > tr > th[data-tablesaw-priority]').length) {
							var $thScopeRow = $curTable.find('> tbody > tr > th[scope="row"]:first');
							var autoPersistColIndex = $thScopeRow.length ? $thScopeRow.index() : 0;
							
							$curTable.find('> thead > tr > th').each(function(index){
								$(this).attr('data-tablesaw-priority', (index == autoPersistColIndex) ? 'persist' : Math.max(Math.min(index, 6), 1));
							});
						}
						$curTable.addClass('tablesaw').tablesaw().trigger( "enhance.tablesaw" );
					});
					$el.removeClass('add-tablesaw');
					var updateSortLabel = function(){
						var $btn = $(this);
						var $th = $btn.closest('th');
						var headerLabel = $.trim($btn.parent().text());
						var curDir = '';
						if($th.hasClass('tablesaw-sortable-ascending')) curDir = ', sorted from A to Z';
						else if ($th.hasClass('tablesaw-sortable-descending')) curDir = ', sorted from Z to A';
						var toggleDir = $th.hasClass('tablesaw-sortable-ascending') ? 'Z to A' : 'A to Z';
						$btn.attr('aria-label', headerLabel + curDir + ' (Click to sort from ' + toggleDir + ')');
					};
					var initSortLabels = function(){
						$el.find('.tablesaw-sortable-btn').click(updateSortLabel).each(updateSortLabel);
					};
					var postInitFixes = function(){
						initSortLabels();
						var $switches
						$el.find('.tablesaw-sortable-switch').each(function(){
							var $select = $(this).find('select');
							$select.prepend('<option class="none"> - SELECT - </option>');
							var $table = $(this).closest('.tablesaw-bar').next();
							if(!$table.find('> thead > th[data-tablesaw-sortable-default-col="true"]').length){
								$select.val('').change();
							}
							$select.change(function(){
								if ($select.val()) $select.children('.none').remove();
								setTimeout(function(){
									$el.find('.tablesaw-sortable-btn').each(updateSortLabel);
								}, 1);
							});
						});//'[data-tablesaw-sortable-default-col]').length
					};
					setTimeout(postInitFixes, 1);
				});
			}
		};
	}//tablesawConfig
	
	if(config.slideshowAccessibility.enabled){
		// this behavior, which should be enabled on dev only, will replace any links to dpcpsi or ncf with links to the dev site of these
		Drupal.behaviors.slideshowAccessibility = {
			attach: function(context, settings) {
				var enableTabbingNextPrev = function($flexslider){
					$flexslider.find('.flex-direction-nav a').removeAttr('tabindex').filter('.flex-disabled').attr('tabindex','-1');
				}
				/*
				var disableTabbingNextPrev = function($flexslider){
					$flexslider.find('.flex-direction-nav a').attr('tabindex','-1');
				}*/
				var disableAllLinks = function($flexslider){
					$flexslider.find('ul.slides a').not('[tabindex]').addClass('slideshow-link-disabled').attr('tabindex','-1'); 
				};
				var disableLinksForInactiveSlides = function($flexslider){
					$flexslider.find('ul.slides a').not('[tabindex]').addClass('slideshow-link-disabled').attr('tabindex','-1'); 
					$flexslider.find('.flex-active-slide a.slideshow-link-disabled').removeAttr('tabindex').removeClass('slideshow-link-disabled'); 
				}
				var hideInactiveSlidesFromScreenReaders = function($flexslider){
					$flexslider.find('ul.slides li').each(function(){
						$(this).is('.flex-active-slide') ? $(this).removeAttr('aria-hidden') : $(this).attr('aria-hidden', true);
					}); 
				}
				$('.flexslider').not('.slideshow-accessibility-processed').each(function(){
					var $flexslider = $(this).addClass('slideshow-accessibility-processed').uniqueId();
					var flexsliderId = $flexslider.attr('id');
					$flexslider.find('.flex-control-paging').each(function(){
						$(this)
							.find('> li > a').each(function(){
								
								var slideIndex = $(this).parent().index();
								var slideNumber = slideIndex + 1;
								var $slide = $flexslider.find('ul.slides > li:eq(' + slideIndex + ')');
								var slideTitle = $slide.find('.homeslide-title').text()
								var linkText = 'Go to slide "' + slideTitle + '"';
								$(this)
									/*.attr('role','presentation')
								   .attr('aria-hidden', 'true')
								   .attr('tabindex','-1') */
							   .attr('role','button')
							   .attr('aria-controls', flexsliderId)
							   .attr('title', linkText)
							   .attr('accesskey', slideNumber)
							   .empty().append($('<span class="element-invisible"/>').text(linkText))
							   .attr('href', window.location.toString() + '#Slide_' + slideNumber)
							 
							    .keydown(function(e){
								   //log("keydown: " + e.which);
								   switch(e.which){
									   case 32: //space
									   case 37: //left
									   case 39: //right
										   e.preventDefault();
										   return false;
								   }
							    })
							    .keyup(function(e){
								   //log("keyup: " + e.which);
								   switch(e.which){
									   case 37: //left
									   case 39: //right
											$(this).closest('li')[e.which == 37 ? 'prev' : 'next']('li').find('a').focus();//.click()
										   e.preventDefault();
										   return false;
									   case 32: //space
										   $(this).trigger('click');
										   e.preventDefault();
										   return false;
								   }
							    })
							   ;
						   
						});
					});
					
					$flexslider.find('.flex-direction-nav').each(function(){
						$(this).find('a').each(function(){
							$(this)
								.add($(this).parent().get(0))
								.attr('aria-controls', flexsliderId)
								.attr('accesskey', $(this).is('.flex-next') ? 'n' : 'p')
								.makeRoleButton()
								;
							;
						});
					});
					enableTabbingNextPrev($flexslider);
					
					
					$flexslider.find('.flex-pauseplay a').each(function(){
						$(this)
							.attr('aria-controls', flexsliderId)
							.attr('accesskey', 's')
							//.addClass('element-invisible element-focusable')
							.makeRoleButton()
						;
					});
/*
    // Callback API
    start: function(){},            //Callback: function(slider) - Fires when the slider loads the first slide
    before: function(){},           //Callback: function(slider) - Fires asynchronously with each slider animation
    after: function(){},            //Callback: function(slider) - Fires after each slider animation completes
    end: function(){},              //Callback: function(slider) - Fires when the slider reaches the last slide (asynchronous)
    added: function(){},            //{NEW} Callback: function(slider) - Fires after a slide is added
    removed: function(){},           //{NEW} Callback: function(slider) - Fires after a slide is removed
    init: function() {}             //{NEW} Callback: function(slider) - Fires after the slider is initially setup
*/
					$flexslider.bind('before', function(){
						//log("BEFORE!");
						disableAllLinks($flexslider);
						//disableTabbingNextPrev($flexslider);
					});
					$flexslider.bind('after', function(){
						//log("AFTER!");
						disableLinksForInactiveSlides($flexslider);
						hideInactiveSlidesFromScreenReaders($flexslider);
						enableTabbingNextPrev($flexslider);
						if (document.activeElement && $(document.activeElement).is('.flex-disabled')){
							if($(document.activeElement).is('.flex-prev')){
								$flexslider.find('.flex-next').not('.flex-disabled').filter(':visible').focus();
							}
							else if ($(document.activeElement).is('.flex-next')){
								$flexslider.find('.flex-prev').not('.flex-disabled').filter(':visible').focus();
							}
						}
						//$flexslider.find('.flex-active-slide a:first').focus();
					});
					disableLinksForInactiveSlides($flexslider);
					hideInactiveSlidesFromScreenReaders($flexslider);
				});
			}
		};
	}//slideshowAccessibility
	if(config.movePageTitleToHeaderImage.enabled){
		Drupal.behaviors.movePageTitleToHeaderImage = {
			attach: function(context, settings) {
				$('.header-image-wrapper', context).not('.has-page-title').each(function(){
					var $pageTitle = $('h1.page-title:first');
					if($pageTitle.length){
						var $pageTitleCell = $('<div class="page-title-wrapper"/>');
						$pageTitleCell.append($pageTitle.removeClass('page-title'));
						$(this).prepend($pageTitleCell);
						$(this).addClass('has-page-title');
						$(this).parent().addClass('header-image-wrapper-table');
						
						//$(this).find('.header-image').addClass('as-background-image').css('background-image','url(' + $(this).find('.header-image img').attr('src')  + ')');
						$('body').addClass('has-page-title-in-header');
					}
					
				});
				
		
				$('.header-image', context).not('.as-background-image').each(function(){
					$(this).addClass('as-background-image').css('background-image','url(' + $(this).find('img').attr('src')  + ')');
				});
			}
		};
	}//movePageTitleToHeaderImage
	
	if(config.customizeSlideFields.enabled){
		Drupal.behaviors.customizeSlideFields = {
			attach: function(context, settings) {
				var customStyles = '';
				var defaultButtonClass = 'blue-dark';
				var breakpoints = {
					'full':{
						'big':'@media all and (min-width: 800px)'
					},
					'one':{
						'big':'@media all and (min-width: 600px)'
					},
					'two':{
						'big':'@media all and (min-width: 500px)'
					},
					'three':{
						'big':'@media all and (min-width: 400px)'
					}
				};
				var bigMedia = function(style, type){
					return breakpoints[type].big + '{' + style + '}';
				}
				
				var $defaultSlideStyles = $('.default-slide-styles', context)
					.not('.customize-slide-fields-processed')
					.each(function(){
						$(this).addClass('customize-slide-fields-processed');
						var $defs = $(this).find('> div.entity > *');
						$('.field-collection-item-field-slide').each(function(){
							var $slide = $(this);
							$defs.each(function(){
								var $d = $(this);
								var c = $d.attr('class');
								if (!$slide.find('.' + c).length){
									$slide.append($d.clone().hide());
								}
							});
						});
				}).remove();
				var setActiveSlide = function($flexslider){
					$flexslider.attr('data-active-slide', $flexslider.find('.flex-active-slide').uniqueId().attr('id') || '');
				};
				$('.flexslider', context).bind('before', function(){
					$(this).attr('data-active-slide', '');
				});
				$('.flexslider', context).bind('after', function(){
					setActiveSlide($(this));
				});
				setActiveSlide($('.flexslider', context));
				$('.field-collection-item-field-slide', context)
					.not('.customize-slide-fields-processed')
					.each(function(){
						
					var $slide = $(this).addClass('customize-slide-fields-processed');//.uniqueId();
					var $slideParent = $slide.parent().uniqueId();
					var slideSelector = '#' + $slideParent.attr('id');
					var slideBackgroundSelector = '[data-active-slide="' + $slideParent.attr('id') + '"] ul.slides'
					$slide.find('.button-class').each(function(){ 
						var c = $.trim($(this).text());
						if (c) { 
							$slide.find('.' + defaultButtonClass).removeClass(defaultButtonClass).addClass(c); 
						} 
						$(this).remove(); 
					});
					$slide.find('.slide-style-options > option').each(function(){
						$(this).addClass($(this).text().split('_').join('-')).text(1);
						//console.log('found ' + $(this).attr('class'));
					});
					$slide.find('.add-gradient').each(function(){ 
						if ($(this).text() == 1) { 
							$slide.addClass('add-gradient');
							$slide.find('.group-left,.slideshow-image,.file').each(function(){
								$(this).prepend('<span class="gradient-left" role="presentation"/>');
								$(this).append('<span class="gradient-right" role="presentation"/>');
							});
						} 
						$(this).remove(); 
					});
					$slide.find('.hide-all-text').each(function(){ 
						if ($(this).text() == 1) { 
							$slide.addClass('hide-all-text'); 
						} 
						$(this).remove(); 
					});
					$slide.find('.suppress-auto-fit-text').each(function(){ 
						if ($(this).text() == 1) { 
							$slide.addClass('suppress-auto-fit-text').css('font-size',''); 
						} 
						$(this).remove(); 
					});
					if(!$slide.find('.group-left img').length){
						$slide.addClass('text-only-slide');
					}
					$slide.find('.text-on-right').each(function(){ 
						if ($(this).text() == 1) { 
							$slide.addClass('text-on-right'); 
						} 
						$(this).remove(); 
					});
					
					/*
					$slide.find('.desktop-image-size').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							var cn = 'desktop-image-size--' + s;
							$slide.children().addClass(cn);
						}
						$(this).remove(); 
					});
					*/
					$slide.not('.text-only-slide,.hide-all-text').find('.desktop-image-width').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							var w = parseFloat(s);
							$slide.children('.group-left').addClass('custom-desktop-image-width').css('width',w + '%'); //should get overridden for mobile display via !important
							$slide.children('.group-right').addClass('custom-desktop-image-width').css('width',(100 - w) + '%');
							/*$slide.children().addClass('custom-desktop-image-width');
							s = Math.round(parseFloat(s));
							$slide.children('.group-left').css('flex', s + ' 1 0%');//.addClass('has-custom-width').css('width',s);//should get overridden for mobile display via !important
							$slide.children('.group-right').css('flex', (100 - s) +  ' 1 0%');
							*/
						}
						$(this).remove(); 
					});
					$slide.find('.image-padding').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.children('.group-left').addClass('has-custom-padding').css('padding',s);
						}
						$(this).remove(); 
					});
					$slide.find('.text-padding').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.children('.group-right').addClass('has-custom-padding').css('padding',s);
						}
						$(this).remove(); 
					});
					/*
					$slide.find('.decrease-font-size').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.attr('data-decrease-font-size', s);
						}
						$(this).remove(); 
					});*/
					
					$slide.find('.adjust-font-size').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.attr('data-adjust-font-size', s);
						}
						$(this).remove(); 
					});
					$slide.find('.text-align').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.children('.group-right').addClass('has-custom-text-align').find('> *, .homeslide-description > p, .homeslide-title').css('text-align',s);
						}
						$(this).remove(); 
					});
					$slide.find('.text-align-vertical').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							var alignSelf = null;
							switch(s){
								case 'top':
									alignSelf = 'flex-start';
									break;
								case 'middle':
									alignSelf = 'center'; //no need, this is the default, but let's do it anyway in case default changes
									break;
								case 'bottom':
									alignSelf = 'flex-end';
									break;
							}
							var $txt = $slide.children('.group-right').addClass('has-custom-text-align-vertical');
							if(alignSelf) $txt.css('align-self',alignSelf);
						}
						$(this).remove(); 
					});
					$slide.find('.title-font-size').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.find('.homeslide-title').wrap($('<div class="homeslide-title-wrapper" />').css('font-size', s));
						}
						$(this).remove(); 
					});
					/*
					$slide.find('.image-align').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							$slide.children('.group-left').addClass('has-custom-image-align').find('> *, .homeslide-description > p').css('text-align',s);
						}
						$(this).remove(); 
					});
					$slide.find('.image-align-vertical').each(function(){ 
						var s = $.trim($(this).text());
						if(s) {
							var alignSelf = null;
							switch(s){
								case 'top':
									alignSelf = 'flex-start';
									break;
								case 'middle':
									alignSelf = 'center'; //no need, this is the default, but let's do it anyway in case default changes
									break;
								case 'bottom':
									alignSelf = 'flex-end';
									break;
							}
							var $txt = $slide.children('.group-left').addClass('has-custom-image-align');
							if(alignSelf) $txt.css('align-self',alignSelf);
						}
						$(this).remove(); 
					});*/
					var $flexslider = $slide.closest('.flexslider');
					var type = 'full';
					if ($flexslider.closest('.l-content-wrapper').length){
						var $lpage = $flexslider.closest('.l-page');
						if($lpage.hasClass('has-one-sidebar')) type = 'one';
						else if ($lpage.hasClass('has-two-sidebars')) type = 'two';
						else if ($lpage.hasClass('has-three-sidebars')) type = 'three';
					}
					$flexslider.is('.optionset-home-page-slideshow') ? 'homepage' : 'program';
					$slide.find('.background-color').each(function(){ 
						var color = $.trim($(this).text());
						if (color){
							customStyles += bigMedia(slideSelector + ', ' + slideBackgroundSelector + ' {background-color: ' + color + ';}', type) + '\n';
							//customStyles += bigMedia(slideSelector + ' {background-color: ' + color + ';}', type) + '\n';
							if($slide.hasClass('add-gradient')){
								var colorRGB = hexToRgb(color).join(',');
								$slide.find('.gradient-left').css('background', 'linear-gradient(to right,' + color + ' 0%,rgba('  +colorRGB + ',0.75) 50%,rgba('  +colorRGB + ',0.01) 100%)');
								$slide.find('.gradient-right').css('background', 'linear-gradient(to right,rgba('  +colorRGB + ',0.01) 0%,rgba('  +colorRGB + ',0.75) 50%,'  +color + ' 100%)');
							}
						}
						$(this).remove(); 
					});
					$slide.find('.text-color').each(function(){ 
						var color = $.trim($(this).text());
						if (color) customStyles +=  bigMedia(slideSelector + ', ' + slideSelector + ' :not(.button) {color: ' + color + ';}', type) + '\n';
						$(this).remove(); 
					});
					if(customStyles) addStyle(customStyles);
				});
				
				$('.flexslider', context)
					.not('.customize-slide-processed')
					.each(function(){
						var $flex = $(this).addClass('customize-slide-processed').uniqueId();
						var $slides = $flex.find('.field-collection-item-field-slide');
						var numOnRight = $slides.filter('.text-on-right').length;
						var numInMiddle = $slides.filter('.hide-all-text').length;
						if (numOnRight > 0){
							if (numOnRight == $slides.length){
								$flex.find('.flex-control-nav').addClass('right-align');
							}
							else {
								$flex.find('.flex-control-nav').addClass('full-width');
							}
						} else if (numInMiddle > 0){
							
							if (numInMiddle == $slides.length){
								$flex.find('.flex-control-nav').addClass('full-width');
							}
							else {
								$flex.find('.flex-control-nav').addClass('full-width');
							}
						}
				});

			}
		};
	}//customizeSlideFields
	
	
	if(config.viewOrHideAll.enabled){
		if (!window.viewOrHideAllInitialized){
			window.viewOrHideAllInitialized = true;
			$(document).on("click", ".viewall", function(e){
				//alert("");
				$(".active_content_links").removeClass("hidden");
				$(".active_content_links").slideDown("fast", function(){
					$(".bot_menu").height(($(".right_panel").height())-($(".menu").height())-2);
					//alert($(".right_panel").height());
				});
				$("ul.links_list li").addClass("active");
				$(this).hide();
				$(".hideall").show();
				e.preventDefault();
				return false;
			});
			$(document).on("click", ".hideall", function(e){
				//alert("");
				$(".active_content_links").addClass("hidden");
				$(".active_content_links").slideUp("fast", function(){
					$(".bot_menu").height(($(".right_panel").height())-($(".menu").height())-2);
					//alert($(".right_panel").height());
				});
				$("ul.links_list li").removeClass("active");
				$(this).hide();
				$(".viewall").show();
				e.preventDefault();
				return false;
			});
		}
	}//viewOrHideAll
	
	
	if(config.siteMapMenu.enabled && $.fn.treeview){
		Drupal.behaviors.siteMapMenu = {
			attach: function(context, settings) {
				$(".sitemapmenu", context)
					.not('site-map-menu-processed')
					.addClass('site-map-menu-processed')
					.find('ul').treeview({
						collapsed: true,
						animated: "medium",
						//control:"#sidetreecontrol",
						persist: "location"
				});
			}
		};
	}//siteMapMenu
	
	
	
	
	if(config.linksPage.enabled){
		Drupal.behaviors.linksPage = {
			attach: function(context, settings) {
				
				//Links Page
					$("ul.links_list2 li", context).mouseover(function () {
						//alert("");
						$(this).addClass("over");							
					});
					//$("ul.links_list2 li").mouseout(function () {
					//	$(this).removeClass("over");						
					//});
					$("a.list_link", context).click(function () {
						//$(".active_content_links").slideUp("slow");
						//$("ul.links_list2 li").removeClass("active");
						if ( $(this).parent().children(".active_content_links").hasClass("hidden") ) {
							$(this).parent().children(".active_content_links").removeClass("hidden");
							$(this).parent().children(".active_content_links").slideDown("fast", function(){
									$(".bot_menu").height(($(".right_panel").height())-($(".menu").height())-2);
									//alert($(".right_panel").height());
								});
							$(this).parent().addClass("active");
							$(".viewall").hide();					
							$(".hideall").show();
							//alert($(".active").length+" this is one  "+$("ul.links_list2").children().length);
						} else {
							$(this).parent().children(".active_content_links").slideUp();
							$(this).parent().children(".active_content_links").addClass("hidden");
							$(this).parent().removeClass("active");
							if($(this).parent().hasClass("intab")){
								$(this).parent().parent().parent().removeClass("active");
							}
							//alert($(".active").length+" this is two "+$("ul.links_list2").children().length);
							if($(".active").length<2){
								$(".viewall").show();					
								$(".hideall").hide();
							}
						}
						return false;
					});
				$('.links_list2 li a.main_links', context).next('br').remove();
			}
		};
	}//linksPage
		
	/*
	var $ = jQuery;
var eh = {};
$('[data-equal-heights-group]').each(function(){
 var g = $(this).data('equal-heights-group');
 if (!eh[g]) eh[g] = $();
 eh[g] = eh[g].add(this);
 console.log("adding " + g);
});
console.log(eh);

for(var nm in eh){
 var $s = eh[nm];
  $s.css('height','');
  var max = 0;
  $s.each(function(){
   var oh = $(this).outerHeight();
   if(oh > max){
     max = oh;
     console.log("new max height: " + max);
   }
  });
  $s.height(max);
}   
*/
	if(config.equalHeights.enabled){
		// this behavior, which should be enabled on dev only, will replace any links to dpcpsi or ncf with links to the dev site of these
		Drupal.behaviors.equalHeights = {
			attach: function(context, settings) {
				var eh = {};
				$('[data-equal-heights-group]', context).each(function(){
				 var g = $(this).data('equal-heights-group');
				 if (!eh[g]) eh[g] = $();
				 eh[g] = eh[g].add(this);
				 //log("adding " + g);
				});
				//log(eh);
				var applyEqualHeights = function(){
					for(var nm in eh){
					 var $s = eh[nm];
					  $s.css('height','');
					  var max = 0;
					  $s.each(function(){
					   var oh = $(this).outerHeight();
					   if(oh > max){
						 max = oh;
						 //log("new max height: " + max);
					   }
					  });
					  $s.outerHeight(max);
					}
				};
				applyEqualHeights();
				equalHeightsTimer = null;
				var invalidateEqualHeights = function(){
					clearTimeout(equalHeightsTimer);
					equalHeightsTimer = setTimeout(applyEqualHeights, 100);
				};
				$(window).on('resize orientationchange load', invalidateEqualHeights);
			}
		};
	}//equalHeights
	
	
	if(config.tableHelpers.enabled){
		Drupal.behaviors.tableHelpers = {
			attach: function(context, settings) {
				$('thead th.phone-cell').each(function(){ 
					$(this).closest('table')
						.find('> tbody > tr')
						.children(':nth-child(' + ($(this).index() + 1) + ')')
						.addClass('phone-cell').each(function(){
							var phoneHTML = $(this).html();
							var phoneText = $(this).text().replace(/\D/g,''); 
							$(this).empty().append($('<a/>').attr('href','tel:+1' + phoneText).html(phoneHTML));
						});
				});
				$('thead th.email-cell').each(function(){ 
					$(this).closest('table')
						.find('> tbody > tr')
						.children(':nth-child(' + ($(this).index() + 1) + ')')
						.addClass('email-cell');
				});
				$('thead th.title-cell').each(function(){ 
					$(this).closest('table')
						.find('> tbody > tr')
						.children(':nth-child(' + ($(this).index() + 1) + ')')
						.addClass('title-cell');
				});
	
			}
		};
	}//tableHelpers
	
	
	
	if(config.devURLReplacement.enabled){
		// this behavior, which should be enabled on dev only, will replace any links to dpcpsi or ncf with links to the dev site of these
		Drupal.behaviors.devURLReplacement = {
			attach: function(context, settings) {
				$('a[href*="' + prodSubdomainDPCPSI + '.' + prodHost + '"],a[href*="' + prodSubdomainNCF + '.' + prodHost + '"]').each(function(){
					var href = $(this).attr('href');
					var origHREF = href;
					href = href.replace(prodSubdomainDPCPSI + '.' + prodHost, devSubdomainDPCPSI + '.' + devHost);
					href = href.replace(prodSubdomainNCF + '.' + prodHost, devSubdomainNCF + '.' + devHost);
					if(href != origHREF) {
						href = href.replace("http://", window.location.protocol + '//');
						href = href.replace("https://", window.location.protocol + '//');
						$(this).attr('href', href).attr('title', 'DEV ONLY: ' + origHREF + ' --> ' + href);
					}
				});
			}
		};
	}
	
	
})(jQuery, Drupal, window);