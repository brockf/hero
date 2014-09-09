# RSS Feeds

RSS feeds do not normally require any template customization because RSS feeds are [highly structured documents](http://en.wikipedia.org/wiki/RSS) and [Hero offers numerous configurations for RSS feeds that don't require template customization](/docs/publishing/rss_feeds.md).

Each RSS feed is mapped to a URL when it's [created in the control panel](/docs/publishing/rss_feeds.md).

## Templates

By default, all RSS feeds out with the `rss_feed.txml` template.

## Example RSS Feed Template

```
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">

    <channel>
    
    <title>{$title}</title>

    <link>{$url}</link>
    <description>{$description}</description>
    <dc:creator>{setting name="site_email"}</dc:creator>

    <dc:rights>Copyright {$smarty.now|date_format:"%Y"}</dc:rights>
    <admin:generatorAgent rdf:resource="{setting name="app_link"}" />

    {foreach $content as $item}
    
        <item>

          <title>{$item.title}</title>
          <link>{$item.url}</link>
          <guid>{$item.url_path}</guid>
			
		  {if $summary_field}
          <description><![CDATA[
     		{$item.summary}
     	  ]]></description>
     	  {/if}
          <pubDate>{$item.date|date_format:"%a %b %e %H:%M:%S %Z %Y"}</pubDate>
        </item>

        
    {/foreach}
    
    </channel>
</rss> 
```

## RSS Template Variables

The following variables are available in the RSS feed template.

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
			<td>An array of the content matching the RSS feed's [configured filters](/docs/publishing/rss_feeds.md).  Each item within this array has the same variables as any content item, [listed in the designer's content reference](/docs/designers/reference/publish.md).  If you have configured your RSS feed to auto-generate a "summary" field, this will be available at `{$summary}` within each item's array.</td>
		</tr>
		<tr>
			<td>`{$id}`</td>
			<td>The RSS feed ID.</td>
		</tr>
		<tr>
			<td>`{$link_id}`</td>
			<td>The corresponding link ID in the universal links database.</td>
		</tr>
		<tr>
			<td>`{$title}`</td>
			<td>The title of the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$description}`</td>
			<td>The description of the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$filter_authors}`</td>
			<td>An array of authors allowed in the RSS feed, else FALSE if any author is allowed.</td>
		</tr>
		<tr>
			<td>`{$filter_topics}`</td>
			<td>An array of topics allowed in the RSS feed, else FALSE if any topic is allowed.</td>
		</tr>
		<tr>
			<td>`{$type}`</td>
			<td>The content type ID for the RSS feed's content type.</td>
		</tr>
		<tr>
			<td>`{$type_name}`</td>
			<td>The name of the content type.</td>
		</tr>
		<tr>
			<td>`{$summary_field}`</td>
			<td>If the RSS feed is auto-generating a "summary" for each content item, this is the system name of the field used for generation.</td>
		</tr>
		<tr>
			<td>`{$url}`</td>
			<td>The absolute URL to the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$url_path}`</td>
			<td>The relative path to the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The template file used to display the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$sort_field}`</td>
			<td>The field used to sort the RSS feed.</td>
		</tr>
		<tr>
			<td>`{$sort_dir}`</td>
			<td>The direction the field is being sorted by, either "ASC" or "DESC".</td>
		</tr>
		<tr>
			<td>`{$privileges}`</td>
			<td>If restricting access to this RSS feed by usergroup, this is an array of all the usergroups who can see the content.</td>
		</tr>
		<tr>
			<td>`{$template}`</td>
			<td>The template file used to display the RSS feed.</td>
		</tr>
	</tbody>
</table>
