/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

	// enable the MediaEmbed plugin
	config.extraPlugins = 'MediaEmbed';

	// get the base URL
	config.base_url = $('#site_url').html();

	config.baseUrl = config.baseHref = config.base_url;

	// integrate KCFinder
	config.filebrowserBrowseUrl = config.base_url + 'branding/default/js/ckeditor/kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = config.base_url + 'branding/default/js/ckeditor/kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = config.base_url + 'branding/default/js/ckeditor/kcfinder/browse.php?type=flash';

    // force plain text
    //config.forcePasteAsPlainText = true;

    // remove upload tab
    config.filebrowserUploadUrl = null;

	config.toolbar_Complete =
	[
	    ['Source','-','ShowBlocks','-',],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
	    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	    '/',
	    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Link','Unlink','Anchor'],
	    ['Image','MediaEmbed','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
	    '/',
	    ['Format','Font','FontSize'],
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['TextColor','BGColor'],
	    ['Maximize']
	];

	config.toolbar_Basic =
	[
	    ['Source','-',],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
	    ['Undo','Redo','-','RemoveFormat'],
	    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Image','MediaEmbed','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
	    '/',
	    ['Format','Font','FontSize'],
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['TextColor','BGColor'],
   	    ['Link','Unlink','Anchor'],
	    ['Maximize']
	];
};
