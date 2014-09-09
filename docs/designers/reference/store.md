# Store

Within the store, we have *products* and *collections* that can all be flexibly retrieved and displayed.  We also have the store shopping cart page which can be styled and modified as you wish.

## Important URL's

* `/store` - The main store page showing parent collections and products without collections.
* `/store/c/12345` - An example link to a store collection with ID "12345".
* `/store/p/12345` - An example link to a store product with ID "12345".
* `/store/cart` - The active user's shopping cart

## Templates

* `store_listing.thtml` - The main store browsing template displaying product and collection listings.  Also displays the store's main page.
* `store_product.thtml` - Displays an individual product
* `store_cart.thtml` - Displays the active user's shopping cart

## Product Template Variables

The following variables are available within the `store_product.thtml` template in the form of `{$variable}`, within `store_listing.thtml` as `{$products.variable}`, and also within any template tags (documented below) in the form of `{$array.variable}`.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$id}`</td>
			<td>The product ID.</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute URL to the product page (e.g., http://www.example.com/store/p/12345).</td>
		</tr>
		<tr>
			<td>`{$url_path}`</td>
			<td>The relative URL to the product page (e.g., /store/p/12345).</td>
		</tr>
		<tr>
			<td>`{$quick_add_to_cart_url}`</td>
			<td>A link which adds the product to the cart and redirects the user to their cart.</td>
		</tr>
		<tr>
			<td>`{$collections}`</td>
			<td>An array of collection ID's for collections the product belongs to.</td>
		</tr>
		<tr>
			<td>`{$name}`</td>
			<td>The product's name.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The product's description</td>
		</tr>
		<tr>
			<td>`{$price}`</td>
			<td>The price of the product, without a currency symbol, but formatted properly (e.g., "45.00").</td>
		</tr>
		<tr>
			<td>`{$member_tiers}`</td>
			<td>An array containing member group pricing, if indicated.  Each array key is a member group ID # and its corresponding value is their special price.</td>
		</tr>
		<tr>
			<td>`{$weight}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$requires_shipping}`</td>
			<td>Set to TRUE if the product requires a shipping address, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$track_inventory}`</td>
			<td>Set to TRUE if the product's inventory is tracked, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$inventory}`</td>
			<td>The current number of this product in stock, if being tracked, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$inventory_allow_oversell}`</td>
			<td>Set to TRUE if the product can be sold at 0 inventory, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$sku}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$is_taxable}`</td>
			<td>Set to TRUE if taxes apply to this product, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$is_download}`</td>
			<td>Set to TRUE if the product is downloadable, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$download_name}`</td>
			<td>The filename of the product's download, if it is a downloadable product.</td>
		</tr>
		<tr>
			<td>`{$download_fullpath}`</td>
			<td>The fullpath to the product download file, relative to the Caribou root.  Usable in `{protected_link}` calls.</td>
		</tr>
		<tr>
			<td>`{$download_size}`</td>
			<td>The filesize of the product's download, in bytes.</td>
		</tr>
		<tr>
			<td>`{$feature_image}`</td>
			<td>The full server path to the product's feature image, if available.</td>
		</tr>
		<tr>
			<td>`{$feature_image_url}`</td>
			<td>An absolute URL for the product's feature image, if available.</td>
		</tr>
		<tr>
			<td>`{$images}`</td>
			<td>An array of all of the product images, if available.</td>
		</tr>
	</tbody>
</table>

### Displaying Product Images

