/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	config.extraPlugins = 'font,panelbutton,colorbutton,justify,menubutton,scayt';
	config.scayt_autoStartup = true;

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
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
	
	config.width = 800;
	
	// get the base URL
	config.base_url = $('#site_url').html();

	config.baseUrl = config.baseHref = config.base_url;

	// integrate KCFinder
	config.filebrowserBrowseUrl = config.baseUrl + 'branding/default/js/ckeditor/kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = config.baseUrl + 'branding/default/js/ckeditor/kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = config.baseUrl + 'branding/default/js/ckeditor/kcfinder/browse.php?type=flash';

    // force plain text
    //config.forcePasteAsPlainText = true;
    
    // remove upload tab
    config.filebrowserUploadUrl = null;
};