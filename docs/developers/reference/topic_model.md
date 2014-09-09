# Topic Model

Topics are essentially categories for content.  Each standard content item can be in none, one, or many topics.  These topics may guide their display on the frontend, a list of related content, or act as simple tags for the content.  What the designer does with this data is entirely up to them.

This model allows for topics to be created, updated, and deleted.  It also allows developers to retrieve topics either as normal arrays of data or as a long list of topics with items like "Parent Topic > Child Topic > Grandchild" that would be suitable for a select dropdown or listing of all topics.

## Initialization

```
$this->load->model('publish/topic_model');
// methods now at: $this->topic_model->x();
```

## Method Reference

## `int new_topic (string $name [, string $description = '' , int $parent = 0]])`

Create a new topic.

## `boolean update_topic (int $topic_id , string $name [, string $description = '' , int $parent])`

Update an existing topic.

## `void delete_topic (int $topic_id)`

Delete a topic.  Note: This will not delete content in the topic.

## `array get_tiered_topics ( [array $filters = array()])`

Retrieve a one-dimensional array of all topics with keys equal to their `topic_id` and values in the form of:

* Parent Topic
* Parent Topic > Child Topic
* Parent Topic > Child Topic > Grandchild
* Other Parent > Child Topic
* Other Parent > Another Child
* etc.

This is a resource-expensive function so call sparingly, but it's a good way to get a list of topics organized and ready for a select dropdown topic selection.

The list of topics can be filtered with the same optional filters as `get_topics()`.

## `array get_topic (int $topic_id)`

Retrieve data for a single topic in the same format as `get_topics()`.

## `array get_topics ( [array $filters = array()])`

Retrieve topic data as arrays, as filtered by an optional filters array.

Possible Filters: 

* int *parent* - The topic_id of a parent topic.
* int *id*
* string *name*
* string *sort* - Sort column
* string *sort_dir* - Sort direction
* int *limit* - Result limit
* int *offset* - Result offset

Each returned array has the following keys:

* *id*
* *name*
* *description*
* *parent* - (optional) May have a parent topic_id listed here, if this topic is a child.
