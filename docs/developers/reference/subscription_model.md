# Subscription Model

Access and search subscriptions in Hero.

## Initialization

```
$this->load->model('billing/subscription_model');
```

## Method Reference

## `boolean cancel_subscription (int $subscription_id)`

Cancel a subscription.

```
$this->subscription_model->cancel_subscription(434);
```

## `boolean has_subscriptions ( [int $member_id = 'active user'])`

Check to see if a user has any active (as in, expiring in the future) subscriptions in their account.

```
if ($this->subscription_model->has_subscriptions()) {
	// they have subscriptions of some kind
}
```

## `array get_subscriptions_friendly ( [array $filters = array() [, int $member_id = 'active user']])`

Return an array of subscriptions matching the filter criteria.  It's just a wrapper for `get_subscriptions()` but, due to that method's complexity, this provides a nice way to do a lot of simple lookups.

Possible Filters: 

* boolean *active* - Is the subscription still active on the account (i.e., end_date < now)?
* boolean *recurring* - Is the subscription still actively recurring?
* int *id* - The subscription ID
* int *plan_id* - The subscription plan ID

```
$subs = $this->subscription_model->get_subscriptions_friendly(array('active' => TRUE));

$subs = $this->subscription_model->get_subscriptions_friendly(array('recurring' => TRUE, 'plan_id' => 1001), 1010);
```

## `array get_subscription (int $subscription_id)`

Another wrapper for `get_subscriptions()`: retrieve the subscription record that matches the ID given.

## `mixed get_subscriptions ( [array $filters = array()])`

Retrieve subscriptions based on a number of filters (or retrieve all subscriptions).

Possible Filters: 

* boolean *active* - Is the subscription still active on the account (i.e., end_date < now)?
* boolean *recurring* - Is the subscription still actively recurring?
* int *id* - The subscription ID
* int *plan_id* - The subscription plan ID
* int *id* - The subscription ID
* string *status* - One of "recurring", "will_expire", "expired", "renewed", "updated"
* int *gateway_id* - The gateway ID used for the order
* date *created_after* - Only subscriptions created after or on this date will be returned
* date *created_before* - Only subscriptions created before or on this date will be returned
* date *end_date_after* - Only subscriptions ending after or on this date will be returned
* date *end_date_before* - Only subscriptions ending before or on this date will be returned
* int *user_id* - The customer id associated with the subscription
* int *amount* - Only subscriptions for this amount will be returned
* boolean *active* - Returns only active subscriptions
* int *plan_id* - Only return subscriptions link to this subscription_plan_id
* int *offset* - Offsets the database query
* int *limit* - Limits the number of results returned
* string *sort* - Variable used to sort the results.  Possible values are date, customer_first_name, customer_last_name, amount
* string *sort_dir* - Used when a sort param is supplied.  Possible values are asc and desc

Returned data for each subscription:

* *id*
* *user_id*
* *user_username*
* *user_first_name*
* *user_last_name*
* *user_email*
* *gateway_id*
* *date_created*
* *amount*
* *interval*
* *start_date* - The start date of the subscription (may follow a free trial)
* *end_date* - The date to expire the subscription.  If this is in the future, the subscription is still active.
* *last_charge_date* - When, if ever, was the last related charge?
* *next_charge_date*
* *cancel_date* - If this subscription was cancelled, when was that?
* *number_occurrences* - Total number of charges to charge for this subscription.
* *active* - Is the subscription still actively recurring?  It may be active (i.e., not expired) even if this is FALSE.
* *renewing_subscription_id* - If a subscription renewed this subscription, its ID will be here.
* *updating_subscription_id* - If a subscription updated the billing details for this subscription, its ID will be here.
* *card_last_four* - Last 4 digits of credit card if one was used.
* *plan_id*
* *coupon_id*
* *renew_link*
* *cancel_link*
* *update_cc_link*
* *is_recurring* - Is the subscription still actively recurring?
* *is_active* - Is the subscription still active on their account?
* *is_renewed* - Has the subscription been renewed?
* *is_updated* - Has the subscription had its billing details updated?

```
$subs = $this->subscription_model->get_subscriptions(array('user_id' => 43));
```