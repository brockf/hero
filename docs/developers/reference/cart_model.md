# Cart Model

The Cart model provides Hero logic on top of the [CodeIgniter Cart library](http://codeigniter.com/user_guide/libraries/cart.html).  It also provides methods of saving a user's cart of later use and performing cart-wide calculations.

## Initializations

The cart model is initialized automatically by the [User model](/docs/developers/reference/user_model.md).

To initialize:

```
$this->load->model('store/cart_model');
// methods at $this->cart_model->x();
```

## Method Reference

## `boolean add_to_cart (int $product_id [, int $quantity_id = 1 [, array $options]])`

Add a store product to the cart.  If there are product options, `$options` should be a name => value array of the product option selections.

## `boolean add_subscription_to_cart (int $subscription_plan_id , int $renew_subscription_id)`

Add a subscription plan to the cart.  There can only be one subscription in the cart at one time so, if one already exists, it will be replaced.

If the subscription is a renewal, pass the old subscription as `$renew_subscription_id`.

## `array get_subscription_cart ()`

Retrieve the subscription from the cart.  Returns FALSE if no subscription exists.

```
if ($this->cart_model->has_subscription()) {
	$subscription = $this->cart_model->get_subscription_cart();
}
```

Subscriptions in the cart have an array with the following data:

* *id*
* *is_subscription* (TRUE)
* *qty* (1)
* *price* - The initial charge of the subscription, "0" if it has a free trial.
* *recurring_price* - The recurring charge for the subscription, "0" if it's a free subscription.
* *free_trial* - # of free trial days, if applicable.
* *free_trial_no_billing* - TRUE if the user can subscribe to the free trial without billing information.
* *interval* - The number of days between recurring charges.
* *occurrences* - The total number of charges for the subscription.
* *name* - The name of the subscription.
* *renew_subscription_id* - The subscription being renewed.
* *weight* (0)
* *requires_shipping* (0)

## `void reduce_subscription_prices (array $allowed_subscription_plan_ids , float $discount [, boolean $is_percentage = FALSE])`

Reduce the subscription price.  This method is called by an active coupon in the checkout controller.  The subscription will only be updated if the first parameter is FALSE or contains the subscription_id of the subscription in the cart.

## `void update_subscription_trial (array $allowed_subscription_plan_ids , int $trial_days)`

Extend/create a free trial for a subscription.  This method is also called by an active coupon in the checkout controller.  The subscription will only be updated if the first parameter is FALSE or contains the subscription_id of the subscription in the cart.

## `void reduce_product_prices (array $allowed_product_ids , float $discount [, boolean $is_percentage = FALSE])`

Reduce the cost of the product(s) in the cart.  This method is also called by an active coupon in the checkout controller.  Product(s) will only be affected if the first parameter is FALSE or if the product in the cart is in that array.

## `void reset_to_precoupon ()`

Reset all subscription/product prices and trials to their pre-coupon state.  When a second coupon is used (or if a coupon is removed), this method provides an easy way to "reset" the cart and stop from summative coupon using.

## `array get_cart ()`

Retrieve the full contents of the cart, else FALSE.

## `boolean has_subscription ()`

Does the cart contain a subscription?

## `boolean has_products ()`

Does the cart contain any products?

## `float get_total ()`

Retrieve the total cost of the cart for checkout today.  This does not include the subscription's recurring rates, but rather the initial charge of the subscription (if one exists).

## `void save_cart_to_db (array $cart_array)`

Save the shopping cart to the logged-in user's account.  This will load the shopping cart automatically on the user's next visit.

## `boolean user_login (array $user)`

When a user logs in, this method is called automatically by the [User model](/docs/developers/reference/user_model.md).  It loads a user's cart into the session, if one exists.  It also updates pricing to reflect the user's member group status.

## `boolean update_quantity (string $rowid , int $quantity)`

Updates the quantity of a product in the cart.

## `boolean remove_from_cart (string $rowid)`

Arguments:

* `$rowid` - The unique "rowid" from the $this->cart->contents() array
* `$quantity` - The new quantity of the product.

Remove a product from the cart.

Arguments:

* `$rowid` - The unique "rowid" from the $this->cart->contents() array

## `array calculate_totals ()`

Returns an array of calculations based on the content of the cart, for checkout.

Has the elements:

* float *shipping* - Cost of shipping
* float *tax_rate* - Tax percentage
* int *tax_id* - The ID of the tax rate being used
* float *order_sub_total* - Today's cost, no tax
* float *order_tax* - Tax on today's cost
* float *order_tax_subscription* - Tax on today's subscription
* float *order_tax_products* - Tax on today's products]
* float *order_total* - Total cost for today
* float *recurring_sub_total* - Cost of recurring charge, no tax
* float *recurring_tax* - Tax on the recurring charge
* float *recurring_total* - Total recurring cost
* int *recurring_interval* - Interval between recurring charges (days)
* date *recurring_first_charge* - The date of the subscription's initial charge
* date *recurring_last_charge* - The date of the subscription's last charge

## `boolean free_cart ()`

Is the current cart free?  No initial charges or recurring charges?
