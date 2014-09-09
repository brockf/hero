# Menus

Hero gives any user the ability to drag and drop their site's pages and content into a dynamic, and two-tiered menu ([learn how to use the Menu Manager](/docs/publishing/menus.md)).  Then, in your templates, you simply call the `{menu}` template plugin (specifying which menu to load) and you get a complete `<ul>` formatted menu, like the one below.

## Example Generated Menu

The following menu is generated with `{menu}` tag documented below:

```
<ul id="side_menu" class="menu">
   <li class="current active">
        <a href="http://www.example.com/">Front Page</a>
   </li>
   <li class="subscribe">
        <a href="http://www.example.com/subscriptions">Subscribe</a>
   </li>
   <li class="about">
        <a href="http://www.example.com/about">About the Author</a>
   </li>
   <li class="events">
        <a href=
        "http://www.example.com/conference">Conference</a>
   </li>
   <li class="archives">
        <a href="http://www.example.com/archives">Archives</a>
   </li>
   <li class="app">
        <a href="http://www.example.com/app">App</a>
   </li>
   <li class="connect">
        <a href="http://www.example.com/connect">Social Connect</a>
   </li>
   <li class="contact">
   		<a href="http://www.example.com/contact">Contact Us</a>
   		<ul class="children">
   			<li>
		        <a href="http://www.example.com/form">via Email</a>
		    </li>
		    <li>
		    	<a href="http://www.example.com/connect">Social Connect</a>
		    </li>
		</ul>
   </li>
</ul>
```

Notice that we have more than just a `<ul>` list with `<li>` children and `<a>` links.  We have *classes* and *embedded* `<ul>` lists for child menus.  Let's take a closer look at those.

## Menu Classes

In order to give you the best opportunities for styling this menu with *CSS stylesheets*, the automatic menus in Hero give you meaningful classes throughout the menu `<ul>` element.

* The active link's parent `<li>` element has an "active" class.
* If a link has child links, the embedded `<ul>` list has a "children" class.
* You can pass `id` and `class` parameters to the `{menu}` function (documented below) to assign these attributes to the main `<ul>` menu element.
* When [building your menu in the control panel](/docs/publishing/menus.md), you can specify an unlimited number of classes in the "Edit" pane for each menu item.  These are seen in the above example for each link item (e.g., "current", "app", and "archives").  In this example, they are being used to add unique background images for each link item.

## Child Sub-menus

If you have created a sub-menu for a link item [when creating your menu in the control panel](/docs/publishing/menus.md), these will be exported as embedded `<ul>` lists with a class of "children".

If you want to show these as a dropdown menu, you will need to hide all `ul.children` elements and then use JavaScript to show the sub-menus when hovering over the main link.

## Showing/hiding certain links based on member group status

You can configure which member groups can see a link, or whether the link is only available for logged in/logged out users, when [creating your menu in the control panel](/docs/publishing/menus.md).

## Template Plugin

[tag]{menu}[/tag]

Retrieve a menu and display an auto-generated `<ul>` element with the menu's contents.

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
			<td>name</td>
			<td>Required</td>
			<td>Specifies the menu to load.  You can have multiple menus for any site (e.g, "main_menu", "side_menu", "footer", etc.) and this specifies which to call.</td>
		</tr>
		<tr>
			<td>show_sub_menus</td>
			<td>No</td>
			<td>Set to "yes" to retrieve all sub menus.  Set to "no" to not retrieve any sub menus.  Set to "active" to only retrieve sub menus if the sub menu's parent link is active (i.e., the user is visiting the parent link or one of its child links).</td>
		</tr>
		<tr>
			<td>class</td>
			<td>No</td>
			<td>Specify a "class" attribute for the menu's main `<ul>` element.</td>
		</tr>	
		<tr>
			<td>id</td>
			<td>No</td>
			<td>Specify an "id" attribute for the menu's main `<ul>` element.</td>
		</tr>	
	</tbody>
</table>

Example usage (to return the menu HTML code used in the example at the top of this page):

```
	<div class="menu">
		{menu name="main_menu" class="menu" id="side_menu" show_sub_menus="yes"}
	</div>
```