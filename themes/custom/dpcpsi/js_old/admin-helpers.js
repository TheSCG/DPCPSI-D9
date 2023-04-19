(function($){
  
  var consoleLogEnabled = true;
  
  function consoleLog(msg){
	  if (consoleLogEnabled && window.console && window.console.log) window.console.log(msg);
  }
  
	function addAdminHelpers(){
		consoleLog("addAdminHelpers()");
		var $hiddenAdminMenu = null;
		window.hideAdminMenu = function(){
			// sometimes it is useful to see the page without the admin menu.
			var $m = $('#admin-menu').detach();
			if ($m.length){
				$hiddenAdminMenu = $m;
				$('body').removeClass("admin-menu");
			}
			else{
				$hiddenAdminMenu = null;
			}
		}
		window.showAdminMenu = function(){
			if($hiddenAdminMenu && $hiddenAdminMenu.length){
				$('body').append($hiddenAdminMenu);
				$hiddenAdminMenu = null;
				$('body').addClass("admin-menu");
			}
		}
		
		
		var $hiddenDevMarker = null;
		window.hideDevMarker = function(){
			var $m = $('#dev-marker').detach();
			if ($m.length){
				$hiddenDevMarker = $m;
			}
		}
		window.showDevMarker = function(){
			if($hiddenDevMarker){
				$('body').append($hiddenDevMarker);
			}
		}
		
		
		window.hideAdminTabs = function(){
			$('.l-content .tabs.tabs--primary').first().addClass('temp-hidden-admin-tab').hide();
		}
		window.showAdminTabs = function(){
			$('.temp-hidden-admin-tab').removeClass('temp-hidden-admin-tab').show();
		}
		window.hideAdminActionLinks = function(){
			$('.admin-action-links').addClass('temp-hidden-admin-links').hide();
		}
		window.showAdminActionLinks = function(){
			$('.temp-hidden-admin-links').removeClass('temp-hidden-admin-links').show();
		}
		
		window.hideHelp = function(){
			$('.l-region--help').css('display','none');
		}
		window.showHelp = function(){
			$('.l-region--help').css('display','');
		}
		
		
	
		
		
		window.hideAdminBlocks = function(){
			$('#block-menu-devel,#block-views-tags-for-this-node-block').addClass('temp-hidden-admin-block').hide();
			if ($(".region-abovefooter").children().length == $(".region-abovefooter").children(".temp-hidden-admin-block").length){
				$("#abovefooter").addClass('temp-hidden-admin-block').hide();
			}
		}
		window.showAdminBlocks = function(){
			$('.temp-hidden-admin-block').removeClass('temp-hidden-admin-block').show();
		}
		
		
		window.disableContextualLinks = function(){
			$('.contextual-links-region').removeClass('contextual-links-region').addClass('contextual-links-region-disabled');
			$('.contextual-links-wrapper').addClass('temp-wrapper-disabled').css('visibility','hidden');
		}
		window.enableContextualLinks = function(){
			$('.contextual-links-region-disabled').removeClass('contextual-links-region-disabled').addClass('contextual-links-region');
			$('.contextual-links-wrapper.temp-wrapper-disabled').removeClass('temp-wrapper-disabled').css('visibility','');
		}
		
		
		window.hideMessages = function(){
			$('.messages').addClass('temp-hidden-messages').hide();
		}
		window.showMessages = function(){
			$('.temp-hidden-messages').removeClass('temp-hidden-messages').show();
		}
		
		window.hideFieldCollectionLinks = function(){
			$('.field-collection-view-links,.action-links-field-collection-add,.action-links-field-collection-remove,.action-links-field-collection-delete').addClass('temp-hidden-fc-links').hide();
		}
		window.showFieldCollectionLinks = function(){
			$('.temp-hidden-fc-links').removeClass('temp-hidden-fc-links').show();
		}
		
		window.removeNodeUnpublishedClass = function(){
			$('.node-unpublished').addClass('node-unpublished-disabled').removeClass('node-unpublished');
		}
		window.addNodeUnpublishedClass = function(){
			$('.node-unpublished-disabled').addClass('node-unpublished').removeClass('node-unpublished-disabled');
		}
		
		window.hideSimpleMetadata = function(){
			$('#simplemeta-meta-form-ajax-wrapper').css('display','none');
		}
		
		window.showSimpleMetadata = function(){
			$('#simplemeta-meta-form-ajax-wrapper').css('display','');
		}
		
		window.enablePublicPreview = function(){
			$('body').addClass('public-preview-enabled');
			window.hideDevMarker();
			window.hideAdminMenu();
			window.hideAdminTabs();
			window.hideHelp();
			window.hideAdminActionLinks();
			window.hideMessages();
			window.hideFieldCollectionLinks();
			window.hideAdminBlocks();
			window.disableContextualLinks();
			window.removeNodeUnpublishedClass();
			window.hideSimpleMetadata();
			$('body').trigger('public-preview-change').trigger('public-preview-enabled');
			$(window).resize();
			$('body').removeClass('logged-in').addClass('not-logged-in');
			$('#admin-helper-preview').hide();
			$('#admin-helper-preview-finish').show();
		}
		window.disablePublicPreview = function(){
			$('body').removeClass('public-preview-enabled');
			window.showDevMarker();
			window.showAdminMenu();
			window.showAdminTabs();
			window.showHelp();
			window.showAdminActionLinks();
			window.showMessages();
			window.showFieldCollectionLinks();
			window.showAdminBlocks();
			window.enableContextualLinks();
			window.addNodeUnpublishedClass();
			window.showSimpleMetadata();
			$('body').trigger('public-preview-change').trigger('public-preview-disabled');
			$(window).resize();
			$('body').removeClass('not-logged-in').addClass('logged-in');
			$('#admin-helper-preview').show();
			$('#admin-helper-preview-finish').hide();
		}
		window.togglePublicPreview = function(){
			
			if($('body').hasClass('public-preview-enabled')){
				window.disablePublicPreview();
			}
			else{
				window.enablePublicPreview();
			}
		}
		/* // commented out in favor of buttons
		$(document).on("click", function(e){
			if (e.ctrlKey || e.shiftKey){
				window.togglePublicPreview();
			}
		}); */
	}
	function loadIfMenuExists(){
		if(!$('#admin-menu').length) {
			setTimeout(loadIfMenuExists, 500);
			return;
		}
		addAdminHelpers();
		$('#admin-menu-menu').prepend('<li><a href="javascript:void(0)" role="button" id="admin-helper-preview" title="Click to view this page as it should appear to a regular un-authenticated user">Preview</a></li>');
		$('body').append('<button type="button" id="admin-helper-preview-finish" style="display: none; position: fixed; bottom: 0; right: 0; background: rgba(0,0,0,.5); color: white; border: none; border-top-left-radius: .7em; z-index: 999999;">Finish Preview</button>');
		$('#admin-helper-preview').click(window.enablePublicPreview);
		$('#admin-helper-preview-finish').click(window.disablePublicPreview);
	}
	Drupal.behaviors.nihAdminHelpers = {
		attach: function(context, settings) {
			if (window.addAdminHelpersProcessed) return;
			window.addAdminHelpersProcessed = true;
			if(!$.fn.on) return;
			if(!$('.contextual-links-region:first').length) return;
			$(window).load(function(){
				loadIfMenuExists();
			});
		}
	};//nihAdminHelpers

})(jQuery);