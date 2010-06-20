<?=$this->load->view(branded_view('cp/header'));?>
<h1>Profile: <?=$user['username'];?></h1>
<h2 class="cat user">Member Data</h2>
<ul class="data">
	<li><span class="tag">Username</span> <?=$user['username'];?></li>
	<li><span class="tag">Full Name</span> <?=$user['last_name'];?>, <?=$user['first_name'];?></li>
	<li><span class="tag">Email</span> <?=$user['email'];?></li>
	<? if (is_array($custom_fields)) { foreach ($custom_fields as $field) { ?>
		<li><span class="tag"><?=$field['friendly_name'];?></span> <?=$user[$field['name']];?></li>
	<? } } ?>
	<li><span class="tag">&nbsp;</span> <a href="<?=site_url('admincp/users/edit/' . $user['id']);?>">edit profile</a></li>
</ul>

<h2 class="cat user">Subscriptions</h2>
<Br />
	<table class="dataset" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<td>ID #</td>
				<td>Plan Name</td>
				<td>Recurring Charge</td>
				<td>Next Charge Date</td>
				<td>Start Date</td>
				<td>End Date</td>
				<td>Status</td>
				<td>Options</td>
			</tr>
		</thead>
		<tbody>
<? if (is_array($subscriptions)) { ?>
	<? foreach($subscriptions as $subscription) { ?>
		<tr>
			<td><?=$subscription['id'];?></td>
			<td><?=$subscription['plan']['name'];?></td>
			<td><?=setting('currency_symbol');?><?=$subscription['amount'];?></td>
			<td><?=$subscription['next_charge_date'];?></td>
			<td><?=$subscription['start_date'];?></td>
			<td><?=$subscription['end_date'];?></td>
			<td><?=$subscription['status'];?></td>
			<td>cancel | change plan | change price</td>
		</tr>
	<? } ?>
<? } else { ?>
	<tr>
		<td colspan="8">This member does not have any active or expired subscriptions.</td>
	</tr>
<? } ?>
	</tbody>
</table>
<?=$this->load->view(branded_view('cp/footer'));?>