<?=$this->load->view(branded_view('cp/header'));?>

<h1>Import Member Results</h1>

<?php if (isset($error_users) && is_array($error_users) && count($error_users)) : ?>

	<?php if ($total_imports) : ?>
		<p><?php echo $total_imports ?> members were successfully imported.</p>
	<?php endif; ?>
	
	<p>The following members were unable to be imported: </p>
	
	<div style="overflow-x: auto">
		<table class="dataset" cellpadding="0" cellspacing="0">
			<?php foreach ($error_users as $user) :?>
			<tr>
				<td style="font-weight: bold; color: red">
				<?
					if ($user['error'] == 'missing_info') {
						echo 'Missing required info';
					}
					elseif ($user['error'] == 'duplicate_username') {
						echo 'Username already taken';
					}
					elseif ($user['error'] == 'duplicate_email') {
						echo 'Email already taken';
					}
					else {
						echo 'General error';
					}
				?>
				</td>
				<?php foreach ($user['data'] as $value) :?>
				<td><?php echo $value ?></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</table>
	</div>

<?php else : ?>

	<p><?php echo $total_imports ?> members were successfully imported.</p>

<?php endif; ?>

<?=$this->load->view(branded_view('cp/footer'));?>