Within the product page (typically the `store_product.thtml` template, the `{$images}` holds an array of all product images (or `FALSE` if none exist).  It can be iterated through like this:

```
{if $images}
	<div class="images">
		<ul>
			{foreach $images as $image}
				{assign var="image_id" value=$image.id}
				<li><a href="{$image.url}"><img src="{thumbnail path=$image.path height="50" width="50"}" alt="click to enlarge" title="click to enlarge" /></a>
			{/foreach}
		</ul>
	</div>
{/if}
```

## Collection Template Variables

The following variables are available when viewing a specific collection in `store_listing.thtml`.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$id}`</td>
			<td>The collection ID.</td>
		</tr>
		<tr>
			<td>`{$name}`</td>
			<td>The collection name.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The collection description.</td>
		</tr>
		<tr>
			<td>`{$parent}`</td>
			<td>The collection's parent collection ID, if it has a parent.</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute URL for the collection store browsing page.</td>
		</tr>
	</tbody>
</table>

## Cart Template Variables

When viewing the cart via the `store_cart.thtml` template, or when using the `{cart}` template plugin (documented below), the following variables are available for each product/subscription.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Variables</td>
		</tr>
		<tr>
			<td class="variable_name">Variable</td>
			<td class="variable_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>`{$name}`</td>
			<td>The product/subscription name.</td>
		</tr>
		<tr>
			<td>`{$id}`</td>
			<td>The product/subscription ID.</td>
		</tr>
		<tr>
			<td>`{$qty}`</td>
			<td>How many of this product is in the cart?</td>
		</tr>
		<tr>
			<td>`{$is_subscription}`</td>
			<td>Set to TRUE if the product is a subscription, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$price}`</td>
			<td>The price of an individual one of these product(s).  Use `{$subtotal}` for the total price of this product in the current cart.</td>
		</tr>
		<tr>
			<td>`{$recurring_price}`</td>
			<td>If this is a subscription, this will be the recurring charge going forward.</td>
		</tr>
		<tr>
			<td>`{$free_trial}`</td>
			<td>If this is a subscription, this will be the number of days offered as a free trial, if applicable.</td>
		</tr>
		<tr>
			<td>`{$free_trial_no_billing}`</td>
			<td>Set to TRUE if the user has a free trial without entering payment information.</td>
		</tr>
		<tr>
			<td>`{$interval}`</td>
			<td>If this a subscription, this is the number of days between subscription charges.</td>
		</tr>
		<tr>
			<td>`{$occurrences}`</td>
			<td>This is the total number of occurrences for this subscription.</td>
		</tr>
		<tr>
			<td>`{$renew_subscription_id}`</td>
			<td>If this is a subscription and we are renewing a subscription, the old subscription is stored here.</td>
		</tr>
		<tr>
			<td>`{$weight}`</td>
			<td>The product's weight.</td>
		</tr>
		<tr>
			<td>`{$requires_shipping}`</td>
			<td>Set to TRUE if the product requires a shipping address, else FALSE.</td>
		</tr>
		<tr>
			<td>`{$subtotal}`</td>
			<td>The total cost of the product, to be charged today.</td>
		</tr>
		<tr>
			<td>`{$remove_link}`</td>
			<td>An absolute URL link which, when accessed, removes this product from the user's cart and redirects them back to the cart.</td>
		</tr>
	</tbody>
</table>

## Example Product Page Template

> This page uses Shadowbox to display the images, and includes the Shadowbox code [using the "head_includes" block method of inclusion](/docs/designers/includes.md).

```
{extends file="layout.thtml"}
{block name="title"}
{$name} - {$smarty.block.parent}
{/block}
{block name="head_includes"}
	<link rel="stylesheet" type="text/css" href="{url path="themes/_common/shadowbox/shadowbox.css"}" />
	<script type="text/javascript" src="{url path="themes/_common/shadowbox/shadowbox.js"}"></script>
	<script type="text/javascript">
		Shadowbox.init();
	</script>
	{$smarty.block.parent}
{/block}
{block name="content"}
	<h1>{$name}</h1>
	
	<div class="product">
		{if $images}
			<div class="images">
				<a rel="shadowbox[product_images]" href="{$feature_image_url}" class="feature_image"><img src="{thumbnail path=$feature_image height="165" width="165"}" alt="{$name}" /></a>
				<ul>
					{foreach $images as $image}
						{* we don't want to show the feature image twice *}
						{if $image.path != $feature_image}
							{assign var="image_id" value=$image.id}
							<li><a rel="shadowbox[product_images]" href="{$image.url}"><img src="{thumbnail path=$image.path height="50" width="50"}" alt="click to enlarge" title="click to enlarge" /></a>
						{/if}
					{/foreach}
				</ul>
			</div>
		{/if}
		<div class="description {if !$images}full{/if}">
			<div class="cart_form">
				{if $track_inventory and !$inventory_allow_oversell and $inventory < 1}
					<p>Unfortunately, this product is sold out.  Please check back again later.</p>
				{else}
					{setting name="currency_symbol"}{$price}
					
					<form method="post" action="{url path="store/add_to_cart"}">
						<input type="hidden" name="product_id" value="{$id}" />
						
						{if $options}
							<ul>
							{foreach $options as $option}
								<li>
									<select name="option_{$option}">
										{if $product_options[$option]['options']}
											{foreach $product_options[$option]['options'] as $value}
												<option value="{$value.label}">{$value.label}{if $value.price != "0"} ({setting name="currency_symbol"}{money_format value=$value.price}){/if}</option>
											{/foreach}
										{/if}
									</select>
								</li>
							{/foreach}
							</ul>
						{/if}
						
						Quantity: <input type="text" style="width: 40px" name="quantity" value="1" />
						<input type="submit" class="button" name="add_to_cart" value="Add to Cart" />
					</form>
				{/if}
			</div>
			
			{$description}
		</div>
	</div>
{/block}
```

## Example Store Listing Template

List collections and products, like categories.

