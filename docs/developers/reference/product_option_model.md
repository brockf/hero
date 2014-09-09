# Product Option Model

Product options are configurable options (e.g., "Size", "Color") for products, selected by the user before the product is added to their cart.  Product options can be linked to a particular product or they can be shared amongst many products (i.e., all shirts share the same "Color" option instead of re-creating this option for each particular product).

## Initialization

```
$this->load->model('store/product_option_model');
// methods at $this->product_option_model->x();
```

## Method Reference

## `int new_option (string $name [, array $values = array() [, boolean $save = FALSE]])`

Create a new product option.

Arguments:

* `$name` - The name of the option (e.g, "Color").
* `$values` - An array of options, each an array with keys "label" and "price"
* `$save` - Set to TRUE to make this product option available for use by other products in the control panel.

## `array get_option (int $option_id)`

Return an array of a particular product option, in the same format as `get_options()`.

## `void delete_option (int $option_id)`

Delete a product option.

## `array get_options ( [array $filters = array()])`

Retrieve one or more product option arrays, based on optional filters.

Possible Filters: 

* int *id*
* int *shared* - Is it an option shared with other products?  (1 or 0)

Each returned product option has the following data:

* *id*
* *name*
* *options* - The product options array
* *shared* - TRUE if shared amongst other products