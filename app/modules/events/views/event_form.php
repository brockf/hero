<?php

// default values
if (!isset($event)) {
	$event = array(
				'title' => '',
				'url_path' => '',
				'description' => '',
				'type' => FALSE,
				'filter_authors' => array(0),
				'filter_topics' => array(0),
				'summary_field' => '',
				'sort_field' => '',
				'sort_dir' => 'ASC'
			);
}

?>
<?=$this->load->view(branded_view('cp/header'), array('head_files' => '<script type="text/javascript" src="' . branded_include('js/form.rss_feed.js') . '"></script>'));?>
<h1><?=$form_title;?></h1>
<form class="form validate" id="form_rss" method="post" action="<?=$form_action;?>">
<fieldset>
	<legend>Event Details</legend>
	<ul class="form">
		<li>
			<label class="full" for="title">Event Name</label>
		</li>
		<li>
			<input type="text" class="required full text" id="title" name="title" value="<?=$event['title'];?>" />
		</li>
		<li>
			<label for="url_path">URL Path</label>
			<input type="text" class="text mark_empty" id="url_path" rel="e.g, events/my_event" style="width:500px" name="url_path" value="<?=$event['url_path'];?>" />
		</li>
		<li>
			<div class="help">If you leave this blank, it will be auto-generated from the Feed Name above.</div>
		</li>
		<li>
			<label class="full" for="description">Event Description</label>
		</li>
		<li>
			<input type="text" class="required full text" id="description" name="description" value="<?=$event['description'];?>" />
		</li>
	</ul>
</fieldset>
<fieldset>
	<legend>Filters</legend>
	<ul class="form">
		<li>
			<label for="type">Content Type</label>
			<?=form_dropdown('type',$types,$event['type'],'id="content_type"');?>
		</li>
		<li>
			<label for="authors">Author(s)</label>
			<?=form_multiselect('authors[]',$users,$event['filter_authors']);?>
		</li>
		<li>
			<label for="topics">Topic(s)</label>
			<?=form_multiselect('topics[]',$topics,$event['filter_topics']);?>
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
				<?=form_dropdown('summary_field',$field_options,$event['summary_field'],'id="summary_field" class="editing populate_fields"');?>
			<? } else { ?>
				<?=form_dropdown('summary_field',array('' => 'Loading...'),'','id="summary_field" class="populate_fields"');?>
			<? } ?>
		</li>
		<li>
			<div class="help">This field populates each RSS feed item's summary.  It helps give users an idea of what the content is about.  All text will automatically be shortened to an appropriate length.</div>
		</li>
		
		<li>
			<label for="sort_field">Sort by</label>
			<? if (isset($field_options)) { ?>
			<? reset($field_options); ?>
				<? // we are editing and must have a field_options array which we can select from ?>
				<?=form_dropdown('sort_field',$field_options,$event['sort_field'],'id="sort_field" class="populate_fields editing"');?>
			<? } else { ?>
				<?=form_dropdown('sort_field',array('' => 'Loading...'),'','id="sort_field" class="populate_fields"');?>
			<? } ?>
			&nbsp;&nbsp;
			<?=form_dropdown('sort_dir',array('ASC' => 'Ascending (First to Last, Oldest to Newest)', 'DESC' => 'Descending (Last to First, Newest to Oldest'), $event['sort_dir']);?>
		</li>
	</ul>
</fieldset>

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="form_rss" value="Save RSS Feed" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>