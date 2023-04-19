(function($) {
	$(document).ajaxComplete(function(){
		$('.field-type-image a.ctools-modal-media-file-edit').removeClass('ctools-use-modal ctools-modal-media-file-edit').addClass('jquery-use-modal').unbind('click').click(function(e){
		  
		  var $editButton = $(this);
		  var href = $editButton.attr('href');
		  var changed = false;
		  
		  if (href){
			  var fid = $editButton.siblings('.fid').val();
			  var iframeSRC = '/file/' + fid + '/edit?context=iframe';
			  var $dialog = $('<div class="jquery-ctools-dialog-replacement"><iframe src="' + iframeSRC + '" seamless style="width: 98%; height: 95%;" /></div>');
			  var $form = null;
			  $dialog.dialog({
				title: 'Edit image', 
				modal:true, 
				width: $(window).width() * .9, 
				height: $(window).height() * .9,
				buttons: {
					'Save': function(){
						if($form) $form.submit();
					},
					'Cancel': function(){
						$dialog.dialog('close');
					}
				},
				close: function(){
					if(changed){
					
						var $imageDisplay = $editButton.closest('.form-type-media');
						$imageDisplay.css('cursor', 'wait').css('opacity', .5);
						$.ajax({
							url: iframeSRC,
							success: function(data){
								$updatedImageForm = $(data).find('form.file-image-form');
								var $fileName = $updatedImageForm.find('.form-item-filename');
								var $img = $fileName.siblings('img');
								$imageDisplay.find('.preview .media-item').attr('title', $img.attr('title'));
								$imageDisplay.find('.media-thumbnail img').replaceWith($img);
								$imageDisplay.find('.media-thumbnail .media-filename').text($updatedImageForm.find('#edit-filename').val());
								$imageDisplay.css('cursor', '').css('opacity', '');
								changed = false;
							}
						});
					}
					$dialog.dialog('destroy');
					$dialog.remove();
				
				}
			});
			  $iframe = $dialog.find('iframe');
			  $iframe.load(function(){
				  var $uiDialog = $dialog.closest('.ui-dialog');
				  if ($form && $uiDialog.hasClass('submitting')){
					  changed = true;
					  
					  $uiDialog.removeClass('submitting');
					  $iframe.contents().find('body').addClass('context-iframe');
					  if ($iframe[0].contentWindow.location.href != iframeSRC) $dialog.dialog('close');
				  }
				  else {
					  $form = $iframe.contents().find('form.file-image-form');
					  $form.prepend(' <p class="warning"><strong>Important:</strong> Any edits made in this form will <strong><em>immediately update all content</em></strong> where this image is used after clicking "Save".  For instance, if choose a new image under "Replace file", that new image will be shown everywhere this image is referenced.  If you want to update the image only for this field, click <b>Cancel</b> to close this dialog, click the <b>Remove</b> button by the image field, then click <b>Browse</b> to upload or select a different image.</p>');
				  }
				  $form.bind('submit', function(e){
					  //alert('preventing form submission!');
					  $uiDialog.addClass('submitting');
					  //e.preventDefault();
					  //return false;
				  });
				  
			  });
			  e.preventDefault();
			  return false;
			  
			  
		  }
		});
	});
	$(window).bind('resize orientationchange', function(){
		$('.jquery-ctools-dialog-replacement').dialog('option', 'width', $(window).width() * .9).dialog('option', 'height', $(window).height() * .9).dialog('option', 'position', 'center');
	});
})(jQuery);