# Blog Model

Blogs are essentially preconfigured listings of content that can be created and configured in the control panel at *Publish > Blogs*.  They are created based on author filters, topic filters, and a content type option.

Retrieve and search blog listings/archives in Hero.

## Initialization

```
$this->load->model('blogs/blog_model');
// methods available at $this->blog_model->x();
```

## Method Reference

[method]int new_blog (int $content_type_id , string $title , string $url_path , string $description [, array $filter_author = array() [, array $filter_topic = array() [, string $summary_field = FALSE [, string $sort_field = FALSE [, string $sort_dir = FALSE [, boolean $auto_trim = FALSE [, string $template = 'blog.html' [, int $per_page = '25' [, array $privileges = array()]]]]]]]]])[/method]

Create a new blog/archive listing with these parameters.

[method]void update_blog (int $blog_id , int $content_type_id , string $title , string $url_path , string $description [, array $filter_author = array() [, array $filter_topic = array() [, string $summary_field = FALSE [, string $sort_field = FALSE [, string $sort_dir = FALSE [, boolean $auto_trim = FALSE [, string $template = 'blog.html' [, int $per_page = '25' [, array $privileges = array()]]]]]]]]])[/method]

Update an existing blog/archive with these parameters.  Specify the blog with `$blog_id`.

[method]boolean delete_blog (int $blog_id)[/method]

Delete an existing blog.

[method]int get_blog_id (string $url_path)[/method]

Return the ID of a blog based on its URL path (used in the blogs controller).

[method]array get_blog_content (int $blog_id [, int $page = 0])[/method]

Retrieve all content (via `content_model->get_contents()`) for a blog.

[method]string get_blog_pagination (int $blog_id , string $base_url [, int $page = 0])[/method]

Retrieve an HTML-formatted string of blog pagination links.  The `$base_url` is the full URL to the blog on which pagination query strings will be built.

[method]array get_blog (int $blog_id)[/method]

Retrieve details about a single blog by ID.

[method]array get_blogs ( [array $filters = array()])[/method]

Retrieve details about a blog or blog based on optional filters.

Possible Filters: 

* int *id*
* int *type*
* string *title*

Each blog will be returned as an array with the following data:

* *id*
* *link_id* - Corresponds to the [link model](/docs/developers/reference/link_model)
* *title*
* *description*
* *filter_authors* - An array of authors to include in the blog.
* *filter_topics* - An array of included topics.
* *type* - The `content_type_id` for the content shown in this blog.
* *type_name* - The name of that content type.
* *summary_field* - The field name of the summary field for content retrieved.
* *url* - Full URL to blog
* *url_path* - URL path to blog
* *auto_trim* - Set to TRUE if the summary text is trimmed automatically for length.
* *template* - The template used to display this blog.
* *sort_field*
* *sort_dir*
* *privileges* - An array of member groups that can view this blog.
* *per_page* - How many content items to show per page?

```
// get all blogs that show content_type_id #4 (e.g., Articles)
$blogs = $this->blog_model->get_blogs(array('type' => 4));
```