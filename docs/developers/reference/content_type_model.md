# Content Type Model

Create, update, retrieve, and delete content types in Hero.

There are a few important notes to consider when creating and updating content types:

* *Standard* content types have content that are likely web "pages", such as blog posts, static pages, news articles, biographies, etc.  Standard content types will automatically give you the following fields when publishing content: "Title", "URL Path", "Topics", and a "Publish Date" (for delayed publishing).  *Non-standard* content will give you an "empty" content type in that the system will not make any assumptions about what fields you need.  This is more appropriate for database type content, such as a list of local schools that you want to appear in a table on a single web page.  When editing templates, you can [use the {content} template tag](/docs/designers/reference/publish.md) to retrieve these content items very easily.
* By marking a content type has a *Module* in `new_content_type()`, you are specifying that you do not want to display a typical publishing form and listing at *Publish* in the control panel; you will be creating a module that will manage this content differently.  This allows developers to tap into the power of the content database (and related models like the [link model](/docs/developers/reference/link_model.md)) but have a separate control panel manager.
* It is only an option to specify the *template* of the content type for standard content types.  This template is triggered when the user visits a URL path specified by a content item.
* The *base URL* for content types (again, only for standard content) defines the root of all URL paths for this content.  For example, if you have a "Press Release" content type, you may want all URL's to begin with "press_release/" or "pr/".
* *Privileged* content will allow you to specify membership group restrictions upon publishing.  This is at the heart of automated subscription paywalls and private membership-only content.

## Initialization

```
$this->load->model('publish/content_type_model');
// methods now available at $this->content_type_model->x();
```

## Method Reference

## `int new_content_type (string $name [, boolean $is_standard = FALSE [, boolean $is_privileged = FALSE [, boolean $is_module = FALSE [, string $template = 'content.thtml' [, string $base_url = '']]]]])`

Create a new content type.

## `void update_content_type (int $content_type_id , string $name [, boolean $is_standard = FALSE [, boolean $is_privileged = FALSE [, string $template = 'content.thtml' [, string $base_url = '']]]])`

Update an existing content type.

## `boolean delete_content_type (int $content_type_id)`

Delete a content type.

## `boolean build_search_index (int $content_type_id)`

This method rebuilds the database search index for a content type after adding/modifying/delete fields from the content type's custom field group.

## `array get_content_type (int $content_type_id)`

Retrieve an array of a single content type, in the same format as `get_content_types()`.

## `void get_content_types ( [array $filters = array()])`

Retrieve an array of one or more content types based on optional filters.

Possible Filters: 

* int *id*
* boolean *is_standard*
* boolean *is_module*

Each returned content type array has the following data:

* *id*
* *name*
* *singular_name* - Automatically inflected to the singular (e.g., "Articles" becomes "Article")
* *system_name* - The database table name
* *is_privileged*
* *is_standard*
* *template*
* *custom_field_group_id*
* *base_url*