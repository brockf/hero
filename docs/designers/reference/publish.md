# Content

*Content* refers to all custom content types that [you have setup under the Publish tab](/docs/publishing/content.md) in the control panel.

## Templates

All standard content types have a *URL Path* field which allows you to map a URL directly to a web page holding the content you have posted.  This is suitable for content that requires its own page, like news articles, blog posts, weather reports, etc.

When the user goes to a URL that is mapped to content, that content type's template is triggered.  The content type's template can be changed to a new template but, by default, it is `content.thtml`.

Certain variables are made available within this template automatically.

## Template Variables

The following variables are available within a content template in the form of `{$variable}` and also within any template tags (documented below) that call content in the form of `{$array.variable}`.

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
			<td>The content ID number.</td>
		</tr>
		<tr>
			<td>`{$title}`</td>
			<td>The title of the content.</td>
		</tr>
		<tr>
			<td>`{$date}`</td>
			<td>The date the content was first published.</td>
		</tr>
		<tr>
			<td>`{$modified_date}`</td>
			<td>The date the content was last modified.</td>
		</tr>
		<tr>
			<td>`{$author_id}`</td>
			<td>The member_id of the publishing author.</td>
		</tr>
		<tr>
			<td>`{$author_username}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$author_email}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$author_first_name}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$author_last_name}`</td>
			<td></td>
		</tr>
		<tr>
			<td>`{$type_id}`</td>
			<td>The content_type_id for the content's type.</td>
		</tr>
		<tr>
			<td>`{$type_name}`</td>
			<td>The content type's name.</td>
		</tr>
		<tr>
			<td>`{$url_path}`</td>
			<td>The relative path to the content (e.g., /blog/my_blog_post)</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute path to the content (e.g., http://www.example.com/blog/my_blog_post).</td>
		</tr>
		<tr>
			<td>`{$topics}`</td>
			<td>An array of all the topic ID's the content belongs to, else FALSE if none.</td>
		</tr>
		<tr>
			<td>`{$hits}`</td>
			<td>Number of views and content loads for this content.</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The filename of the template file used to display this content.</td>
		</tr>
		<tr>
			<td>`{$privileges}`</td>
			<td>An array of member groups that have access to view this content.  Set to FALSE if the content is public.</td>
		</tr>
		<tr>
			<td colspan="2">All *custom fields* for the content are accessible just like the other variables, with the *system name* of the custom field as the variable name (e.g., `{$my_custom_field}`).</td>
		</tr>
	</tbody>
</table>

## Template Plugins

[tag]{content}[/tag]

Retrieve or search your content database based on a number of function parameters.  Content retrieved is limited to be of one content type.

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
			<td>Specify the name for the returned variable array (e.g., "article" returns an array with keys like `{$article.title}`.</td>
		</tr>
		<tr>
			<td>type</td>
			<td>Required if "id" is not passed.</td>
			<td>Either the content type ID or system name of the content type to pull from (e.g., "4", or "blog_posts").</td>
		</tr>
		<tr>
			<td>id</td>
			<td>Required if "type" is not passed.</td>
			<td>The content ID for a specific piece of content to retrieve.</td>
		</tr>
		<tr>
			<td>topic</td>
			<td>No</td>
			<td>Specify a topic by topic ID, or a series of topics like "4|5|6" to pull only content from those topics.</td>
		</tr>
		<tr>
			<td>keyword</td>
			<td>No</td>
			<td>Perform a fulltext keyword search of content and retrieve only matching items.  If passed, each returned content item will have an additional variable, `{$var.relevance}`.</td>
		</tr>
		<tr>
			<td>sort</td>
			<td>No</td>
			<td>Specify a database field to sort the results by (e.g., "content.content_date", or "blog_posts.current_music") (default: content.content_date).</td>
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
		<tr>
			<td><b>any custom field</b></td>
			<td>No</td>
			<td>You can use the system name of any custom field as a filter parameter.  For example, if you have a field called "White Horses", you can pass the
			parameter "white_horses" (the system name for that field) and the value you would like to filter by.  These filters are dealt with as a
			<b>search</b> within the field (i.e., if you pass the value "black" for a parameter "color_of_car", any content with
			"color_of_car" including "black" - such as "black_and_white" - will be returned).
		</tr>
	</tbody>
</table>

Example - retrieve the latest 10 items for a content type:

```
{content type="3" var="article" limit="10"}
<p>{$article.title}<br />{$article.summary}</p>
{/content}
```

Example - retrieve one piece of content in its entirety:

```
{content id="431" var="post"}
<h1>{$post.title}</h1>
Custom Field: {$post.custom_field}
Publish Date: {$post.date|date_format: "%b %e, %Y"}

Body:

{$post.body}
{/content}
```

Example - using the system name instead of the numeric type ID:

```
{content type="news_posts" var="post" limit="10"}
<p>{$post.title}<br />{$post.summary}</p>
{/content}
```

Example - retrieve content based on a fulltext keyword search, limited to top 25 results:

```
{content type="docs" var="doc" keyword="php" limit="25" sort="relevance" sort_dir="DESC"}
	<a href="{$doc.url}">{$doc.title}</a> - {$doc.relevance}
{/content}
```

Example - retrieve only parks described as beautiful (custom field search)

```
{content type="parks" var="park" description="beautiful"}
	<a href="{$park.url}">{$park.name}</a> - {$park.description}
{/content}
```

> If you are passing a "limit" parameter and not retrieving any content, you must pass a "sort" parameter with the content table prefixed
on the custom field name.  For example, `{content var="ad" sort="advertisements.advertisements_id" limit="2"}` instead of
`{content var="ad" sort="advertisements_id" limit="2"}`.

When `{content}` calls are made, two new variables become available in your template (they are reset after each function call):

* `{$content_count}` - How many items were returned in your content call.
* `{$content_total_count}` - If you used "limit" or "offset", this variable will tell you how many records match your parameters *in total* (useful for pagination).

Example - using `{$content_total_count}` in paginating an "archives" page for blog posts:

```
{* compute offset *}
{if $smarty.get.p}
{assign var="offset" value=$smarty.get.p}
{/if}
{* end compute offset *}

{content var="article" type="1" limit="25" offset=$offset keyword=$filter_keyword topic=$filter_topic sort=$filter_sort sort_dir=$filter_sort_dir}
	<li>
		<div class="archive_date">
			{$article.date|date_format: "M\.d\.Y"}
		</div>
		
		<a class="title" href="{$article.url}">{$article.title}</a>
	</li>
{/content}

{if $content_total_count == 0}
<p>No archive items match your filters.</p>
{/if}

</ul>

{paginate base_url=$current_url total_rows=$content_total_count per_page=25 variable="p"}
```

[tag]{content_in_topic}[/tag]

Retrieve content, across multiple content types, that belongs to a certain topic (or topics).

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
			<td>Specify the name for the returned variable array (e.g., "article" returns an array with keys like `{$article.title}`.</td>
		</tr>
		<tr>
			<td>topic</td>
			<td>Required</td>
			<td>Specify a topic by topic ID, or a series of topics like "4|5|6" to pull only content from those topics.</td>
		</tr>
		<tr>
			<td>sort</td>
			<td>No</td>
			<td>Specify a database field to sort the results by (e.g., "content.content_date", or "blog_posts.current_music") (default: content.content_date).</td>
		</tr>
		<tr>
			<td>sort_dir<>
			<td>No</td>
			<td>Specify whether to sort the results in ascending ("ASC") or descending ("DESC") order.</td>
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

Example - retrieve the last 10 content items from a topic:

```
<ul class="topic_list">
{content_in_topic var="item" topic="4" limit="10"}
	<li><a href="{$item.url}">{$item.title}</a> (published {$item.date|date_format})</li>
{/content_in_topic}
```

Example - retrieve the first 5 alphabetically-sorted items from multiple topics:

```
<ul class="topic_list">
{content_in_topic var="article" topic="1|3|4" limit="5" sort="content_title" sort_dir="ASC"}
	<li><a href="{$article.url}">{$article.title}</a> (published {$article.date|date_format})</li>
{/content_in_topic}
```

[tag]{topics}[/tag]

Retrieve and display one or more site topics based on the given parameters.

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
			<td>Specify the name for the returned variable array (e.g., "topic" returns an array with keys like `{$topic.name}`.</td>
		</tr>
		<tr>
			<td>parent</td>
			<td>No</td>
			<td>Specify a parent topic so that only direct children of this topic are retrieved.</td>
		</tr>
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify a specific topic - by ID - to retrieve.</td>
		</tr>
		<tr>
			<td>sort</td>
			<td>No</td>
			<td>Specify a database field to sort the results by (e.g., "topic_name", or "topic_id") (default: topic_name).</td>
		</tr>
		<tr>
			<td>sort_dir<>
			<td>No</td>
			<td>Specify whether to sort the results in ascending ("ASC") or descending ("DESC") order.</td>
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

Each topic returns the following variables:

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
			<td>The topic ID.</td>
		</tr>
		<tr>
			<td>`{$name}`</td>
			<td>The topic name.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The description of the topic.</td>
		</tr>
	</tbody>
</table>

Example - retrieve all site topics alphabetically:

```
<ul class="topics">
{topics var="topic"}
	<li>{$topic.name}</li>
{/topics}
```

Example - retrieve all sub-topics of topic #4, with description.

```
<ul class="topics">
{topics parent="4" var="topic"}
	<li>{$topic.name} - {$topic.description}</li>
{/topics}
```