# Subscription Plan Model

Retrieve and search subscription plans in Hero.

## Initialization

```
$this->load->model('billing/subscription_plan_model');
// methods available at $this->subscription_plan_model->x();
```

## Method Reference

## `int new_plan (string $name , float $amount , float $initial_charge , boolean $is_taxable , int $interval , int $free_trial , boolean $require_billing_for_trial , int $occurrences , int $promotion , int $demotion , string $description)`

Create a new subscription plan.

## `void update_plan (int $subscription_plan_id , string $name , float $amount , float $initial_charge , boolean $is_taxable , int $interval , int $free_trial , boolean $require_billing_for_trial , int $occurrences , int $promotion , int $demotion , string $description)`

Update an existing subscription plan.

## `boolean delete_plan (int $id)`

Delete a subscription plan.  Note:  This will not cancel existing subscriptions.

## `array get_plan (int $id)`

Retrieve details about a specific plan with the plan ID (corresponding to table, `subscription_plans`).

## `array get_plan_from_api_plan_id (int $api_plan_id)`

Retrieve details about a specific plan with the API plan ID (corresponding to table, `plans`).

## `array get_plans ( [array $filters = array(), [ boolean $allow_deleted = FALSE ]])`

Retrieve a plan/plans based on optional filter criteria.

Possible Filters: 

* int *id*
* float *amount*
* int *interval*
* string *name*
* int *api_plan_id*

```
$plans = $this->subscription_plan_model->get_plans(array('name' => 'Monthly Sub'));
```

Each plan returns an array of the following data:

* *id* - relates to `suscription_plans` table
* *plan_id* - relates to `plans` table
* *name*
* *type*
* *initial_charge*
* *amount*
* *interval*
* *free_trial*
* *occurrences*
* *is_taxable*
* *active_subscribers* - number of active subscribers
* *deleted*
* *require_billing_for_trial*
* *promotion*
* *demotion*
* *description*
* *add_to_cart* - link which will add the subscription to the cart

