<?php

// default values
if (!isset($event)) {
	$event = array(
				'title' => '',
				'url_path' => '',
				'description' => '',
				'location' => '',
				'max_attendees' => '',
				'price' => '',
				'subscription' => ''
			);
}

?>
<?=$this->load->view(branded_view('cp/header'));?>
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
			<div class="help">If you leave this blank, it will be auto-generated from the Event Name above.</div>
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
	<legend>Options</legend>
	<ul class="form">
		<li>
			<label class="full" for="location">Location</label>
		</li>
		<li>
			<input type="text" class="required full text" id="location" name="location" value="<?=$event['location'];?>" />
		</li>
		<li>
			<label class="full" for="max_attendees">Maximum Attendees</label>
		</li>
		<li>
			<input type="text" class="required full text" id="max_attendees" name="max_attendees" value="<?=$event['max_attendees'];?>" />
		</li>		
		<li>
			<label class="full" for="price">Price</label>
		</li>
		<li>
			<input type="text" class="number text" id="price" name="price" value="<?=$event['price'];?>" />
		</li>
		
		<li>
			<label class="full" for="v">Subscription</label>
		</li>
		<li>
			<input type="text" class="number text" id="subscription" name="subscription" value="<?=$event['subscription'];?>" />
		</li>			
	</ul>
</fieldset>

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="form_event" value="Save Event" />
</div>
</form>
<?=$this->load->view(branded_view('cp/footer'));?>