# Content Model

This model is the gateway to retrieving and searching all of the content published via the publish module.  This includes content of content type created and managed at *Publish > Content Types* in the control panel.

Of course, you can also publish and manage existing content.

## Initialization

```
$this->load->model('publish/content_model');
// methods now available at $this->content_model->x();
```

## Method Reference

## `int new_content (int $type , int $user [, string $title = '' [, string $url_path = '' [, array $topics = array() [, array $privileges = array() [, string $publish_date = FALSE [, array $custom_fields = array()]]]]]])`

Publish new content and get the returned `$content_id`.  Technically, almost all the fields are optional.  However, all standard content (i.e., with a Title and URL Path and potentially with topics, member group privileges, and a publish date) will use all of the function parameters.

The `$custom_fields` array is a simple key => value array likely generated automatically by [the form builder library](/docs/developers/reference/form_builder_library.md).  However, if you are creating new content outside of the control panel, you can just as easily create this array yourself as long as each key is equal to the name of a custom field comprising the content type's fields.

## `void update_content (int $content_id [, string $title = '' [, string $url_path = '' [, array $topics = array() [, array $privileges = array() [, string $publish_date = FALSE [, array $custom_fields = array()]]]]]])`

Update an existing piece of content.

## `boolean delete_content (int $content_id)`

Delete an existing piece of content.

## `int add_hit (int $content_id)`

Add a traffic hit to a piece of content.

## `int get_content_id (string $url_path)`

Retrieve the content ID for a piece of content when given the URL path.  This is used in the content controller.

## `int count_content ( [array $filters = array()])`

Count all content items in the database matching the optional filters.  These filters are identical to those of `get_contents()`.  In fact, this method is a wrapper for that method, but uses a much more optimize method of obtaining a simple count number.

## `array get_content (int $content_id [, boolean $allow_future = FALSE])`

Retrieve an array for a single piece of content from the database.  By default, content with publish dates in the future will return `FALSE`.

## `array get_contents ( [array $filters = array() , boolean $counting = FALSE])`

Retrieve one or more content items in an array as filtered with the optional filters.

Possible Filters: 

* date *start_date* - Only content after this date
* date *end_date* - Only content before this date
* string *author_like* - Only content created by this user (by username, text search)
* int *type* - Only content of this type
* string *title*
* int *id*
* array *topic* - Single topic ID or array of multiple topics
* array *author* - Single author ID or array of multiple authors
* string *keyword* - A keyword to search across a content type (i.e., it requires `$filters['type']`).  This is a full text search across all of the content fields available.  If selected, each element returns a *relevance* key, as well.
* string *date_format* - The format to return dates in
* boolean *allow_future* - Allow content from the future?  Default: FALSE.
* string *sort*
* string *sort_dir*
* int *limit*
* int *offset*

> You may also send any of your content types' *custom fields* as a filter, referencing the "system_name" of the custom field.  For example, if
you have a field called "Program Type", you can send a filter called `program_type`.  If your custom field conflicts
with another MySQL column name, you can send it using your content type's table as a prefix.  For example: `programs.program_type`.
To pass the filter as a search parameter, wrap it in "%" (e.g., `%test%`).

Each piece of content has the following data:

* *id*
* *link_id* - Corresponding with the [Link model](/docs/developers/reference/link_model.md)
* *date* - Publish date
* *modified_date* - Date last modified
* *author_id*
* *author_username*
* *author_first_name*
* *author_last_name*
* *author_email*
* *type_id*
* *type_name*
* *is_standard* - Returns TRUE if the content is a "standard" content type with a title, URL path, privileges, etc, else FALSE.
* *title*
* *url_path*
* *url*
* *privileges* - Array of member groups who can access this content.
* *topics* - Array of topics that the group belongs to.
* *template* - Template file used to display this content (based on the content type, not this individual piece of content).
* *hits* - Number of hits
* *relevance* - If you used the `keyword` filter to do a search, each returned piece of content will have this key.
* All custom field data is returned here as well, though this is custom to each content type.  They are returned with each custom field's "name" attribute as the key in this array.