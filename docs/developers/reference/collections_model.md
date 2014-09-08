# Collections Model

Product collections are flexible product categories.  Products can belong to more than one collection.  Collections are tiered so that there can be sub-collections to parent collections at infinite levels.

## Initialization

```
$this->load->model('store/collections_model');
// methods now at $this->collections_model->x();
```

## Method Reference

[method]int new_collection (string $name [, string $description = '' , int $parent [, array $custom_fields = array()]])[/method]

Create a new product collection.

Arguments:

* `$name`
* `$description`
* `$parent` - Possible parent collection ID, else 0.
* `$custom_fields` - Custom field data for this collection, if you have collection custom fields.

[method]void update_collection (int $collection_id , string $name [, string $description = '' , int $parent [, array $custom_fields = array()]])[/method]

Update an existing collection.

[method]void delete_collection (int $collection_id)[/method]

Delete an existing collection.  Note:  Products in this collection will not be deleted.

[method]array get_tiered_collections ( [array $filters = array()])[/method]

Retrieve a one-dimensional array of all collections with keys equal to their `collection_id` and values in the form of:

* Parent Collection
* Parent Collection > Child Collection
* Parent Collection > Child Collection > Grandchild
* Other Parent > Child Collection
* Other Parent > Another Child
* etc.

This is a resource-expensive function so call sparingly, but it's a good way to get a list of collections organized and ready for a select dropdown topic selection.

The list of collections can be filtered with the same optional filters as `get_collections()`.

[method]array get_collection (int $collection_id)[/method]

Retrieve data for a single collection, in the same format as `get_collections()`.

[method]array get_collections ( [array $filters = array() [, boolean $any_status = FALSE]])[/method]

Retrieve an array of one or more product collections, based on optional filters.  Also, with `$any_status`, you can choose whether you want to return even deleted collections.

Possible Filters:

* *id*
* *parent* - ID of parent collection
* *name* - Search for a collection name

Each collection returns an array with the following data:

* *id*
* *url*
* *name*
* *description*
* *parent*

[method]array get_custom_fields ()[/method]

Retrieve all product collection custom fields.  This is essentially a wrapper for the [custom field model](/docs/developers/reference/custom_fields_model)'s method of the same name, except that it knows which custom field group is assigned to product collections (a stored setting).
