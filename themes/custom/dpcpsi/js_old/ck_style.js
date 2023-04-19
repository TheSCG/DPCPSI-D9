/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/*
 * This file is used/requested by the 'Styles' button.
 * The 'Styles' button is not enabled by default in DrupalFull and DrupalFiltered toolbars.
 */
if(typeof(CKEDITOR) !== 'undefined') {
	CKEDITOR.on( 'dialogDefinition', function( ev ) {
      // Dialog name to find the one we want
      // Definition so we can customize it
      var dialogName = ev.data.name;
      var dialogDefinition = ev.data.definition;

      if ( dialogName == 'table' || dialogName == 'tableProperties' ) {
        // alert('boo');
		var $ = jQuery;
		if (!$.fn.uniqueId){ // this is a jquery-ui script, see https://raw.githubusercontent.com/jquery/jquery-ui/master/ui/unique-id.js
			var uuid = 0;
			$.fn.uniqueId = function() {
				return this.each(function() {
					if (!this.id) {
						this.id = "ui-id-" + (++uuid);
					}
				});
			}
		}
		 setTimeout(function(){
			 $('label[for]').filter(function(){
				 return $.trim($(this).text()).toLowerCase() == 'stylesheet classes';
			 }).each(function(){
				 var $input = $('#' + $(this).attr('for')).bind('focus change', function(){
					 $test.attr('class', $(this).val());
					 //console.log('test class: ' + $test.attr('class'));
					 $fs.find('input[type="checkbox"]').each(function(){
						 var theClass = $(this).val();
						 //console.log('checking '  +theClass);
						 this.checked = $test.hasClass(theClass);
					 });
				 });/*.focus(function(){
					 $fs.slideDown();
				 }).blur(function(){
					 $fs.slideUp();
				 }); */
				 var $test = $('<span/>').attr('class', $input.val());
				 var tableClasses = ['table-stack','table-columntoggle','table-swipe','responsive-data-table','tablecloth','minimap','table-data','table-data-tight','table-data-fancy', 'table-data-plain', 'hide-column-headers', 'schedule-table', 'table-data-item-value', 'equal-width-columns-2', 'equal-width-columns-3', 'equal-width-columns-4', 'equal-width-columns-5', 'equal-width-columns-6', 'desktop-auto-width'];
				 
				 var $fs = $('<fieldset class="ck-data-table-styles"><legend>Data Table Styles</legend></fieldset>');
				 var $ul = $('<ul/>');
				 $fs.append($ul);
				 for(var i=0; i<tableClasses.length; i++){
					 var cls = tableClasses[i];
					 var $li = $('<li/>').css({'list-style-type':'none','margin-bottom':'2px'});
					 var $cb = $('<input type="checkbox" />').attr('value',cls).uniqueId().change(function(){
						 var theClass = $(this).val();
						 var checked = $(this).is(':checked');
						 $test.attr('class', $input.val());
						 if(checked) $test.addClass(theClass);
						 else $test.removeClass(theClass);
						 $input.val($test.attr('class'));
					 });
					 var $l = $('<label/>').text(cls).attr('for',$cb.attr('id'));
					 $li.append($cb);
					 $li.append($l);
					 $ul.append($li);
					 
				 }
				 $fs.insertAfter($input);//.hide();
				 //alert($input.val());
				 $input.change();
			 });
		 }, 1000);
		 //table-stack


      }
	}	  );

    CKEDITOR.addStylesSet( 'drupal',
    [
            /* Block Styles */

            // These styles are already available in the "Format" drop-down list, so they are
            // not needed here by default. You may enable them to avoid placing the
            // "Format" drop-down list in the toolbar, maintaining the same features.
            /*
            { name : 'Paragraph'		, element : 'p' },
            { name : 'Heading 1'		, element : 'h1' },
            { name : 'Heading 2'		, element : 'h2' },
            { name : 'Heading 3'		, element : 'h3' },
            { name : 'Heading 4'		, element : 'h4' },
            { name : 'Heading 5'		, element : 'h5' },
            { name : 'Heading 6'		, element : 'h6' },
            { name : 'Preformatted Text', element : 'pre' },
            { name : 'Address'			, element : 'address' },
			
			
			
            */
		
			{ name : 'Collapsed'	, element : 'div',  attributes : {'class' : 'content-collapsible collapsed'} },
			{ name : 'Collapsible'	, element : 'div',  attributes : {'class' : 'content-collapsible'} },
			{ name : 'Inline Read More'	, element : 'div',  attributes : {'class' : 'inline-read-more'} },
			{ name : 'More Label'	, element : 'span',  attributes : {'class' : 'read-more-label'} },
			{ name : 'Less Label'	, element : 'span',  attributes : {'class' : 'read-less-label'} },
			{ name : 'Slideshow'	, element : 'div',  attributes : {'class' : 'slideshow-placeholder'} },
			{ name : 'Slideshow: Program'	, element : 'div',  attributes : {'class' : 'slideshow-placeholder force-optionset-program'} },
			{ name : 'Slideshow: Home Page'	, element : 'div',  attributes : {'class' : 'slideshow-placeholder force-optionset-home-page'} },
			{ name : 'Image'	, element : 'span',  attributes : {'class' : 'image-placeholder'} },
			{ name : 'Image: Auto Width'	, element : 'span',  attributes : {'class' : 'image-placeholder image-placeholder-auto'} },
			{ name : 'Image: Left'	, element : 'span',  attributes : {'class' : 'image-placeholder image-placeholder-left'} },
			{ name : 'Image: Right'	, element : 'span',  attributes : {'class' : 'image-placeholder image-placeholder-right'} },
			{ name : 'Image: Centered Small'	, element : 'span',  attributes : {'class' : 'image-placeholder image-placeholder-centered-small'} },
			{ name : 'Image: Centered Big'	, element : 'span',  attributes : {'class' : 'image-placeholder image-placeholder-centered-big'} },
			
			/*
			
			{ name : 'Table Style 1 TR - TH'	, element : 'tr',  styles : {'background' : '#466d94','color':'#fff' } },
			{ name : 'Table Style 1 TD - ODD'	, element : 'tr',  styles : {'background' : '#ecedf6'} },
			{ name : 'SKY BLUE'	, element : 'tr',  element : 'tr',  styles : {'background' : '#e1f3fd','color':'#000' } },

			{ name : 'Yellow Box'	, element : 'div',  attributes : {'class' : 'yellow_box'} },
			{ name : 'Yellow Box 1'	, element : 'div',  attributes : {'class' : 'yellow_box_bg'} },
			
			{ name : 'Blue Box'	, element : 'div',  attributes : {'class' : 'program_content_bg'} },
			{ name : 'Blue Box Right'	, element : 'div',  attributes : {'class' : 'program_content_bg_right'} },
			
			{ name : 'Blue Box Big'	, element : 'div',  attributes : {'class' : 'program_big_bg'} },
			
			
			{ name : 'Orange Box'	, element : 'div',  attributes : {'class' : 'program_orange_bg'} },
			
			{ name : 'Green Border Box'	, element : 'div',  attributes : {'class' : 'program_green_border_bg'} },
			
			{ name : 'Grey Box Dark'	, element : 'div',  attributes : {'class' : 'gery_box_dark'} },
			{ name : 'Grey Box Dark Right'	, element : 'div',  attributes : {'class' : 'gery_box_dark right'} },

			{ name : 'Grey Box'	, element : 'div',  attributes : {'class' : 'white_box'} },
			{ name : 'Grey Box Right'	, element : 'div',  attributes : {'class' : 'white_box right'} },

			{ name : 'Light Grey'	, element : 'div',  attributes : {'class' : 'light_grey'} },
			{ name : 'Left Nav'	, element : 'div',  attributes : {'class' : 'sublink'} },

            { name : 'Blue Title'		, element : 'h3', styles : { 'color' : 'Blue' } },
            { name : 'Red Title'		, element : 'h3', styles : { 'color' : 'Red' } },
			
			*/


            /* Inline Styles */

            // These are core styles available as toolbar buttons. You may opt enabling
            // some of them in the "Styles" drop-down list, removing them from the toolbar.
            
            { name : 'Strong'			, element : 'strong', overrides : 'b' },
            { name : 'Emphasis'			, element : 'em'	, overrides : 'i' },
            { name : 'Underline'		, element : 'u' },
            { name : 'Strikethrough'	, element : 'strike' },
            { name : 'Subscript'		, element : 'sub' },
            { name : 'Superscript'		, element : 'sup' },
            

            { name : 'Marker: Yellow'	, element : 'span', styles : { 'background-color' : 'Yellow' } },
            { name : 'Marker: Green'	, element : 'span', styles : { 'background-color' : 'Lime' } },
			
            { name : 'Big'				, element : 'big' },
            { name : 'Small'			, element : 'small' },
            { name : 'Typewriter'		, element : 'tt' },

            { name : 'Computer Code'	, element : 'code' },
            { name : 'Keyboard Phrase'	, element : 'kbd' },
            { name : 'Sample Text'		, element : 'samp' },
            { name : 'Variable'			, element : 'var' },

            { name : 'Deleted Text'		, element : 'del' },
            { name : 'Inserted Text'	, element : 'ins' },

            { name : 'Cited Work'		, element : 'cite' },
            { name : 'Inline Quotation'	, element : 'q' },

            { name : 'Language: RTL'	, element : 'span', attributes : { 'dir' : 'rtl' } },
            { name : 'Language: LTR'	, element : 'span', attributes : { 'dir' : 'ltr' } },

            /* Object Styles */

            {
                    name : 'Image on Left',
                    element : 'img',
                    attributes :
                    {
                            'class' : 'float-left'
                    }
            },

            {
                    name : 'Image on Right',
                    element : 'img',
                    attributes :
                    {
                            'class' : 'float-right'
                    }
            }

    ]);
}