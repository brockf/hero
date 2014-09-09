# Blogs and Archives

Blogs and archives, [created in the control panel](/docs/publishing/blogs.md), are a way of creating "content listing" pages.  While the name "Blogs and Archives" is somewhat functionally-binding, you can actually use this for any type of content listing imaginable.  This is highly evident when you see how flexible and easy templating these pages is.

## Templates

Each blog is bound to a URL when created, and you can specify any template in your theme's folder to display this blog.  The default template - included with standard themes - is `blog.thtml`.

## Template Variables

When the blog template is triggered, the following variables are available for use right away, without any template plugins.

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
			<td>`{$content}`</td>
			<td>An array of the content matching the blog's [configured filters](/docs/publishing/blogs.md) as well as limited to the current page, if pagination is in effect.  Each item within this array has the same variables as any content item, [listed in the designer's content reference](/docs/designers/reference/publish.md).  If you have configured your blog to auto-generate a "summary" field, this will be available at `{$summary}` within each item's array.</td>
		</tr>
		<tr>
			<td>`{$pagination}`</td>
			<td>A `<ul>` formatted list of pagination links generated automatically.  Can be included in your template as-is.</td>
		</tr>
		<tr>
			<td>`{$id}`</td>
			<td>The blog ID.</td>
		</tr>
		<tr>
			<td>`{$link_id}`</td>
			<td>The corresponding link ID in the universal links database.</td>
		</tr>
		<tr>
			<td>`{$title}`</td>
			<td>The title of the blog.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The description of the blog.</td>
		</tr>
		<tr>
			<td>`{$filter_authors}`</td>
			<td>An array of authors allowed in the blog, else FALSE if any author is allowed.</td>
		</tr>
		<tr>
			<td>`{$filter_topics}`</td>
			<td>An array of topics allowed in the blog, else FALSE if any topic is allowed.</td>
		</tr>
		<tr>
			<td>`{$type}`</td>
			<td>The content type ID for the blog's content type.</td>
		</tr>
		<tr>
			<td>`{$type_name}`</td>
			<td>The name of the content type.</td>
		</tr>
		<tr>
			<td>`{$summary_field}`</td>
			<td>If the blog is auto-generating a "summary" for each content item, this is the system name of the field used for generation.</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute URL to the blog's main page.</td>
		</tr>
		<tr>
			<td>`{$url_path}`</td>
			<td>The relative path to the blog's main page.</td>
		</tr>
		<tr>
			<td>`{$auto_trim}`</td>
			<td>If the summary field is being auto trimmed, how many characters is it being cut to?</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The template file used to display the blog.</td>
		</tr>
		<tr>
			<td>`{$sort_field}`</td>
			<td>The field used to sort the blog.</td>
		</tr>
		<tr>
			<td>`{$sort_dir}`</td>
			<td>The direction the field is being sorted by, either "ASC" or "DESC".</td>
		</tr>
		<tr>
			<td>`{$privileges}`</td>
			<td>If restricting access to this blog by usergroup, this is an array of all the usergroups who can see the content.</td>
		</tr>
		<tr>
			<td>`{$per_page}`</td>
			<td>The number of content items to show per blog page.</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The template file used to display the RSS feed.</td>
		</tr>
	</tbody>
</table>

## Example Blog Template

List the content items on the blog page, and paginate if necessary.

```
{extends file="layout.thtml"}
{block name="title"}
{$title} - {$smarty.block.parent}
{/block}
{block name="content"}
	<h1>{$title}</h1>
	{$description}
	
	<ul class="blog">
	{foreach $content as $item}
		<li>
			<h4 class="date">{$item.date|date_format:"%A, %B %e, %Y"}</h4>
			<a href="{$item.url}">{$item.title}</a>
			<p class="summary"}>{$item.summary}</p>
		</li>
	{/foreach}
	</ul>
	
	{$pagination}
	
{/block}
```