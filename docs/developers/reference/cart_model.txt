# Cart Model

The Cart model provides Hero logic on top of the [CodeIgniter Cart library](http://codeigniter.com/user_guide/libraries/cart.html).  It also provides methods of saving a user's cart of later use and performing cart-wide calculations.

## Initializations

The cart model is initialized automatically by the [User model](/docs/developers/reference/user_model).

To initialize:

```
$this->load->model('store/cart_model');
// methods at $this->cart_model->x();
```

## Method Reference

[method]boolean add_to_cart (int $product_id [, int $quantity_id = 1 [, array $options]])[/method]

Add a store product to the cart.  If there are product options, `$options` should be a name => value array of the product option selections.

[method]boolean add_subscription_to_cart (int $subscription_plan_id , int $renew_subscription_id)[/method]

Add a subscription plan to the cart.  There can only be one subscription in the cart at one time so, if one already exists, it will be replaced.

If the subscription is a renewal, pass the old subscription as `$renew_subscription_id`.

[method]array get_subscription_cart ()[/method]

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

[method]void reduce_subscription_prices (array $allowed_subscription_plan_ids , float $discount [, boolean $is_percentage = FALSE])[/method]

Reduce the subscription price.  This method is called by an active coupon in the checkout controller.  The subscription will only be updated if the first parameter is FALSE or contains the subscription_id of the subscription in the cart.

[method]void update_subscription_trial (array $allowed_subscription_plan_ids , int $trial_days)[/method]

Extend/create a free trial for a subscription.  This method is also called by an active coupon in the checkout controller.  The subscription will only be updated if the first parameter is FALSE or contains the subscription_id of the subscription in the cart.

[method]void reduce_product_prices (array $allowed_product_ids , float $discount [, boolean $is_percentage = FALSE])[/method]

Reduce the cost of the product(s) in the cart.  This method is also called by an active coupon in the checkout controller.  Product(s) will only be affected if the first parameter is FALSE or if the product in the cart is in that array.

[method]void reset_to_precoupon ()[/method]

Reset all subscription/product prices and trials to their pre-coupon state.  When a second coupon is used (or if a coupon is removed), this method provides an easy way to "reset" the cart and stop from summative coupon using.

[method]array get_cart ()[/method]

Retrieve the full contents of the cart, else FALSE.

[method]boolean has_subscription ()[/method]

Does the cart contain a subscription?

[method]boolean has_products ()[/method]

Does the cart contain any products?

[method]float get_total ()[/method]

Retrieve the total cost of the cart for checkout today.  This does not include the subscription's recurring rates, but rather the initial charge of the subscription (if one exists).

[method]void save_cart_to_db (array $cart_array)[/method]

Save the shopping cart to the logged-in user's account.  This will load the shopping cart automatically on the user's next visit.

[method]boolean user_login (array $user)[/method]

When a user logs in, this method is called automatically by the [User model](/docs/developers/reference/user_model).  It loads a user's cart into the session, if one exists.  It also updates pricing to reflect the user's member group status.

[method]boolean update_quantity (string $rowid , int $quantity)[/method]

Updates the quantity of a product in the cart.

[method]boolean remove_from_cart (string $rowid)[/method]

Arguments:

* `$rowid` - The unique "rowid" from the $this->cart->contents() array
* `$quantity` - The new quantity of the product.

Remove a product from the cart.

Arguments:

* `$rowid` - The unique "rowid" from the $this->cart->contents() array

[method]array calculate_totals ()[/method]

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

[method]boolean free_cart ()[/method]

Is the current cart free?  No initial charges or recurring charges?
