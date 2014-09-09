# Invoice Model

Retrieve and search invoices.

## Initialization

```
$this->load->model('billing/invoice_model');
```

## Method Reference

## `array invoice_lines (int $invoice_id)`

Retrieve an array of itemized products/subscriptions for an invoice.

## `array get_invoice_data (int $invoice_id)`

Retrieve an array of all calculated totals and data for the invoice, saved from the cart_model during checkout.

## `array get_invoice (int $invoice_id)`

Retrieve an individual invoice.  Returns the same variables as `get_invoices()`, documented below.

## `float get_invoices_total ( [array $filters = array() , boolean $counting])`

Retrieve the total currency amount for all invoices matching your filters.

Arguments:

* `$filters` - An optional array of filters for the search.
* `$counting` - Set to TRUE to receive the total number of matching invoices

Possible Filters: 

* int *user_id* - Member ID
* date *start_date* - Only orders after or on this date will be returned
* date *end_date* - Only orders before or on this date will be returned
* int *id* - The charge ID
* string *amount* - The amount of the charge
* boolean *subscription_id* - Only charges linked to this subscription
* int *card_last_four* - Last 4 digits of credit card
* int *offset* - Offsets the database query
* int *limit* - Limits the number of results returned
* string *sort* - Column used to sort the results
* string *sort_dir* - Used when a sort param is supplied.  Possible values are asc and desc
* int *user_id* - Member ID
* date *start_date* - Only orders after or on this date will be returned
* date *end_date* - Only orders before or on this date will be returned
* int *id* - The charge ID
* string *amount* - The amount of the charge
* boolean *subscription_id* - Only charges linked to this subscription
* int *card_last_four* - Last 4 digits of credit card
* int *offset* - Offsets the database query
* int *limit* - Limits the number of results returned
* string *sort* - Column used to sort the results
* string *sort_dir* - Used when a sort param is supplied.  Possible values are asc and desc

## `array get_invoices ( [array $filters = array() , boolean $counting])`

Retrieve an array of all invoices matching the optional filters.

Possible Filters: 

* int *user_id* - Member ID
* date *start_date* - Only orders after or on this date will be returned
* date *end_date* - Only orders before or on this date will be returned
* int *id* - The charge ID
* string *amount* - The amount of the charge
* boolean *subscription_id* - Only charges linked to this subscription
* int *card_last_four* - Last 4 digits of credit card
* int *offset* - Offsets the database query
* int *limit* - Limits the number of results returned
* string *sort* - Column used to sort the results
* string *sort_dir* - Used when a sort param is supplied.  Possible values are asc and desc
* int *user_id* - Member ID
* date *start_date* - Only orders after or on this date will be returned
* date *end_date* - Only orders before or on this date will be returned
* int *id* - The charge ID
* string *amount* - The amount of the charge
* boolean *subscription_id* - Only charges linked to this subscription
* int *card_last_four* - Last 4 digits of credit card
* int *offset* - Offsets the database query
* int *limit* - Limits the number of results returned
* string *sort* - Column used to sort the results
* string *sort_dir* - Used when a sort param is supplied.  Possible values are asc and desc

Arguments:

* `$filters` - An optional array of filters for the search.
* `$counting` - Set to TRUE to receive the total number of matching invoices

Returns each invoice as an array with the following keys:

* *id*
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
* *subscription_id*
* *tax_name*
* *tax_paid*
* *tax_rate*
* *shipping_id*
* *shipping_name*
* *shipping_charge*
* *order_details_id*
* *billing_address* (array)