# Products Model

Manage products and product images.  This model also has helper methods like `get_price()` which calculates a product's price based on the product ID, the logged-in user's member groups (if logged in), and selected product options.

## Initialization

```
$this->load->model('store/products_model');
// now methods are available at $this->products_model->x();
```

## Method Reference

## `float get_price (int $product_id [, array $selected_options = array()])`

Calculate the price for a product based on the product's ID (i.e., it's price), the potentially logged-in user's member groups, and the selected options for the product passed as an array of product_option_names => selected_values.

```
$options = array(
				* *colour*
				'size' => 'large'
			);
			
			
$price = $this->products_model->get_price(1050, $options);

echo 'Please pay us $' . money_format("%!i", $price) . '.';
```

## `array get_custom_fields ()`

Retrieve all product custom fields.  This is essentially a wrapper for the [custom field model](/docs/developers/reference/custom_fields_model.md)'s method of the same name, except that it knows which custom field group is assigned to products (a stored setting).

## `int add_image (int $product_id , string $filename)`

Add an image to a product.

Arguments:

* `$product_id` - Product ID
* `$filename` - The filename of the file in `/writeable/product_images/`

## `void remove_image (int $image_id)`

Remove an image from a product.

## `boolean make_feature_image (int $image_id)`

Make the selected image the feature image for a product (and remove any other image from feature image status).

## `void images_reset_order (int $product_id)`

Reset the image order for a specific product.  This is a useful method to use before `image_update_order()` when sorting product images.

## `void image_update_order (int $field_id , int $new_order)`

Set the order of a particular image within a product's image gallery.  `$new_order` corresponds to the order of the image.  They will be displayed in ascending order.

## `string|boolean validation ()`

Validate the current POST data for a product submission.  If successful, it returns TRUE.  Otherwise, it returns an HTML-formatted string of errors.

## `int new_product (string $name , string $description [, array $collections = array() [, float $price = '1' , int $weight [, boolean $requires_shipping = FALSE [, boolean $track_inventory = FALSE , float $starting_inventory [, boolean $allow_oversell = FALSE [, string $sku = '' [, boolean $is_taxable = FALSE [, array $member_tiers = array() [, boolean $is_download = FALSE [, string $download_name = '' , int $download_size [, int $promotion = '' [, array $custom_fields = array() [, array $product_options = array()]]]]]]]]]]]]])`

Create a new product in the database.

Parameter Notes:

* `$name` - Product name
* `$description` - Product description
* `$collections` - Array of collection ID's (default: array())
* `$price` - Product price (e.g., "5.00") (default: 1)
* `$weight` - Product weight in default units (default: 0)
* `$requires_shipping` - Does it require a shipping address? (default: FALSE)
* `$track_inventory` - Shall we track inventory? (default: FALSE)
* `$starting_inventory` - How many are in stock right now? (default: 0)
* `$allow_oversell` - Should we allow the product to sell at zero stock? (default: FALSE)
* `$sku` - The SKU identifier (default: '')
* `$is_taxable` - Is this product subject to taxes? (default: FALSE)
* `$member_tiers` - A "[group]" => "[price]" array of member tiered pricing (default: array())
* `$is_download` - Is this a downloadable product? (default: FALSE)
* `$download_name` - Filename for the download in /writeable/product_files/ (default: '')
* `$download_size` - Total size of the file, in KB (default: 0)
* `$promotion` - Shall we put purchasers into a usergroup? (default: '')
* `$custom_fields` - Any custom field data (default: array())
* `$product_options` - Array of product_options ID's (default: array())

## `boolean update_product (int $product_id , string $name , string $description [, array $collections = array() [, float $price = '1' , int $weight [, boolean $requires_shipping = FALSE [, boolean $track_inventory = FALSE , float $starting_inventory [, boolean $allow_oversell = FALSE [, strig $sku = '' [, boolean $is_taxable = FALSE [, array $member_tiers = array() [, boolean $is_download = FALSE [, string $download_name = '' , int $download_size [, int $promotion = '' [, array $custom_fields = array() [, array $product_options = array()]]]]]]]]]]]]])`

Update an existing product in the database.

Parameter Notes:

* `$product_id` - The product ID to update
* `$name` - Product name
* `$description` - Product description
* `$collections` - Array of collection ID's (default: array())
* `$price` - Product price (e.g., "5.00") (default: 1)
* `$weight` - Product weight in default units (default: 0)
* `$requires_shipping` - Does it require a shipping address? (default: FALSE)
* `$track_inventory` - Shall we track inventory? (default: FALSE)
* `$starting_inventory` - How many are in stock right now? (default: 0)
* `$allow_oversell` - Should we allow the product to sell at zero stock? (default: FALSE)
* `$sku` - The SKU identifier (default: '')
* `$is_taxable` - Is this product subject to taxes? (default: FALSE)
* `$member_tiers` - A "[group]" => "[price]" array of member tiered pricing (default: array())
* `$is_download` - Is this a downloadable product? (default: FALSE)
* `$download_name` - Filename for the download in /writeable/product_files/ (default: '')
* `$download_size` - Total size of the file, in KB (default: 0)
* `$promotion` - Shall we put purchasers into a usergroup? (default: '')
* `$custom_fields` - Any custom field data (default: array())
* `$product_options` - Array of product_options ID's (default: array())

## `int get_product_id (string $url_path)`

Return a product ID from a URL path.  This is used in the store controller.

## `array get_product (int $product_id)`

Return data for a particular product from an ID, in the same format as `get_products()`.

## `array get_images (int $product_id)`

Retrieve all images for a particular product.

The image array returned has multiple arrays (images in ascending order) with the following keys:

* *id*
* *path* - The local full server path to the file
* *url* - The full URL to the image
* *featured* - TRUE if it is the feature image, else FALSE

## `array get_products ( [array $filters = array()])`

Return an array of products based on optional filters.  This includes a fulltext search if you use the `keyword` filter.

Possible Filters: 

* int *id*
* string *type* - Either "download" or "shippable"
* string *name*
* int *collection*
* float *price*
* string *keyword*
* string *sort* - Field to sort by
* string *sort_dir* - ASC or DESC
* int *limit* - How many records to retrieve?
* int *offset* - Start records retrieval at this record

Each returned product array has the following data:

* *id*
* *url*
* *url_path*
* *quick_add_to_cart_url* - A URL that can be used to quickly add 1 of these products to your cart
* *admin_link*
* *collections* 
* *name*
* *description*
* *price*
* *weight*
* *requires_shipping*
* *track_inventory*
* *inventory*
* *inventory_allow_oversell*
* *sku*
* *is_taxable*
* *member_tiers*
* *is_download*
* *download_name* - Filename of the file
* *download_fullpath* - Full local server path to the download file
* *download_size* - Filesize, in bytes
* *promotion* - Promotion member group after purchase, if available
* *feature_image* - Full local server path to the feature image file
* *feature_image_url* - Full URL to the image file
* *options* - An array of product option ID's, if this product has product options
* *relevance* (if a *keyword* filter was specified)

## `void delete_product (int $product_id)`

Delete an existing product.

## `int knock_inventory (int $product_id)`

Reduce a product's inventory by 1 (after purchase).