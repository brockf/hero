# Taxes Model

This model provides the means for creating, updating, retrieving, and deleting tax rules.  It also calculates taxes received and retrieves taxes received.

## Initialization

```
$this->load->model('store/taxes_model');
// methods at $this->taxes_model->x();
```

## Method Reference

[method]int record_tax (int $tax_id , int $charge_id , int $user_id [, float $product_tax = 0 [, float $subscription_tax = 0 ]])[/method]

Record a tax payment when a charge is made.

[method]int future_subscription_tax (int $subscription_id , int $tax_id , float $tax_amount)[/method]

Record a tax payment that will be charged each and every time a subscription is renewed.  It is stored in a separate table and then retrieved upon each subscription renewal.

[method]array get_tax_for_subscription (int $subscription_id)[/method]

Retrieve a pre-recorded subscription tax charge made by `future_subscription_tax()`.

[method]int new_tax (string $name , float $percentage , int $state_id , int $country_id)[/method]

Create a new tax rule.

[method]boolean update_tax (int $tax_id , string $name , float $percentage , int $state_id , int $country_id)[/method]

Update a tax rule.

[method]void delete_tax (int $tax_id)[/method]

Delete a tax rule.

[method]array get_tax (int $tax_id)[/method]

Retrieve a tax rule, in the same format as `get_taxes()`.

[method]array get_taxes ( [array $filters = array()])[/method]

Possible Filters: 

* int *id*
* string *state*
* string *country*
* float *percentage*
* string *name*

[method]array get_paid_tax (int $paid_tax_id)[/method]

Each tax paid is stored in a table.  Retrieve one of these records with this method.

[method]array get_paid_taxes ( [array $filters = array()])[/method]

Retrieve information for paid taxes.

Possible Filters: 

* string *state*
* string *country*
* float *percentage*
* string *name*
* int *id*
* int *id*
* int *tax*
* int *invoice_id*
* string *member_name*
* date *date_start*
* date *date_end*
* string *sort*
* string *sort_dir*
* int *limit*
* int *offset*

Each paid tax item returns an array with the following data:

* *id*
* *invoice_id*
* *tax_id*
* *tax_name*
* *tax_rate*
* *amount*
* *user_id*
* *user_first_name*
* *user_last_name*
* *user_email*
* *date*

[method]float get_paid_taxes_total ( [array $filters = array()])[/method]

Return the total amount of tax paid based on a set of optional filters.

Possible Filters: 

* string *state*
* string *country*
* float *percentage*
* string *name*
* int *id*
* int *id*
* int *tax*
* int *invoice_id*
* string *member_name*
* date *date_start*
* date *date_end*
* string *sort*
* string *sort_dir*
* int *limit*
* int *offset*
* int *tax*
* int *invoice_id*
* string *member_name*
* date *date_start*
* date *date_end*
* string *sort*
* string *sort_dir*
* int *limit*
* int *offset*
