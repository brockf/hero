# Cronjobs

Cronjobs are scheduled software processes.  Typically, they perform some sort of maintenance within an application.

In Hero, a cronjob runs every five minutes.  You can bind any one of your module's methods to this cronjob through the "cronjob" system hook.  This way, you can automatically schedule your code to run without requiring the user to create a cronjob on their server.

## Binding a Cronjob

Cronjobs are typically bound in your main module definition file's `update()` process.

Example (from the real billing module):

```
$this->CI->app_hooks->bind('cron','Subscription_model','hook_cron',APPPATH . 'modules/billing/models/subscription_model.php');
```

This `bind()` method of the [App Hooks library](/docs/developers/reference/app_hooks_library.md) simply specifies the hook you want you to bind your code to (in this case, "cron"), and the class and method that you want to execute.  In case that class is not available, you can specify the filename by which to load the class.

For more information on hooks and binds, [click here](/docs/developers/reference/app_hooks_library.md).

## Running your cronjob at intervals other than 5 minutes

While the cronjob hook is triggered every 5 minutes, you don't necessarily have to run your code every 5 minutes.  Simply create a setting value in the database which stores the last time your cronjob was run.  Then, wait until your chosen amount of time has passed between code executions before executing your code again.

For example, this billing cronjob in the [Subscription_model](/docs/developers/reference/subscription_model.md) runs once per day:

```
function hook_cron () {
	$CI =& get_instance();
	$run_cron = TRUE;
	
	// we only need this to run once per day
	if (setting('cron_billing_last_update') === FALSE) {
		// no setting exists yet, create it
		$this->settings_model->new_setting(1, 'cron_billing_last_update', date('Y-m-d H:i:s'), 'When did the billing cron job last run?', 'text', '', FALSE, TRUE);
	}
	elseif (date('Y-m-d') == date('Y-m-d', strtotime(setting('cron_billing_last_update')))) {
		// the cron has already run today
		$run_cron = FALSE;
	}
	elseif ((int)date('H') < 6) {
		// don't run before 6AM
		$run_cron = FALSE;
	}
	else {
		$this->settings_model->update_setting('cron_billing_last_update', date('Y-m-d H:i:s'));
	}
	
	if ($run_cron == FALSE) {
		return;
	}
	
	// run the billing maintenance!
	// ...
}
```

## Display output and logging during your cron process

If you have bound a method/function to the cron hook, it will begin executing every 5 minutes.  We recommend logging the actions of your cronjob, as well as displaying visual output during the cronjob, as this helps to debug any issues that may come up.

For this, we have created a helper function called `cron_log()`.  This function is automatically defined by the cronjob controller, so you can use it freely within your cron code.

Example usage:

```
cron_log('Beginning calendar update process.');

foreach ($calendar as $event) {
	cron_log('Examing event #' . $event['id']);
}

cron_log('Calendar process complete.');
```

This will display these messages in the HTML browser output (provided the cron is being access manually at its URL, likely in a debugging situation) and also log the messages to the logs (provided the configuration file is set to create logs at `/app/logs`).