<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Coupons Module
 *
 * This module provides coupons for money-saving or free subscriptions,
 * to be applied at checkout.
 *
 * @author	Electric Function, Inc.
 * @copyright Electric Function, Inc.
 * @package Hero Framework
 */
class Coupons extends Module {
	
	var $version	= '1.06';
	var $name		= 'coupons';
	
	public function __construct() 
	{
		$this->active_module = $this->name;
		parent::__construct();
	}
	
	/**
	* Pre-admin function
	*
	* Initiate navigation in control panel
	*/
	public function admin_preload() 
	{
		$this->CI->admin_navigation->child_link('storefront', 31, 'Coupons', site_url('admincp/coupons/'));
	}
	
	/**
	* Module update
	*
	* @param	int	$db_version The current DB version
	* @return	int	The current software version, to update the database
	*/
	public function update($db_version) 
	{
		if ($db_version < 1.0) {
			// Initial install
			
			// Base Coupons
			$this->CI->db->query("CREATE TABLE  `coupons` (
				`coupon_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`coupon_type_id` INT( 11 ) UNSIGNED NOT NULL ,
				`coupon_name` VARCHAR(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				`coupon_code` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
				`coupon_start_date` DATE NOT NULL ,
				`coupon_end_date` DATE NOT NULL ,
				`coupon_max_uses` INT( 11 ) UNSIGNED NOT NULL ,
				`coupon_customer_limit` INT( 11 ) UNSIGNED NOT NULL ,
				`coupon_reduction_type` TINYINT( 1 ) UNSIGNED NULL COMMENT  '0=%, 1=fixed amount',
				`coupon_reduction_amt` INT( 9 ) UNSIGNED NULL ,
				`coupon_trial_length` INT( 4 ) UNSIGNED NULL COMMENT  'in days',
				`coupon_min_cart_amt` INT( 9 ) UNSIGNED NULL COMMENT  'in cents',
				`coupon_deleted` TINYINT( 1 ) UNSIGNED NOT NULL,
				`created_on` DATETIME NOT NULL ,
				`modified_on` DATETIME NULL
				) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
				
			// Coupon Types
			$this->CI->db->query('CREATE TABLE `coupon_types` (
				`coupon_type_id` INT( 3 ) NOT NULL ,
				`coupon_type_name` VARCHAR( 255 ) NOT NULL ,
				INDEX (  `coupon_type_id` )
				) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;');
				
			$this->CI->db->query("INSERT INTO  `coupon_types` (`coupon_type_id`, `coupon_type_name`) VALUES ('1',  'Price Reduction'), ('2',  'Free Trial'), ('3', 'Free Shipping');");
			
			// Coupon/Product Relationships
			$this->CI->db->query("CREATE TABLE  `coupons_products` (
				`coupon_id` INT( 11 ) UNSIGNED NOT NULL ,
				`product_id` INT( 11 ) UNSIGNED NOT NULL ,
				INDEX (  `coupon_id` ,  `product_id` )
				) ENGINE = MYISAM ;");
				
			// Coupon/Subscription Relationships
			$this->CI->db->query("CREATE TABLE  `coupons_subscriptions` (
				`coupon_id` INT( 11 ) NOT NULL ,
				`subscription_plan_id` INT( 11 ) NOT NULL ,
				INDEX (  `coupon_id` ,  `subscription_plan_id` )
				) ENGINE = MYISAM ;");
				
			// Coupon/Shipping Method Relationships
			$this->CI->db->query("CREATE TABLE `coupons_shipping` (
				`coupon_id` INT( 11 ) UNSIGNED NOT NULL ,
				`shipping_id` INT( 11 ) NOT NULL ,
				INDEX (  `coupon_id` ,  `shipping_id` )
				) ENGINE = MYISAM ;");
				
		}
		
		if ($db_version < 1.01) {
			$this->CI->db->query('UPDATE `coupon_types` SET `coupon_type_name`=\'Free Shipping\' WHERE `coupon_type_name`=\'Free Subscription\'');
		}
		
		if ($db_version < 1.04) {
			$this->CI->db->query('ALTER TABLE `coupons` MODIFY COLUMN `coupon_reduction_amt` FLOAT UNSIGNED');
		}
		
		if ($db_version < 1.05) {
			$this->CI->db->query('ALTER TABLE `coupons_products` MODIFY COLUMN `product_id` FLOAT');
			$this->CI->db->query('ALTER TABLE `coupons_subscriptions` MODIFY COLUMN `subscription_plan_id` FLOAT');
		}
		
		if ($db_version < 1.06) {
			$this->CI->load->library('app_hooks');
			$this->CI->app_hooks->register('coupon_validate','A user is attempting to apply a coupon during checkout.',array('member'));
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
								'coupons',
								'coupon_types',
								'coupons_products',
								'coupons_subscriptions',
								'coupons_shipping'
							);
							
		$delete_hooks = array(
								'coupon_validate'
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
		
		return TRUE;
	}
	
}

/* End of file coupons.php */
/* Location: ./app/modules/coupons/coupons.php */