			</div>
			<div id="box-bottom"></div>
		</div>
	</div>
	<div id="footer">
		Powered by <a href="<?=$this->config->item('app_url');?>"><?=$this->config->item('app_name');?></a> v<?=$this->config->item('app_version');?>. <?
		
			if (defined("_LICENSENUMBER")) {
				echo 'License Number: ' . _LICENSENUMBER;
			}
			
			?>
	</div>
<div class="hidden" id="base_url"><?=site_url('admincp') . '/';?></div>
<div class="hidden" id="site_url"><?=base_url();?></div>
<div class="hidden" id="current_url"><?=current_url();?></div>
<?=$this->load->view(branded_view('cp/html_footer'));?>