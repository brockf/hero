# Coupon Model

Coupons are codes that can be entered during checkout to reduce the cost of subscriptions/products, receive a free trial on a subscription, or receive free shipping on product orders.

This model provides direct access to the coupons.

## Initialization

```
$this->load->model('coupons/coupon_model');
// methods available at $this->coupon_model->x();
```

## Method Reference

## `void has_coupons ()`

Are there any coupons in this installation of Hero?  This is useful when determining whether to show the promotional code box, for example.

## `int count_uses ($coupon_id)`

Count how many uses there have been of this coupon.

## `int customer_usage (int $coupon_id , int $customer_id)`

How many times has this particular customer used this coupon?

## `array get_coupon (int $id)`

Return details about a particular coupon, in the same format of `get_coupons()`

## `array get_coupons ( [array $filters = array()])`

Retrieve details about one or more particular coupons based on optional filtering.

Possible Filters: 

* int *id* - Coupon ID
* string *name* - The name of the coupon
* string *code* - The coupon code (entered at checkout)
* date *start_date* - Start date of coupon must be after or equal to this date
* date *end_date* - Start date of coupon must be before or equal to this date
* int *type* - Type of coupon (1 = Price Reduction, 2 = Free Trial, 3 = Free Shipping)

## `int new_coupon (string $name , string $code , string $start_date , string $end_date , int $max_uses , bool $customer_limit , int $type_id , int $reduction_type , string $reduction_amt , int $trial_length , string $min_amt , array $products , array $plans , array $ship_rates)`

Create a new coupon in the system.

## `boolean update_coupon (int $coupon_id , string $name , string $code , string $start_date , string $end_date , int $max_uses , bool $customer_limit , int $type_id , int $reduction_type , string $reduction_amt , int $trial_length , string $min_amt , array $products , array $plans , array $ship_rates)`

Update an existing coupon.

## `bool delete_coupon (int $id)`

Delete an existing coupon.

## `object get_coupon_types ()`

Returns a database object of a database query for coupon types.

## `boolean validation (boolean $editing)`

Validates a POST submission for control panel management of coupons.

## `void save_related (int $coupon_id , string $table , field $field , array $items)`

Save related products/subscriptions (by ID) to a coupon.

## `array get_related (id $coupon_id , string $table , string $field)`

Retrieve related products/subscriptions (by ID) to a coupon.
