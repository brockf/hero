# Order Model

This model provides key methods for processing orders and retrieving order records.  These are store orders, not subscription orders.  It is unlikely that you, as a developer, will be attempting to process an order using this method, as it's meant to work very specifically with the basic checkout controller.

This model makes it clear how products/orders are stored.

First, there is the `orders` table with one record for every invoice, whether it is a subscription or product order.

Second, there is the `order_details` table which stores one record for every store order (including multiple products in a cart).

Finally, each particular product (regardless of quantity) has a record in the `order_products` table.  This stores quantity, product ID, product options, shipping status, etc.  Shipping statuses are tracked at a product level, not an order level, for more flexibility.

## Initialization

```
$this->load->model('store/order_model');
// methods at $this->order_model->x();
```

## Method Reference

## `boolean process_order (int $charge_id , int $user_id , array $totals [, array $shipping_address = FALSE])`

Process an order passed from the checkout controller.

Arguments:

* `$charge_id` - The `invoice_id`, referenced to the `orders` table.
* `$user_id` - The user's ID
* `$totals` - An array of charge totals from [the cart model](/docs/developers/reference/cart_model.md)'s `calculate_totals()`
* `$shipping_address` - A standard address array for shipping, if available (default: FALSE)

## `array get_order (int $order_details_id [, string $field_to_match = 'order_details_id'])`

Retrieve basic details about a specific order.  This does not include the particular products within an order.

Each order returns an array with the following data:

* *customer_id*
* *invoice_id*
* *affiliate*
* *shipping* (if available, an array with a shipping address in it)

## `array get_order_products ( [array $filters = array()])`

Retrieve all orders based on optional filters.

> If there is an order with 3 products in a single cart, this method will actually return 3 separate orders, treating each product as a separate order.  However, the shipping address, invoice ID, etc. will all be the same across one order.

Possible Filters: 

* date *start_date*
* date *end_date*
* float *amount*
* int *invoice_id*
* int *customer_id*
* int *user_id*
* int *gateway*
* string *member_name*
* boolean *refunded*
* string *product_name*
* boolean *shipped*
* string *sort*
* string *sort_dir*
* int *limit*
* int *offset*

Each returned record has the following data:

* *invoice_id*
* *order_products_id*
* *name*
* *quantity*
* *price*
* *shipped*
* *options*
* *gateway_id*
* *gateway*
* *date*
* *user_id*
* *user_first_name*
* *user_last_name*
* *user_email*
* *user_groups*
* *amount*
* *refunded*
* *card_last_four*
* *is_refunded*
* *refund_date*
* *tax_name*
* *tax_paid*
* *tax_rate*
* *shipping_id*
* *shipping_name*
* *shipping_charge*
* *order_details_id*
* *shipping_address* (an array, if applicable)
* *billing_address* (an array, if applicable)

## `boolean mark_as_shipped (int $order_products_id)`

Mark a particular product within an order as shipped.

## `void mark_as_not_shipped (int $order_products_id)`

Mark a particular product within an order as NOT shipped.
