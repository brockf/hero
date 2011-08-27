<?=$this->head_assets->javascript('js/dashboard.js');?>
<?=$this->head_assets->javascript('js/jquery.sparkline.js');?>
<?=$this->head_assets->stylesheet('css/dashboard.css');?>

<?=$this->load->view(branded_view('cp/header.php'));?>

<? if (module_installed('billing','store','coupons')) { ?>
	<div id="dash_stats" rel="day">
		<div id="date_selector">
			<ul>
				<li><input type="radio" name="date_select" value="day" /> <span>Today</span></li>
				<li><input type="radio" name="date_select" value="week" /> <span>This Week</span></li>
				<li><input type="radio" name="date_select" value="month" /> <span>This Month</span></li>
			</ul>
		</div>
		<div class="stat">
			<div class="wrap">
			<span class="stat day"><?=setting('currency_symbol');?><?=$day['revenue'];?></span>
			<span class="stat week"><?=setting('currency_symbol');?><?=$week['revenue'];?></span>
			<span class="stat month"><?=setting('currency_symbol');?><?=$month['revenue'];?></span>
			<? if (!empty($week_by_day['revenue'])) { ?>
				<span class="chart week">
					<? foreach ($week_by_day['revenue'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			
			<? } ?>
			<? if (!empty($month_by_day['revenue'])) { ?>
				<span class="chart month">
					<? foreach ($month_by_day['revenue'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			<? } ?>
			<span class="title">
				Revenue
			</span>
			<a class="more" href="<?=site_url('admincp/reports/invoices');?>">
				View all invoices &gt;
			</a>
			</div>
		</div>
		<div class="stat">
			<div class="wrap">
			<span class="stat day"><?=$day['orders'];?></span>
			<span class="stat week"><?=$week['orders'];?></span>
			<span class="stat month"><?=$month['orders'];?></span>
			<? if (!empty($week_by_day['orders'])) { ?>
				<span class="chart week">
					<? foreach ($week_by_day['orders'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			
			<? } ?>
			<? if (!empty($month_by_day['orders'])) { ?>
				<span class="chart month">
					<? foreach ($month_by_day['orders'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			<? } ?>
			<span class="title">
				Product Orders
			</span>
			<a class="more" href="<?=site_url('admincp/reports/products');?>">
				View all orders &gt;
			</a>
			</div>
		</div>
		<div class="stat">
			<div class="wrap">
			<span class="stat day"><?=$day['subscriptions'];?></span>
			<span class="stat week"><?=$week['subscriptions'];?></span>
			<span class="stat month"><?=$month['subscriptions'];?></span>
			<? if (!empty($week_by_day['subscriptions'])) { ?>
				<span class="chart week">
					<? foreach ($week_by_day['subscriptions'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			
			<? } ?>
			<? if (!empty($month_by_day['subscriptions'])) { ?>
				<span class="chart month">
					<? foreach ($month_by_day['subscriptions'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			<? } ?>
			<span class="title">
				New Subscriptions
			</span>
			<a class="more" href="<?=site_url('admincp/reports/subscriptions');?>">
				View all subscriptions &gt;
			</a>
			</div>
		</div>
		<div class="stat">
			<div class="wrap">
			<span class="stat day"><?=$day['registrations'];?></span>
			<span class="stat week"><?=$week['registrations'];?></span>
			<span class="stat month"><?=$month['registrations'];?></span>
			<? if (!empty($week_by_day['registrations'])) { ?>
				<span class="chart week">
					<? foreach ($week_by_day['registrations'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			
			<? } ?>
			<? if (!empty($month_by_day['registrations'])) { ?>
				<span class="chart month">
					<? foreach ($month_by_day['registrations'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			<? } ?>
			<span class="title">
				New Members
			</span>
			<a class="more" href="<?=site_url('admincp/users');?>">
				View all members &gt;
			</a>
			</div>
		</div>
		<div class="stat">
			<div class="wrap">
			<span class="stat day"><?=$day['logins'];?></span>
			<span class="stat week"><?=$week['logins'];?></span>
			<span class="stat month"><?=$month['logins'];?></span>
			<? if (!empty($week_by_day['logins'])) { ?>
				<span class="chart week">
					<? foreach ($week_by_day['logins'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			
			<? } ?>
			<? if (!empty($month_by_day['logins'])) { ?>
				<span class="chart month">
					<? foreach ($month_by_day['logins'] as $point) { ?><?=$point;?>,<? } ?>null
				</span>
			<? } ?>
			<span class="title">
				Member Logins
			</span>
			<a class="more" href="<?=site_url('admincp/users/logins');?>">
				View all logins &gt;
			</a>
			</div>
		</div>
		<div style="clear:both"></div>
	</div>
<? } ?>

<div id="dash_left">
	
	<div id="site_activity">
		<div id="update_status">
			Last update: 7:15pm
		</div>
		<h2>Live Site Activity</h2>
		<ul id="activity_list">
		</ul>
	</div>
	
</div>

<div id="dash_right">
	
	<div class="quick_publish">
		<ul>
			<? if (empty($quick_publish)) { ?>
				<li><a href="<?=site_url('admincp/publish/types');?>">Manage Content Types</a></li>
			<? } else { ?>
				<? foreach ($quick_publish as $type) { ?>
				<li><a href="<?=$type['link'];?>">&#43; New <?=$type['name'];?></a></li>
				<? } ?>
			<? } ?>
		</ul>
		<a class="more" href="<?=site_url('admincp/publish/create');?>">publish other content types &gt;</a>
	</div>
	
	<div class="dash_box system">
		<h3>System Stats</h3>
		<div class="contents">	
			<table style="width: 100%" class="system">
				<? foreach ($system as $name => $stat) { ?>
				<tr><td style="width: 40%"><strong><?=$name;?></strong></td><td style="width:60%"><?=$stat;?></td></tr>
				<? } ?>
			</table>
		</div>
	</div>
	
	<div class="dash_box app_details">
		<h3><?=$this->config->item('app_name');?></h3>
		<div class="contents">
			<ul>
				<li><a href="<?=$this->config->item('app_link');?>"><?=$this->config->item('app_name');?></a></li>
				<li><a href="<?=$this->config->item('app_support');?>">Support &amp; Documentation</a></li>
			</ul>
		</div>
	</div>
	
</div>

<div style="clear:both"></div>

<?=$this->load->view(branded_view('cp/footer.php'));?>