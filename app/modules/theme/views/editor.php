<?=$this->head_assets->javascript('js/theme_editor.js');?>
<?=$this->head_assets->stylesheet('css/theme_editor.css');?>

<?=$this->load->view(branded_view('cp/header'));?>
<div class="sidebar" style="width: 30%">
	<h2 style="margin-bottom: 0">Theme Files <?=$theme;?>: <?=form_dropdown('theme_directory', $themes, $theme);?></h2>
	<div id="file_list_options">
		<input class="button tooltip" type="button" id="new_file" name="" value="&#43; New File" title="create a new file in this theme folder" />
		<? //<input class="button tooltip" type="button" id="export_theme" name="" value="Export Theme" title="export all theme files as a ZIP file" /> ?>
		<input class="button tooltip" type="button" id="set_frontpage" name="" value="Set Frontpage" title="set the default template loaded as your site's homepage" />
	</div>
	<div class="sidebar_content">
		<div id="set_frontpage_help">
			Find and select the template below that you would like to load as your site's frontpage!
		</div>
		<div id="file_list">
			
		</div>
	</div>
</div>

<h1>Theme Editor</h1>

<div style="float: left; width: 65%;">
	<div id="empty_editor">Select a file on the left to begin editing your theme.</div>
	<div id="theme_editor">
		<div id="editor_head">
			<div id="filename">
				<input id="current_file" type="input" name="current_file" value="empty.thtml" class="tooltip" title="rename this file" />
				<input id="old_filename" type="hidden" name="old_filename" value="empty.thtml" />
			</div>
			<div id="options">
				<input type="button" class="button" id="save_file" name="" value="Save File" />
				<input type="button" class="button" id="delete_file" name="" value="Delete File" />
				<input type="button" class="button tooltip" id="map_url" name="" value="Map URL to Template" title="map a site URL path (e.g., '/any_url_string') to this template file" />
			</div>
			<div style="clear:both"></div>
		</div>
		<div id="editor_loading">
			<img src="<?=branded_include('images/loading.gif');?>" alt="Loading" />
		</div>
		<div id="not_writable">
		    <p>WARNING: This file or folder is not writable by <?=$this->config->item('app_name');?>.  This means that <b>we cannot save any changes you
		    make to the file in this editor.</b>  Please login to your FTP server and attempt to change the
			file <i>and</i> folder permissions (with CHMOD) to 0755.  If the error doesn't subside, attempt to change the permissions to 0777.  Finally, if that does not work,
			contact your server administrator.</p>
			<p><a id="recheck_writable" href="javascript:void(0)">Click here to re-check your file permissions after modifying them.</a></p>
		</div>
		<div id="success_note">
			Success!  The file is now writable and we'll be able to save it.
		</div>
		<div id="editor_body_div">
			<textarea id="editor_body" name="editor_body" wrap="off"></textarea>
		</div>
	</div>
</div>
<div style="clear:both"></div>

<div class="modal" id="new_file_dialog">
	<h3>Create New File</h3>
	
	<form class="modal form" method="post">
		<ul class="form">
			<li>
				<label>Path</label>
				themes/<span id="path_theme"></span> <select name="new_file_path"></select>
			</li>
			<li>
				<label>Filetype</label>
				<select name="filetype">
					<option value="thtml">Template</option>
					<option value="txml">Template (XML)</option>
					<option value="js">JavaScript</option>
					<option value="css">CSS Stylesheet</option>
					<option value="php">PHP Script</option>
				</select>
			</li>
			<li>
				<label>Filename</label>
				<input type="text" name="new_file_name" class="text required" value="" />
			</li>
		</ul>
		<div class="submit">
			<input type="button" class="button" name="" id="new_file_create" value="Create File" />
		</div>
	</form>
</div>

<div class="modal" id="map_url_dialog" style="height: 350px">
	<h3>Map URL to Template</h3>
	
	<form class="modal form" method="post">
		<ul class="form">
			<li>
				<label>URL Path</label>
				<?=base_url();?><input type="text" name="new_url" class="required text" value="" />
				<div class="help">e.g., "my-template-link"</div>
			</li>
			<li>
				<label>Link Title</label>
				<input type="text" name="new_url_title" class="required text" value="" />
				<div class="help">e.g., "My Custom Template Link".  Required in case this link is used in a menu (can be modified later).</div>
			</li>
		</ul>
		<div class="submit">
			<input type="button" class="button" name="" id="new_url_create" value="Map URL to Template" />
		</div>
	</form>
	<br />
	<h3>Existing Custom URL's mapped to this template:</h3>
	<p><i>Note: This does not include automatic mappings by <?=$this->config->item('app_name');?> modules.</i></p>
	
	<div id="existing_maps">
	
	</div>
</div>

<?=$this->load->view(branded_view('cp/footer'));?>