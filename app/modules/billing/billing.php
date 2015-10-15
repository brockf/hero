<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Billing Module Definition
*
* Declares the module, update code, etc.
*
* @author Electric Function, Inc.
* @copyright Electric Function, Inc.
* @package Hero Framework
*
*/

class Billing extends Module {
	var $version = '1.25';
	var $name = 'billing';

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
		$this->CI->admin_navigation->child_link('configuration',30,'Payment Gateways',site_url('admincp/billing/gateways'));
		$this->CI->admin_navigation->child_link('storefront',10,'Subscription Plans',site_url('admincp/billing/subscriptions'));
	}

	/*
	* Pre-front Method
	*
	* Triggered prior to loading the frontend
	*/
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/billing/template_plugins/');
	}

	/*
	* Module update
	*
	* @param int $db_version The current DB version
	*
	* @return int The current software version, to update the database
	*/
	function update ($db_version) {
		if ($db_version < '1.0') {
			// initial install
			$this->CI->db->query('CREATE TABLE `subscription_plans` (
 								 `subscription_plan_id` int(11) NOT NULL auto_increment,
 								 `plan_id` int(11) NOT NULL,
 								 `subscription_plan_initial_charge` varchar(15) NOT NULL,
 								 `subscription_plan_is_taxable` tinyint(1) NOT NULL,
 								 `subscription_plan_promotion` int(11) NOT NULL,
 								 `subscription_plan_demotion` int(11) NOT NULL,
 								 `subscription_plan_description` text NOT NULL,
 								 `subscription_plan_require_billing_trial` tinyint(1) NOT NULL,
 								 PRIMARY KEY  (`subscription_plan_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `customers` (
								  `customer_id` int(11) NOT NULL auto_increment,
								  `first_name` varchar(200) NOT NULL,
								  `last_name` varchar(200) NOT NULL,
								  `company` varchar(200) NOT NULL,
								  `internal_id` varchar(200) NOT NULL,
								  `address_1` varchar(200) NOT NULL,
								  `address_2` varchar(200) NOT NULL,
								  `city` varchar(200) NOT NULL,
								  `state` varchar(200) NOT NULL,
								  `postal_code` varchar(200) NOT NULL,
								  `country` int(11) NOT NULL,
								  `phone` varchar(200) NOT NULL,
								  `email` varchar(255) NOT NULL,
								  `active` tinyint(4) NOT NULL,
								  `date_created` datetime NOT NULL,
								  PRIMARY KEY  (`customer_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');

			$this->CI->db->query('CREATE TABLE `external_apis` (
								  `external_api_id` int(11) NOT NULL auto_increment,
								  `name` varchar(20) NOT NULL,
								  `display_name` varchar(255) NOT NULL,
								  `prod_url` varchar(255) NOT NULL,
								  `test_url` varchar(255) NOT NULL,
								  `dev_url` varchar(255) NOT NULL,
								  `arb_prod_url` varchar(255) NOT NULL,
								  `arb_test_url` varchar(255) NOT NULL,
								  `arb_dev_url` varchar(255) NOT NULL,
								  PRIMARY KEY  (`external_api_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;');

			$this->CI->db->query("INSERT INTO `external_apis` (`external_api_id`, `name`, `display_name`, `prod_url`, `test_url`, `dev_url`, `arb_prod_url`, `arb_test_url`, `arb_dev_url`) VALUES (1, 'authnet', 'Authorize.net', 'https://secure.authorize.net/gateway/transact.dll', 'https://secure.authorize.net/gateway/transact.dll', 'https://test.authorize.net/gateway/transact.dll', 'https://api.authorize.net/xml/v1/request.api', 'https://api.authorize.net/xml/v1/request.api', 'https://apitest.authorize.net/xml/v1/request.api'),
								(2, 'exact', 'E-xact', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl', 'https://secure2.e-xact.com/vplug-in/transaction/rpc-enc/service.asmx?wsdl'),
								(3, 'paypal', 'PayPal Pro', 'https://api-3t.paypal.com/nvp', 'https://api-3t.sandbox.paypal.com/nvp', 'https://api-3t.sandbox.paypal.com/nvp', 'https://api-3t.paypal.com/nvp', 'https://api-3t.sandbox.paypal.com/nvp', 'https://api-3t.sandbox.paypal.com/nvp'),
								(4, 'sagepay', 'SagePay', 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp', 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp', 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp', 'https://live.sagepay.com/gateway/service/repeat.vsp', 'https://test.sagepay.com/gateway/service/repeat.vsp', 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRepeatTx'),
								(5, 'wirecard', 'Wirecard', 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp', '', '', '', '', ''),
								(6, 'paypal_standard', 'PayPal Express Checkout', 'https://api-3t.paypal.com/nvp', 'https://api-3t.sandbox.paypal.com/nvp', '', 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout', 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout', ''),
								(7, 'pacnet', 'Pacnet', 'https://raven.pacnetservices.com/realtime', '', '', '', '', '');");

			$this->CI->db->query('CREATE TABLE `gateway_params` (
								  `gateway_params_id` int(11) NOT NULL auto_increment,
								  `gateway_id` int(11) NOT NULL,
								  `field` varchar(255) NOT NULL,
								  `value` varchar(255) NOT NULL,
								  PRIMARY KEY  (`gateway_params_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `gateways` (
								  `gateway_id` int(11) NOT NULL auto_increment,
								  `external_api_id` int(11) NOT NULL,
								  `alias` varchar(200) NOT NULL,
								  `enabled` tinyint(4) NOT NULL,
								  `deleted` int(11) NOT NULL default \'0\',
								  `create_date` date NOT NULL,
								  PRIMARY KEY  (`gateway_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `notifications` (
								  `notification_id` int(11) NOT NULL auto_increment,
								  `url` text NOT NULL,
								  `variables` text NOT NULL,
								  PRIMARY KEY  (`notification_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `order_authorizations` (
								  `order_authorization_id` int(11) NOT NULL auto_increment,
								  `order_id` varchar(200) NOT NULL,
								  `tran_id` varchar(255) NOT NULL,
								  `authorization_code` varchar(200) NOT NULL,
								  `security_key` varchar(200) NOT NULL,
								  PRIMARY KEY  (`order_authorization_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `order_data` (
								  `order_data_id` int(11) NOT NULL auto_increment,
								  `order_id` varchar(250) NOT NULL,
								  `order_data_key` varchar(25) NOT NULL,
								  `order_data_value` text NOT NULL,
								  PRIMARY KEY  (`order_data_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');

			$this->CI->db->query('CREATE TABLE `orders` (
								  `order_id` int(11) NOT NULL auto_increment,
								  `gateway_id` int(11) NOT NULL,
								  `customer_id` int(11) default \'0\',
								  `subscription_id` int(11) NOT NULL,
								  `card_last_four` varchar(4) NOT NULL,
								  `amount` varchar(11) NOT NULL,
								  `customer_ip_address` varchar(14) default NULL,
								  `status` tinyint(1) NOT NULL default \'0\',
								  `timestamp` datetime NOT NULL,
								  `refunded` tinyint(3) NOT NULL,
								  `refund_date` datetime default NULL,
								  PRIMARY KEY  (`order_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');

			$this->CI->db->query('CREATE TABLE `plan_types` (
								  `plan_type_id` int(11) NOT NULL,
								  `type` varchar(20) NOT NULL,
								  PRIMARY KEY  (`plan_type_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8;');

			$this->CI->db->insert('plan_types',array('plan_type_id' => '1', 'type' => 'free'));
			$this->CI->db->insert('plan_types',array('plan_type_id' => '2', 'type' => 'paid'));

			$this->CI->db->query('CREATE TABLE `plans` (
								  `plan_id` int(11) NOT NULL auto_increment,
								  `plan_type_id` int(11) NOT NULL,
								  `amount` decimal(10,2) NOT NULL,
								  `interval` int(11) NOT NULL,
								  `occurrences` int(11) default NULL,
								  `name` varchar(200) NOT NULL,
								  `free_trial` int(11) NOT NULL,
								  `notification_url` varchar(255) NOT NULL,
								  `deleted` tinyint(1) NOT NULL default \'0\',
								  PRIMARY KEY  (`plan_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000 ;');

			$this->CI->db->query('CREATE TABLE `subscriptions` (
								  `subscription_id` int(11) NOT NULL auto_increment,
								  `gateway_id` int(11) NOT NULL,
								  `customer_id` int(11) NOT NULL,
								  `plan_id` int(11) NOT NULL default \'0\',
								  `notification_url` varchar(255) default NULL,
								  `start_date` date NOT NULL,
								  `end_date` date NOT NULL,
								  `last_charge` date NOT NULL,
								  `next_charge` date NOT NULL,
								  `number_charge_failures` int(11) NOT NULL default \'0\',
								  `number_occurrences` int(11) NOT NULL,
								  `charge_interval` int(11) NOT NULL,
								  `amount` decimal(10,2) NOT NULL,
								  `card_last_four` varchar(4) NOT NULL,
								  `api_customer_reference` varchar(255) default NULL,
								  `api_payment_reference` varchar(255) default NULL,
								  `api_auth_number` varchar(255) default NULL,
								  `active` tinyint(1) NOT NULL default \'1\',
								  `cancel_date` datetime NOT NULL,
								  `timestamp` date NOT NULL,
								  PRIMARY KEY  (`subscription_id`)
								) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1000 ;');
		}

		if ($db_version < '1.01') {
			$this->CI->db->query("INSERT INTO `external_apis` (`external_api_id`, `name`, `display_name`, `prod_url`, `test_url`, `dev_url`, `arb_prod_url`, `arb_test_url`, `arb_dev_url`)
								VALUES  (8, 'offline', 'Offline, Cheque, &amp; Money Order', '', '', '', '', '', ''),
										(9, 'freshbooks', 'FreshBooks', '', '', '', '', '', '');");

			// enable offline by default
			$this->CI->db->query('INSERT INTO `gateways` (`external_api_id`,`alias`,`enabled`,`deleted`,`create_date`) VALUES (8, \'Offline, Cheque, and Money Order\', \'1\', \'0\', \'' . date('Y-m-d') . '\');');
		}

		if ($db_version < '1.02') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD COLUMN `renewed` INT(11) AFTER `active`');
		}

		if ($db_version < '1.03') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD COLUMN `updated` INT(11) AFTER `renewed`');
		}

		if ($db_version < '1.04') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD COLUMN `expiry_processed` INT(11) AFTER `updated`');
		}

		if ($db_version < '1.05') {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->register('subscription_new','A subscription is created.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_renew','A subscription is renewed.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_charge','A subscription charge is made.',array('member','subscription','invoice','subscription_plan'));
			$this->CI->app_hooks->register('subscription_cancel','A subscription is cancelled.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_expire','A subscription expires.',array('member','subscription','subscription_plan'));
		}

		if ($db_version < '1.06') {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->bind('subscription_new','Subscription_model','hook_subscription_new',APPPATH . 'modules/billing/models/subscription_model.php');
			$this->CI->app_hooks->bind('subscription_expire','Subscription_model','hook_subscription_expire',APPPATH . 'modules/billing/models/subscription_model.php');
		}

		if ($db_version < '1.07') {
			$this->CI->db->query('DROP TABLE IF EXISTS `notifications`');
		}

		if ($db_version < '1.08') {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->register('subscription_renewal_failure','A subscription charge fails.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->bind('cron','Subscription_model','hook_cron',APPPATH . 'modules/billing/models/subscription_model.php');

			$this->CI->app_hooks->register('subscription_renew_1_week','A subscription will renew in 1 week.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_renew_1_month','A subscription will renew in 1 month.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_expire_1_week','A subscription will expire in 1 week.',array('member','subscription','subscription_plan'));
			$this->CI->app_hooks->register('subscription_expire_1_month','A subscription will expire in 1 month.',array('member','subscription','subscription_plan'));
		}

		if ($db_version < '1.09') {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->bind('subscription_charge', 'Subscription_model', 'hook_subscription_charge', APPPATH . 'modules/billing/models/subscription_model.php');
		}

		if ($db_version < '1.11') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD INDEX `plan_id` (`plan_id`);');
		}

		if ($db_version < '1.12') {
			$this->CI->db->query('INSERT INTO `external_apis` VALUES(10, \'eway\', \'eWay\', \'https://www.eway.com.au/gateway_cvn/xmlpayment.asp\', \'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx\', \'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx\', \'http://www.eway.com.au/gateway/managedpayment\', \'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedCreditCardPayment.asmx\', \'https://www.eway.com.au/gateway/ManagedPaymentService/test/managedcreditcardpayment.asmx\');');

			$this->CI->db->query('ALTER TABLE `subscriptions` ADD COLUMN `coupon_id` INT(11) AFTER `timestamp`');
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD INDEX `coupon_id` (`coupon_id`);');
		}

		if ($db_version < '1.13') {
			$this->CI->db->query('INSERT INTO `external_apis` (`name`, `display_name`, `prod_url`, `test_url`, `dev_url`, `arb_prod_url`, `arb_test_url`, `arb_dev_url`)
								VALUES  (\'twocheckout\', \'2Checkout\', \'https://www.2checkout.com/checkout/purchase\', \'https://www.2checkout.com/checkout/purchase\', \'https://www.2checkout.com/checkout/purchase\', \'\', \'\', \'\');');
		}

		if ($db_version < '1.14') {
			$this->CI->db->query('ALTER TABLE `subscriptions` MODIFY COLUMN `renewed` INT(11)');
		}

		if ($db_version < '1.15') {
			// fix encryption with Reactor release
			$this->CI->load->library('encrypt');

			$result = $this->CI->db->get('gateway_params');

			foreach ($result->result_array() as $gateway) {
				$update = $this->CI->encrypt->encode_from_legacy($gateway['value']);

				$this->CI->db->update('gateway_params', array('value' => $update), array('gateway_params_id' => $gateway['gateway_params_id']));
			}
		}

		if ($db_version < '1.16') {
			$this->CI->db->query('CREATE TABLE `transaction_log` (
								  `log_id` int(11) NOT NULL auto_increment,
								  `order_id` int(11) default NULL,
								  `subscription_id` int(11) default NULL,
								  `log_date` datetime default NULL,
								  `log_event` varchar(250) default NULL,
								  `log_data` text,
								  `log_ip` varchar(100) default NULL,
								  `log_browser` varchar(250) default NULL,
								  `log_file` text,
								  `log_line` INT(11) default NULL,
								  PRIMARY KEY  (`log_id`)
								) ENGINE=MyISAM DEFAULT CHARSET=utf8;');
		}

		if ($db_version < '1.17') {
			$this->CI->load->library('app_hooks');
			$this->CI->app_hooks->bind('cron','Paypal_fix','hook_cron',APPPATH . 'modules/billing/libraries/paypal_fix.php');
		}

		if ($db_version < '1.18') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD INDEX `customer_id` (`customer_id`)');
			$this->CI->db->query('ALTER TABLE `orders` ADD INDEX `customer_id` (`customer_id`)');
			$this->CI->db->query('ALTER TABLE `orders` ADD INDEX `timestamp` (`timestamp`)');
		}

		if ($db_version < '1.19') {
			$this->CI->db->query('INSERT INTO `external_apis` (`name`, `display_name`, `prod_url`, `test_url`, `dev_url`, `arb_prod_url`, `arb_test_url`, `arb_dev_url`) VALUES  (\'stripe_gw\', \'Stripe\', \'\', \'\', \'\', \'\', \'\', \'\');');
		}

		if ($db_version < '1.20') {
			$this->CI->load->library('app_hooks');
			$this->CI->app_hooks->bind('subscription_renew','Subscription_model','hook_subscription_renew',APPPATH . 'modules/billing/models/subscription_model.php');
		}

		if ($db_version < '1.21') {
			$this->CI->db->update('binds', array('bind_method' => 'hook_subscription_renew'), array('bind_method' => 'subscription_renew', 'bind_class' => 'Subscription_model'));
		}

		if ($db_version < '1.22') {
			$this->CI->load->library('app_hooks');

			$this->CI->app_hooks->register('checkout_billing_shipping','In checkout, the billing/shipping submission has been processed.',array());
			$this->CI->app_hooks->register('checkout_shipping_method','In checkout, the shipping method has been selected.',array());
			$this->CI->app_hooks->register('checkout_payment','In checkout, the payment has been successfully processed.',array());
		}
		
		if ($db_version < '1.24') {
			$this->CI->load->library('app_hooks');
			$this->CI->app_hooks->bind('member_delete','Subscription_model','hook_member_delete',APPPATH . 'modules/billing/models/subscription_model.php');
		}
		
		if ($db_version < '1.25') {
			$this->CI->db->query('ALTER TABLE `subscriptions` ADD COLUMN `completed` TINYINT(4)');
			$this->CI->db->query('UPDATE `subscriptions` SET `completed` = \'1\'');
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
								'subscription_plans',
								'customers',
								'external_apis',
								'gateway_params',
								'gateways',
								'order_authorizations',
								'order_data',
								'orders',
								'plan_types',
								'plans',
								'subscriptions',
								'transaction_log'
							);

		$delete_hooks = array(
								'subscription_cancel',
								'subscription_charge',
								'subscription_expire',
								'subscription_expire_1_month',
								'subscription_expire_1_week',
								'subscription_new',
								'subscription_renew',
								'subscription_renew_1_month',
								'subscription_renew_1_week',
								'subscription_renewal_failure'
							);

		$delete_binds = array(
							 array('cron' => 'Paypal_fix'),
							 array('cron' => 'Subscription_model')
							);

		if (!empty($delete_tables)) {
			foreach ($delete_tables as $table) {
				$this->CI->db->query('DROP TABLE IF EXISTS `' . $table . '`');
			}
		}

		if (!empty($delete_hooks)) {
			foreach ($delete_hooks as $hook) {
				$this->CI->db->query('DELETE FROM `hooks` WHERE `hook_name`=\'' . $hook . '\'');
				$this->CI->db->query('DELETE FROM `binds` WHERE `hook_name` = \'' . $hook . '\'');
			}
		}

		if (!empty($delete_binds)) {
			foreach ($delete_binds as $bind) {
				foreach ($bind as $hook => $class) {
					$this->CI->db->query('DELETE FROM `binds` WHERE `hook_name` = \'' . $hook . '\' and `bind_class` = \'' . $class . '\'');
				}
			}
		}

		return TRUE;
	}
}