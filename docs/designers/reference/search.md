# Search Forms and Results

Hero includes a [powerful search feature](/docs/configuration/search.md) that can perform a fulltext keyword search of all your site's content and store products.  Creating the search forms and customizing the results page is just as easy.

## Important URL's

* `/search` - The search module is located here, and all search forms should send a GET request to this address.

## Templates

* `search.thtml` All search results, and the default search form, are displayed with this template by default.

## Creating Search Forms

To create a search form, you need to setup a form with the following attributes:

* Form Action: `{url path="search"}`
* Form Method: `GET`
* Search Field: `<input type="text" name="q" />`

Example:

```
<form action="{url path="search"}" method="GET">
Search Query: <input type="text" name="q" />&nbsp;&nbsp;<input type="submit" name="do_search" value="Search" />
</form>
```

## Example Search Template

```
{extends file="layout.thtml"}
{block name="title"}
{$title} - {$smarty.block.parent}
{/block}
{block name="content"}
	<h1>{$title}</h1>
	
	<div id="search_form">
	<form method="get" action="{url path="search"}" class="validate">
	<input id="query" class="text mark_empty required" rel="search keyword(s)" name="q" value="{if $query}{$query}{/if}" />&nbsp;<input id="submit" type="submit" name="go" value="Search" />
	</form>
	</div>
	
	{if $searching == TRUE}
		<div class="num_results">{$num_results} Results</div>
		
		{if $num_results == 0}
			<p>Your search did not return any results.</p>
		{else}
			<ul class="search_results">
			{foreach $results as $result}
				{if $result.result_type == "content"}
					<li>
						<a href="{$result.url}">{$result.title}</a>{if $result.relevance != 0} <span class="relevance">relevance: {$result.relevance}</span>{/if}
						{if $result.summary}<br /><p>{$result.summary}</p>{/if}
					</li>
				{elseif $result.result_type == "product"}
					<li>
						<a href="{$result.url}">{$result.name}</a> {setting name="currency_symbol"}<span class="price">{$result.price}</span> <span class="relevance">relevance: {$result.relevance}</span>
						{if $result.summary}<br /><p>{$result.summary}</p>{/if}
					</li>
				{/if}
			{/foreach}
			</ul>
			
			{$pagination}
		{/if}
	{else}
		<p>Enter your keyword(s) above to search {setting name="site_name"}.</p>
	{/if}
{/block}
```

## Search Template Variables

The following variables are available in the `search.thtml` template.

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
			<td>`{$search}`</td>
			<td>If a GET/POST request with a "q" input field has been sent to this page, the module will automatically search.  This variable is set to TRUE if searching and FALSE if not.  If false, you should simply display a search form.</td>
		</tr>
		<tr>
			<td>`{$query}`</td>
			<td>If searching, this variable stores the keyword(s) being used.</td>
		</tr>
		<tr>
			<td>`{$num_results}`</td>
			<td>If searching, how many results were found?</td>
		</tr>
		<tr>
			<td>`{$results}`</td>
			<td>If searching, this variable is an array of all matching items.  Set to FALSE if no results were found.  More information on the contents of this array is below, under "Search Result Variables".</td>
		</tr>
		<tr>
			<td>`{$pagination}`</td>
			<td>If searching and the search results span multiple pages, this variable will contain a `<ul>` list of pagination links.</td>
		</tr>
	</tbody>
</table>

## Search Result Variables

If a search has been made and there are results to display, the `{$results}` variable contains the data for each result.  It is ordered from high -> low relevancy, so it can be displayed as is with a simple loop.

The following data is available for each search result item:

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
			<td>`{$result_type}`</td>
			<td>Either "content" or "product", depending on what type of result this matching item is.</td>
		</tr>
		<tr>
			<td colspan="2">If the `{$result_type}` is a content item, all [content item variables](/docs/designers/reference/publish.md) are available.<br />
						    If the `{$result_type}` is a product, all [store product variables](/docs/designers/reference/store.md) are available.<br />
						    You can see this switching in the example search results template on this page.</td>
		</tr>
		<tr>
			<td>`{$relevance}`</td>
			<td>The relevance (e.g., "0.49914754") of the item to search keywords.  This is how the items are ranked, from highest to lowest.</td>
		</tr>
	</tbody>
</table>
