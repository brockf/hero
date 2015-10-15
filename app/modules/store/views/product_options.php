<?php echo $this->load->view(branded_view('cp/header')); ?>
<h1>Manage Product Options</h1>

<?php echo $this->dataset->table_head(); ?>

<?php if (!empty($this->dataset->data)) : ?>
	<?php foreach ($this->dataset->data as $row) :?>
	<tr>
		<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
		<td><?=$row['id'];?></td>
		<td><a href="<?=$row['admin_link'];?>"><?=$row['name'];?></a></td>
		<td>
		<?php 
			$use_comma = false;
			foreach ($row['options'] as $option) {
				echo $use_comma ? ', ' : '';
				echo $option['label'];
				
				$use_comma = true;
			}
		?>
		</td>
		<td class="options"><a href="<?=$row['admin_link'];?>">edit</a></td>
	</tr>
	<?php endforeach; ?>
<?php else: ?>
	<tr>
		<td colspan="9">No product options match your filters.</td>
	</tr>
<?php endif; ?>

<?php echo $this->dataset->table_close(); ?>

<?php echo $this->load->view(branded_view('cp/footer')); ?>