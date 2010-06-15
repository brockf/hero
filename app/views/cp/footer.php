			</div>
			<div id="box-bottom"></div>
		</div>
	</div>
	<div id="footer">
		Powered by <a href="<?=$this->config->item('app_url');?>"><?=$this->config->item('app_name');?></a> v<?=$this->config->item('app_version');?>.  Copyright &copy; 2007-<?=date('Y');?>, Electric Function, Inc. <?
		
			if (defined("_LICENSENUMBER")) {
				echo 'License Number: ' . _LICENSENUMBER;
			}
			
			?>
	</div>
<div class="hidden" id="base_url"><?=site_url('admincp') . '/';?></div>
<div class="hidden" id="current_url"><?=current_url();?></div>
</body>
</html>