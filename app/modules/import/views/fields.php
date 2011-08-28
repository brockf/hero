<?=$this->load->view(branded_view('cp/header'));?>

<h1>Match Columns</h1>

<p>Your list has been uploaded. Now match up the columns in your uploaded list to <?=$this->config->item('app_name');?>'s user fields.</p>

<p>Every import MUST supply email address, first name and last name.</p>

<p><b>NOTE: Unmatched fields will NOT be imported.</b></p>

<?php if (isset($csv_data) && is_array($csv_data) && count($csv_data)) : ?>

<form class="form" action="<?php echo site_url('admincp/import/do_import') ?>" method="post">

<div style="overflow-x: auto">
	<table class="dataset" cellpadding="0" cellspacing="0">
		<thead>
		<tr>
		<?php $count = count( explode(',', $csv_data[0]) );	?>
		<?php for ($i=0; $i < $count; $i++) :?>
			<td>
				<select name="db_field[]">
					<option value="">-----</option>
				<?php foreach ($fields as $name => $display_name) : ?>
					<option value="<?php echo $name ?>"><?php echo $display_name ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		<?php endfor; ?>
		</tr>
		</thead>
		
		<?php $count = 0; ?>
		<?php foreach ($csv_data as $row) : ?>
			<?php 
				$csv_fields = explode(',', $row);
			?>
			<?php if ($count == 1) { $count = 2; } else { $count = 1; } ?>
			
			<tr <?php echo $count == 1 ? 'class="odd"' : ''; ?>>
			<?php foreach ($csv_fields as $field) : ?>
				<td><?php echo str_replace('"', '', $field) ?></td>
			<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
</div>

<div class="submit">
	<br/>
	<input type="submit" class="button" name="submit" value="Import Members" />
</div>

</form>

<?php endif; ?>


<?=$this->load->view(branded_view('cp/footer'));?>