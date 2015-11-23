<?= $this->load->view(branded_view('cp/header')); ?>

<h1>Manage Coupons</h1>

<?=$this->dataset->table_head();?>

<?php if (!empty($this->dataset->data)) : ?>
	<?php foreach ($this->dataset->data as $row) :?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/coupons/edit/' . $row['id']);?>"><?=$row['name'];?></a></td>
			<td><?= $row['code'] ?></td>
			<td><?= date('Y-m-d', strtotime($row['start_date'])) .' - '. date('Y-m-d', strtotime($row['end_date']));?></td>
			<td><?= $coupon_options[$row['type_id']] ?></td>
		</tr>
	<?php endforeach; ?>
<?php else : ?>
	<tr>
		<td colspan="6">No coupons match your filters.</td>
	</tr>
<?php endif; ?>

<?=$this->dataset->table_close();?>
<?= $this->load->view(branded_view('cp/footer')); ?>