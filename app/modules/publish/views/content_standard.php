<?=$this->load->view(branded_view('cp/header'));?>
<h1>Manage <?=$type['name'];?></h1>

<?=$this->dataset->table_head();?>

<?

// generate preview keys
$this->load->library('encrypt');

?>

<?
if (!empty($this->dataset->data)) {
	foreach ($this->dataset->data as $row) {
	?>
		<tr>
			<td><input type="checkbox" name="check_<?=$row['id'];?>" value="1" class="action_items" /></td>
			<td><?=$row['id'];?></td>
			<td><a href="<?=site_url('admincp/publish/edit/' . $row['id']);?>"><?=$row['title'];?></a></td>
			<td><?=$row['author_username'];?></td>
			<td>
			<?php 
				$str = '';
			
				if (!is_array($row['topics']))
				{
					$str = '---';
				}
				else
				{		
					foreach ($row['topics'] as $topic)
					{ 
						if (!is_bool($topic) && array_key_exists($topic, $topics))
						{
							$str .= $topics[$topic] .', ';
						}
					}
				}
				
				echo trim($str, ', ');
			?>
			</td>
			<td><?=$row['date'];?></td>
			<td><?=$row['hits'];?></td>
			<td class="options">
				<a href="<?=site_url('admincp/publish/edit/' . $row['id']);?>">edit</a>
				<? if ($row['is_standard'] == TRUE) { ?>
					<? if (strtotime($row['date']) > time()) { ?>
						<a href="<?=$row['url'];?>?preview=<?=base64_encode($this->encrypt->encode($row['url_path']));?>">preview</a>
					<? } else { ?>
						<a href="<?=$row['url'];?>">view</a>
					<? } ?>
				<? } ?>
			</td>
		</tr>
	<?
	}
}
else {
?>
<tr>
	<td colspan="7">No content available.</td>
</tr>
<? } ?>
<?=$this->dataset->table_close();?>
<?=$this->load->view(branded_view('cp/footer'));?>