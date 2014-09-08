# RSS Feeds

RSS is the worldwide standard for sharing lists of frequently updated content, such as blog posts, so that the content may be imported and read by RSS readers such as [Google Reader](http://www.google.com/reader/).  In laymen's terms, it's a great way to give people the option of staying up-to-date with your website's content by allowing them to be notified of new content, and preview the content with a title and (optional) short summary.  They will then click through to your website to view the full content.

Hero allows you to create an unlimited number of RSS feeds which automatically import content from your content database(s), format them, and output them as RSS files.

RSS feeds are configured with the following options:

* *Name* - This name will be displayed by any RSS reader (e.g., "Latest Blog Posts at Company X")
* *URL* - What URL should this RSS feed be accessed at?
* *Description* - Again, this will be displayed by the RSS reader.  Keep it short.
* *Content Type* - Which type of content shall we retrieve from?
* *Topics* - Should only content of a specific topic be retrieved?  (Optional)
* *Authors* - Should only content written by specific authors be retrieved?  (Optional)
* *Summary Field* - Specify a field which will be used to provide a summary of the content.  If specified, this summary will appear below the title in the RSS reader.  If the summary field data for a specific item is too long, it will be truncated to an appropriate length.  So, for blog posts, you could use the post's Body as the summary field, as it will be shortened to a length that acts as a preview for the user to decide whether they want to click through and read the full content.  (Optional)
* *Sort by* - Specify the manner by which to retrieve content (e.g., by date, descending, or by title, ascending, etc.).  (Optional)
* *Output Template* - Specify the template file to be used to display this RSS feed.  The default will work 99.99% of the time, because these options are so easily configured.  (Optional)