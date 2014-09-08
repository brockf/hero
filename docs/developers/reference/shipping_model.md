# Shipping Model

The shipping model provides the support for [shipping management in the control panel](/docs/configuration/shipping) and also for calculating shipping rates dynamically during checkout.

## Initialization

```
$this->load->model('store/shipping_model');
// methods at $this->shipping_model->x();
```

## Method Reference

[method]int new_rate (string $name , string $type , float $rate , int $state_id , int $country_id [, boolean $taxable] )[/method]

Create a new shipping method.

`$type` should be either "weight", "product", or "flat".  This determines if the shipping rate is calculated by cart weight, number of products, or a flat fee.

[method]int update_rate (int $rate_id , string $name , string $type , float $rate , int $state_id , int $country_id [, boolean $taxable] )[/method]

Update an existing shipping method.

[method]boolean delete_rate (int $rate_id)[/method]

Delete an existing shipping method.

[method]array get_rates_for_address (array $cart , array $shipping_address)[/method]

Retrieve an array of possible shipping rates with full price calculations based on the user's cart and shipping address.

[method]array get_rate (int $rate_id)[/method]

Retrieve details about a specific shipping method, in the same format as `get_rates()`.

[method]array get_rates ( [array $filters = array()])[/method]

Retrieve details for one or more shipping rates based on optional filters.

Possible Filters: 

* int *id*
* string *name*
* string *country*
* string *state*

Each shipping rate returns an array with the following data:

* *id*
* *name*
* *state_id*
* *country_id*
* *state*
* *country*
* *country_iso2*
* *state_code*
* *type* - either "weight", "product", or "flat"
* *rate*