<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Store Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

define('ADDTYPE_UNKNOWN', 0);
define('ADDTYPE_RESIDENTIAL', 1);
define('ADDTYPE_COMMERCIAL', 2);

class Store_module extends Module {
	var $version = '1.44';
	var $name = 'store';

	function __construct () {
		// set the active module
		$this->active_module = $this->name;

		parent::__construct();
	}

	/*
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	function admin_preload ()
	{
		$this->CI->admin_navigation->child_link('storefront',20,'Products',site_url('admincp/store'));
		$this->CI->admin_navigation->child_link('storefront',30,'Collections',site_url('admincp/store/collections'));
		$this->CI->admin_navigation->child_link('storefront',40,'Tax Rules',site_url('admincp/store/taxes'));
		$this->CI->admin_navigation->child_link('storefront',50,'Shipping Rates',site_url('admincp/store/shipping'));
		$this->CI->admin_navigation->child_link('storefront',35,'Product Options',site_url('admincp/store/product_options'));
	}

	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/store/template_plugins/');
	}

	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < 1.0) {
			// initial install
			$this->CI->db->query('CREATE TABLE `order_details` (
								  `order_details_id` int(11) NOT NULL auto_increment,
								  `order_id` int(11) NOT NULL,
								  `customer_id` int(11) NOT NULL,
								  `affiliate` int(11) NOT NULL,
								  `shipping_first_name` varchar(250) default NULL,
								  `shipping_last_name` varchar(250) default NULL,
								  `shipping_company` varchar(250) default NULL,
								  `shipping_address_1` varchar(250) default NULL,
								  `shipping_address_2` varchar(250) default NULL,
								  `shipping_city` varchar(250) default NULL,
								  `shipping_state` varchar(250) default NULL,
								  `shipping_country` varchar(10) default NULL,
								  `shipping_postal_code` varchar(50) default NULL,
								  PRIMARY KEY  (`order_details_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

  			$this->CI->db->query('CREATE TABLE `order_products` (
 								 `order_products_id` int(11) NOT NULL auto_increment,
 								 `order_details_id` int(11) NOT NULL,
 								 `product_id` int(11) NOT NULL,
 								 `order_products_quantity` int(11) NOT NULL,
 								 `order_products_options` text NOT NULL,
 								 `order_products_shipped` tinyint(4) NOT NULL,
 								 PRIMARY KEY  (`order_products_id`)
 								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

 			$this->CI->db->query('CREATE TABLE `download_links` (
								  `download_link_hash` varchar(32) NOT NULL,
								  `download_link_path` varchar(250) NOT NULL,
								  `download_link_downloads` int(2) NOT NULL
								) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		}

		if ($db_version < 1.01) {
			$this->CI->settings_model->new_setting(2, 'weight_unit', 'kg', 'What unit should be used to represent product weight?', 'text', '');
		}

		if ($db_version < 1.07) {
			$this->CI->settings_model->make_writeable_folder(setting('path_product_files'),TRUE);
		}

		if ($db_version < 1.08) {
			$this->CI->settings_model->make_writeable_folder(setting('path_product_images'));
		}

		if ($db_version < 1.09) {
			$this->CI->db->query('DROP TABLE IF EXISTS `products`');

			$this->CI->db->query('CREATE TABLE `products` (
 								 `product_id` int(11) NOT NULL auto_increment,
  								 `product_url_path` varchar(250) NOT NULL,
  								 `product_collections` varchar(255) NOT NULL,
  								 `product_name` varchar(250) NOT NULL,
  								 `product_price` varchar(15) NOT NULL,
  								 `product_weight` int(11) NOT NULL,
  								 `product_track_inventory` tinyint(4) NOT NULL,
  								 `product_inventory` float NOT NULL,
  								 `product_inventory_allow_oversell` tinyint(4) NOT NULL,
  								 `product_sku` varchar(100),
  								 `product_description` text NOT NULL,
  								 `product_taxable` tinyint(4) NOT NULL,
  								 `product_member_tiers` varchar(250) NOT NULL,
  								 `product_requires_shipping` tinyint(1) NOT NULL,
  								 `product_download` tinyint(4) NOT NULL,
  								 `product_download_name` varchar(250),
  								 `product_download_size` int(11),
  								 `product_promotion` int(11),
  								 `product_deleted` tinyint(1),
  								 PRIMARY KEY  (`product_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');

			$this->CI->db->query('CREATE FULLTEXT INDEX `search` ON `products` (`product_name`, `product_description`, `product_price`);');
		}

		if ($db_version < 1.11) {
			$this->CI->db->query('DROP TABLE IF EXISTS `product_images`');

			$this->CI->db->query('CREATE TABLE `product_images` (
 								 `product_image_id` int(11) NOT NULL auto_increment,
 								 `product_id` int(11) NOT NULL,
  								 `product_image_filename` varchar(250) NOT NULL,
  								 `product_image_featured` tinyint(1) NULL,
  								 `product_image_order` int(5) NOT NULL,
  								 `product_image_uploaded` DATETIME,
  								 PRIMARY KEY  (`product_image_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');
		}

		if ($db_version < 1.12) {
			// this has been moved to the publish module, because "store" is no longer a required module
			// $this->CI->settings_model->make_writeable_folder(setting('path_image_thumbs'));
		}

		if ($db_version < 1.13) {
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `collections` (
 								 `collection_id` int(11) NOT NULL auto_increment,
 								 `collection_parent_id` int(11) NOT NULL,
 								 `collection_name` varchar(250) NOT NULL,
  								 `collection_description` text NOT NULL,
  								 `collection_deleted` tinyint(1) NOT NULL,
   								 PRIMARY KEY  (`collection_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');
		}

		if ($db_version < 1.14) {
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `collection_maps` (
 								 `collection_map_id` int(11) NOT NULL auto_increment,
 								 `collection_id` int(11) NOT NULL,
 								 `product_id` int(11) NOT NULL,
   								 PRIMARY KEY  (`collection_map_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}

		if ($db_version < 1.16) {
			$this->CI->db->query('DROP TABLE IF EXISTS `taxes`');

			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `taxes` (
 								 `tax_id` int(11) NOT NULL auto_increment,
 								 `tax_name` varchar(250) NOT NULL,
 								 `tax_percentage` float(5) NOT NULL,
 								 `state_id` int(11) NOT NULL,
 								 `country_id` int(11) NOT NULL,
 								 `tax_deleted` tinyint(1),
   								 PRIMARY KEY  (`tax_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}

		if ($db_version < 1.17) {
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `taxes_received` (
								 `tax_received_id` int(11) NOT NULL auto_increment,
 								 `tax_id` int(11) NOT NULL,
 								 `tax_received_amount` float(8) NOT NULL,
 								 `tax_received_date` DATETIME NOT NULL,
 								 `user_id` int(11) NOT NULL,
 								 `order_details_id` int(11) NOT NULL,
 								 `subscription_id` int(11) NOT NULL,
   								 PRIMARY KEY  (`tax_received_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}

		if ($db_version < 1.18) {
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `shipping` (
								 `shipping_id` int(11) NOT NULL auto_increment,
								 `shipping_name` varchar(250) NOT NULL,
 								 `country_id` int(11) NOT NULL,
 								 `state_id` int(11) NOT NULL,
 								 `shipping_rate_type` varchar(10) NOT NULL,
 								 `shipping_rate` float NOT NULL,
 								 `shipping_deleted` tinyint(1) NOT NULL,
   								 PRIMARY KEY  (`shipping_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}

		if ($db_version < 1.19) {
			$this->CI->settings_model->new_setting(2, 'shipping_default_type', 'flat', 'Should the default shipping charge be by weight unit or a flat fee?', 'text', '', FALSE, TRUE);
			$this->CI->settings_model->new_setting(2, 'shipping_default_rate', '0.00', 'Default shipping rate.', 'text', '', FALSE, TRUE);
			$this->CI->settings_model->new_setting(2, 'shipping_available_countries', '', 'If not empty, only these regions can be shipped to.', 'text', '', FALSE, TRUE);
		}

		if ($db_version < 1.20) {
			$this->CI->db->query('ALTER TABLE `order_products` ADD COLUMN `order_products_price` VARCHAR(15) AFTER `product_id`');
		}

		if ($db_version < 1.21) {
			$this->CI->db->query('ALTER TABLE `taxes_received` ADD COLUMN `tax_received_for_subscription` float(8) AFTER `tax_received_amount`');
		}

		if ($db_version < 1.23) {
			$this->CI->db->query('DROP TABLE `taxes_received`');

			// re-create tax table in new format
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `taxes_received` (
								 `tax_received_id` int(11) NOT NULL auto_increment,
 								 `tax_id` int(11) NOT NULL,
 								 `order_id` int(11) NOT NULL,
 								 `tax_received_for_products` float(8) NOT NULL,
 								 `tax_received_for_subscription` float(8) NOT NULL,
 								 `tax_received_date` DATETIME NOT NULL,
 								 PRIMARY KEY  (`tax_received_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			// add some indexes
			$this->CI->db->query('ALTER TABLE `taxes_received` ADD INDEX `order_id` (`order_id`);');
			$this->CI->db->query('ALTER TABLE `order_details` ADD INDEX `order_id` (`order_id`);');
			$this->CI->db->query('ALTER TABLE `order_products` ADD INDEX `order_details_id` (`order_details_id`);');
		}

		if ($db_version < 1.25) {
			$this->CI->db->query('CREATE TABLE IF NOT EXISTS `future_sub_tax` (
								 `future_sub_tax_id` int(11) NOT NULL auto_increment,
 								 `tax_id` int(11) NOT NULL,
 								 `subscription_id` int(11) NOT NULL,
 								 `tax_amount` float(8) NOT NULL,
 								 PRIMARY KEY  (`future_sub_tax_id`)
								 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
		}

		if ($db_version < 1.26) {
			// clear the tables from testing
			$this->CI->db->query('TRUNCATE TABLE `subscriptions`');
			$this->CI->db->query('TRUNCATE TABLE `orders');
			$this->CI->db->query('TRUNCATE TABLE `taxes_received`');
			$this->CI->db->query('TRUNCATE TABLE `future_sub_tax`');
			$this->CI->db->query('TRUNCATE TABLE `order_details`');
			$this->CI->db->query('TRUNCATE TABLE `order_products`');
			$this->CI->db->query('TRUNCATE TABLE `order_data`');
			$this->CI->db->query('TRUNCATE TABLE `order_authorizations`');
		}

		if ($db_version < 1.30) {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->register('store_order','An order is made from the store (includes at least one product).',array('member','invoice','order'));
			$this->CI->app_hooks->register('store_order_product','A product is ordered from the store (hook fires for each product).',array('member','invoice','product'));
			$this->CI->app_hooks->register('store_order_product_downloadable','A downloadable product is ordered from the store (hook for each downloadable product).',array('member','invoice','product'),array('download_link'));
		}

		if ($db_version < 1.31) {
			$this->CI->db->query('CREATE TABLE `product_options` (
									  `product_option_id` int(11) NOT NULL auto_increment,
									  `product_option_share` tinyint(1) default NULL,
									  `product_option_name` varchar(250) default NULL,
									  `product_option_options` text,
									  PRIMARY KEY  (`product_option_id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

			$this->CI->db->query('ALTER TABLE `products` ADD COLUMN `product_options` VARCHAR(255) AFTER `product_promotion`');

			$this->CI->db->query('ALTER TABLE `order_details` ADD COLUMN `coupon_id` INT(11) AFTER `affiliate`');
			$this->CI->db->query('ALTER TABLE `order_details` ADD INDEX `coupon_id` (`coupon_id`);');
		}

		if ($db_version < 1.32) {
			$this->CI->db->query('ALTER TABLE `shipping` ADD COLUMN `shipping_is_taxable` TINYINT(1) AFTER `country_id`');
		}

		if ($db_version < 1.33) {
			$this->CI->db->query('CREATE TABLE `shipping_received` (
									  `shipping_received_id` int(11) NOT NULL auto_increment,
									  `order_id` int(11) NOT NULL,
									  `shipping_id` int(11) NOT NULL,
									  `shipping_received_amount` float NOT NULL,
									  PRIMARY KEY  (`shipping_received_id`)
									) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		}

		if ($db_version < 1.34) {
			$this->CI->db->query('ALTER TABLE `shipping` ADD COLUMN `shipping_max_weight` FLOAT AFTER `shipping_is_taxable`');
		}

		if ($db_version < 1.35) {
			$this->CI->db->query('ALTER TABLE `products` MODIFY COLUMN `product_weight` FLOAT');
		}

		if ($db_version < 1.36) {
			// moved to billing module, where the `orders` table is managed
			// $this->CI->db->query('ALTER TABLE `orders` ADD INDEX `customer_id` (`customer_id`)');
			// $this->CI->db->query('ALTER TABLE `orders` ADD INDEX `timestamp` (`timestamp`)');
		}

		// Installs new front_items_count to the settings
		if ($db_version < 1.38) {
			$this->CI->settings_model->new_setting(4, 'front_items_count', 10, 'How many items would you like to show for blogs, product collections, etc.');
		}

		if ($db_version < 1.39) {
			$this->CI->db->query('ALTER TABLE `products` MODIFY COLUMN `product_options` TEXT');
		}

		if ($db_version < 1.40) {
			// Clean up any products in the product collection_maps table that have been deleted
			// so that our pagination does not report false information.
			$this->CI->db->query('DELETE FROM `collection_maps`
									USING collection_maps
									LEFT JOIN `products` ON collection_maps.product_id = products.product_id
									WHERE products.product_deleted = 1');
		}

		if ($db_version < 1.41) {
			$this->CI->db->query('ALTER TABLE `order_details` ADD COLUMN `shipping_phone_number` VARCHAR(50) AFTER `shipping_postal_code`');
		}

		if ($db_version < 1.42)
		{
			$this->CI->db->query('ALTER TABLE `order_details` ADD COLUMN `module` VARCHAR(255) NULL');
		}

		if ($db_version < 1.43)
		{
			$this->CI->db->query('ALTER TABLE `order_details` ADD COLUMN `address_type` TINYINT(1) NOT NULL DEFAULT 0');
		}
		
		if ($db_version < 1.44)
		{
			$this->CI->db->query('ALTER TABLE `shipping_received` ADD COLUMN `shipping_desc` VARCHAR(250) NOT NULL DEFAULT 0');
		}

		// return current version
		return $this->version;
	}

	/**
	* Uninstall
	*
	* @return boolean
	*/
	function uninstall () {
		$delete_tables = array(
								'order_details',
								'order_products',
								'download_links',
								'products',
								'product_images',
								'collections',
								'collection_maps',
								'taxes',
								'taxes_received',
								'shipping',
								'future_sub_tax',
								'product_options',
								'shipping_received'
							);

		$delete_hooks = array(
								'store_order',
								'store_order_product',
								'store_order_product_downloadable'
							);

		$delete_settings = array(
								'weight_unit',
								'shipping_default_type',
								'shipping_default_rate',
								'shipping_available_countries'
							);

		if (!empty($delete_tables)) {
			foreach ($delete_tables as $table) {
				$this->CI->db->query('DROP TABLE IF EXISTS `' . $table . '`');
			}
		}

		if (!empty($delete_hooks)) {
			foreach ($delete_hooks as $hook) {
				$this->CI->db->query('DELETE FROM `hooks` WHERE `hook_name`=\'' . $hook . '\'');
			}
		}

		if (!empty($delete_settings)) {
			foreach ($delete_settings as $setting) {
				$this->CI->db->query('DELETE FROM `settings` WHERE `setting_name`=\'' . $setting . '\'');
			}
		}

		return TRUE;
	}
}