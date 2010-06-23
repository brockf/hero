<?=$this->load->view(branded_view('cp/header'),array('head_files' => '<script type="text/javascript" src="' . branded_include('js/jquery-ui-1.8.2.min.js') . '"></script>
<script type="text/javascript" src="' . branded_include('js/sortable.js') . '"></script>'));?>
<h1>Arrange Data Fields</h1>
<form rel="<?=$field_group_id;?>" class="form arrange validate" enctype="multipart/form-data" id="form_arrange" method="post" action="<?=site_url('admincp/custom_fields/save/' . $return_url);?>">

<?=$form;?>

<div class="submit">
	<input type="submit" class="button" name="go_save" value="Save Arrangement &amp; Return " />
</div>

</form>
<?=$this->load->view(branded_view('cp/footer'));?>