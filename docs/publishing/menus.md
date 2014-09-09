# Menus

All content in Hero is registered in a global link database (developers: [see how to access this](/docs/developers/reference/link_model.md)).  In doing so, Hero allows you to *drag and drop* your site's content and modules into navigational menus through a simple web interface, directly in your site's control panel.

The *Menu Manager* is accessible at Design > Menu Manager.  Here, you can create menus or edit existing menus.

## Hiding Menu Items from Certain Groups

If you select "Edit" beside any link item in your menu, you can specify which usergroups should see this link.  This allows you to hide "Signup!" links from subscribers and target other users with links.

## Link Customization

You can customize the link text as well as any CSS classes for each individual link item in the "Edit" menu for any menu link item.  [CSS classes can be used to style specific menu items with your CSS stylesheets](/docs/designers/reference/menus.md).

## Two-Tiered Menus

The Menu Manager supports two-tiered menus, or menus with parent links and children links.  To create a submenu beneath any parent link, simply select *Create Submenu* beside that link item.  You will then access the child menu and be able to continue dragging content into the menu.  You can select the main menu by click the main menu link at the top of the Menu Manager box at any time.

## Finding Your Content

If you have lots of content and have trouble finding it in the Menu Manager, there are features that will make your life easier.  First of all, all content is alphabetically sorted.  Second, you can search all of your site's links directly within the Menu Manager by just entering part or all of the link's title.  Each content item will have a title as well as say which type of content it is (e.g., "Blog Post", "Module", "Article", "Static Page", etc.).

## External Links

Not all of your menu links will be links to site content or modules.  You may want to link to unmapped areas of your site, or pages not at your own website.  For this, we have *external links*.  At the bottom of the Menu Manager, you can enter any link (this will populate the *href* attribute of the `<a>` link tag) and any link text and add this to your menu.

## Displaying Menus

If your theme is not already setup to display menus, you can use the [{menu} tag to dynamically generate the HTML for an Hero-managed menu](/docs/designers/reference/menus.md).