```
{extends file="layout.thtml"}
{block name="title"}
{if $title}{$title}{elseif $collection}{$collection.name}{/if} - {$smarty.block.parent}
{/block}
{block name="content"}
	<h1>Browse Our Store</h1>
	
	<div class="store">
		{if $collections}
			<ul class="collections">
			{foreach $collections as $collection}
				<li>
					<a class="name" href="{$collection.url}">{$collection.name}</a>
					{if $collection.description}<div class="description">{$collection.description}</div>{/if}
				</li>
			{/foreach}
			</ul>
		{/if}
		
		{if $products}
			<ul class="products">
			{foreach $products as $product}
				<li>
					{if $product.feature_image}
						<a href="{$product.url}"><img src="{thumbnail path=$product['feature_image'] width="150" height="150"}" alt="{$product.name}" /></a>
					{/if}
					<a class="name" href="{$product.url}">{$product.name}</a>
					<div class="price">{setting name="currency_symbol"}{$product.price}</div>
				</li>
			{/foreach}
			</ul>
		{/if}
	</div>
	
	{if !$collections and !$products}
	<p>This collection is empty.  Add some categories and/or products to populate this page.</p>
	{/if}
{/block}
```

## Template Plugins

[tag]{cart}[/tag]

Display all of the products/subscriptions in the active user's cart.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "item" returns an array with keys like `{$item.subtotal}`.</td>
		</tr>
	</tbody>
</table>

Usage:

```
You have the following items in your cart:

<ul class="cart">
{cart var="item"}
	<li>{$item.name} ({setting name="currency_symbol"}{$item.$price} each) - {setting name="currency_symbol"}{$item.$subtotal}</li>
{/cart}
</ul>
```

[tag]{cart_total}[/tag]

Return the total price of the user's cart (without a prefixed currency symbol).

Usage:

```
Total Cart Value: ${cart_total}
```

[tag]{cart_items}[/tag]

Displays the total number of items in the cart.

Usage:

```
You have {cart_items} items in your <a href="{url path="store/cart"}">shopping cart</a>.
```

[tag]{collections}[/tag]

Retrieves store collections, with optional parameters.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "collection" returns an array with keys like `{$collection.name}`.</td>
		</tr>
		<tr>
			<td>parent</td>
			<td>No</td>
			<td>Specify a collection by collection ID, or a series of collection like "4|5|6" to pull only products from those collection.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify a collection ID to pull specifically.</td>
		</tr>
	</tbody>
</table>


Example:

```
<div>
	Our store categories:
	<ul>
		{collections var="collection"}
		<li><a href="{$collection.url}">{$collection.name}</a> - {$collection.description}</li>
		{/collections}
	</ul>
</div>
```

[tag]{has_cart}[/tag]

Only show the content between the tags if the user has 1+ items in their cart.

Usage:

```
{has_cart}
<a href="{url path="store/cart"}">You have {cart_items} in your cart.</a>
{/has_cart}
```

[tag]{no_cart}[/tag]

Only show the content between the tags if the user doesn't have any items in their cart.

Usage:

```
{no_cart}
<a href="{url path="store"}">You don't have any items in your shopping cart - get shopping!.</a>
{/no_cart}
```

[tag]{products}[/tag]

Retrieve products based on a number of set parameters, or a fulltext keyword search.

<table>
	<thead>
		<tr class="title">
			<td colspan="3">Parameters</td>
		</tr>
		<tr>
			<td class="parameter_name">Variable</td>
			<td class="is_required">Required?</td>
			<td class="parameter_description">Description</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>var</td>
			<td>Required</td>
			<td>Specify the name for the returned variable array (e.g., "prod" returns an array with keys like `{$prod.price}`.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify a product ID to pull specifically.</td>
		</tr>
		<tr>
			<td>collection</td>
			<td>No</td>
			<td>Specify a collection by collection ID, or a series of collection like "4|5|6" to pull only products from those collection.</td>
		</tr>
		<tr>
			<td>keyword</td>
			<td>No</td>
			<td>Perform a fulltext search on the products database and retrieve only matching results.  If used, a `{$var.relevance}` variable will also be available for each returned item.</td>
		</tr>
		<tr>
			<td>sort</td>
			<td>No</td>
			<td>Specify a database field to sort the results by (e.g., "products.product_name") (default: products.product_id).</td>
		</tr>
		<tr>
			<td>sort_dir<>
			<td>No</td>
			<td>Specify whether to sort the results in ascending ("ASC") or descending ("DESC") order (default: DESC).</td>
		</tr>
		<tr>
			<td>limit</td>
			<td>No</td>
			<td>How many items should be retrieved? (default: unlimited)</td>
		</tr>
		<tr>
			<td>offset</td>
			<td>No</td>
			<td>If set, only content records after this number will be retrieved (e.g., passing "6" will return all records from the 7th record onward).</td>
		</tr>
	</tbody>
</table>


Example - sorting by price to get the cheapest products in a specific collection:

```
<div>
	Our 5 cheapest products in the Women's collection:
	<ul>
		{products var="prod" collection="5" sort="product_price" sort_dir="ASC"}
		<li>{$prod.name} - {$prod.price}</li>
		{/products}
	</ul>
</div>
```