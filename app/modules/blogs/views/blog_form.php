<?php

// default values
if (!isset($blog)) {
	$blog = array(
				'title' => '',
				'url_path' => '',
				'description' => '',
				'type' => FALSE,
				'filter_authors' => array(0),
				'filter_topics' => array(0),
				'summary_field' => '',
				'sort_field' => '',
				'sort_dir' => 'ASC',
				'auto_trim' => TRUE
			);
}

?>

<?=$this->head_assets->javascript('js/form.blog.js');?>

<?=$this->load->view(branded_view('cp/header'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_blog" method="post" action="<?=$form_action;?>">
<fieldset>
	<legend>Blog Details</legend>
	<ul class="form">
		<li>
			<label class="full" for="title">Blog Title</label>
		</li>
		<li>
			<input type="text" class="required full text" id="title" name="title" value="<?=$blog['title'];?>" />
		</li>
		<li>
			<label for="url_path">URL Path</label>
			<input type="text" class="text mark_empty" id="url_path" rel="e.g, my_blog" style="width:500px" name="url_path" value="<?=$blog['url_path'];?>" />
		</li>
		<li>
			<div class="help">If you leave this blank, it will be auto-generated from the Title above.</div>
		</li>
		<li>
			<label class="full" for="description">Blog Description</label>
		</li>
		<li>
			<textarea class="text full wysiwyg complete" id="description" name="description"><?=$blog['description'];?></textarea>
		</li>
		<li>
			<div class="help" style="margin:0">(Optional) Your site may use this description at the top or side of your blog as a summary of its purpose, history, your background, etc.</div>
		</li>
	</ul>
</fieldset>

<?=$privilege_form;?>

<fieldset>
	<legend>Filters</legend>
	<ul class="form">
		<li>
			<label for="type">Content Type</label>
			<?=form_dropdown('type',$types,$blog['type'],'id="content_type"');?>
		</li>
		<li>
			<label for="authors">Author(s)</label>
			<?=form_multiselect('authors[]',$users,$blog['filter_authors']);?>
		</li>
		<li>
			<label for="topics">Topic(s)</label>
			<?=form_multiselect('topics[]',$topics,$blog['filter_topics']);?>
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend>Options</legend>
	<ul class="form">
		<li>
			<label for="summary_field">Summary Field</label>
			<? if (isset($field_options)) { ?>
				<? // we are editing and must have a field_options array which we can select from ?>
				<?=form_dropdown('summary_field',$field_options,$blog['summary_field'],'id="summary_field" class="populate_fields editing"');?>
			<? } else { ?>
				<?=form_dropdown('summary_field',array('' => 'Loading...'),'','id="summary_field" class="populate_fields"');?>
			<? } ?>
		</li>
		<li>
			<div class="help">This field populates the summary displayed on the main blog listing page.  It can be a dedicated "Summary" field or the full text of your article (i.e., a "Body" field).</div>
		</li>
		<li>
			<label>&nbsp;</label><?=form_checkbox('auto_trim','1',($blog['auto_trim'] == TRUE) ? TRUE : FALSE);?> <b>Automatically shorten the summary to <?=setting('blog_summary_length');?> characters?</b>  This setting is customizable under Configuration > Settings.
		</li>
		
		<li>
			<label for="sort_field">Sort by</label>
			<? if (isset($field_options)) { ?>
			<? reset($field_options); ?>
				<? // we are editing and must have a field_options array which we can select from ?>
				<?=form_dropdown('sort_field',$field_options,$blog['sort_field'],'id="sort_field" class="populate_fields editing"');?>
			<? } else { ?>
				<?=form_dropdown('sort_field',array('' => 'Loading...'),'','id="sort_field" class="populate_fields"');?>
			<? } ?>
			&nbsp;&nbsp;
			<?=form_dropdown('sort_dir',array('ASC' => 'Ascending (First to Last, Oldest to Newest)', 'DESC' => 'Descending (Last to First, Newest to Oldest'), $blog['sort_dir']);?>
		</li>
	</ul>
</fieldset>

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="form_blog" value="Save Blog" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>