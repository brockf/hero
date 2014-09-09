# Blogs

In Hero, *blogs* are content listings that dynamically display content you have published.  This could be in a standard blog format sorted by publish date, or it could be a table listing of content, an alphabetical listing of entries, etc.

As with all content, blogs can be limited to access by specific member groups.

Blogs are configured with the following options:

* *Title* - This name will be displayed by any RSS reader (e.g., "Latest Blog Posts at Company X")
* *URL* - What URL should this blog be accessed at?
* *Description* - Depending on how your template files are setup, this bit of text may be used in the blog.
* *Access Requires Membership to Group* - Which member groups should be allowed to view this blog?
* *Content Type* - Which type of content shall we retrieve from?
* *Topics* - Should only content of a specific topic be retrieved?  (Optional)
* *Authors* - Should only content written by specific authors be retrieved?  (Optional)
* *Summary Field* - Specify a field which will be used to provide a summary of the content in the blog listing.  If specified, this field will be available in the templates as `$summary` for each item.  You can optionally automatically shorten this summary to a set number of characters.
* *Sort by* - Specify the manner by which to retrieve content (e.g., by date, descending, or by title, ascending, etc.).  (Optional)
* *Items per page* - How many items should be displayed on each page of the blog?
* *Output Template* - Specify the template file to be used to display this blog. (Optional)

## Why use blogs and not the {content} template tag?

Blogs are an easy way for non-technical users to create content listings.  They can also be used within certain themes for different content listing purposes.

However, it is true that a designer and developer who is customizing the templates may do better to [use the {content} template tag to retrieve content](/docs/designers/reference/publish.md) as opposed to a blog, in some cases.  A blog effect can be achieved by combining a `{content}` tag and [mapping a URL to your template](/docs/designers/mapping_urls.md).