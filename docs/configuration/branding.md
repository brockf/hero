# Branding (White Label)

As laid out in the [License agreement](/docs/license.md), you are able to brand Hero to your own company or band.  This allows you to resell the platform, or use it as if it was a proprietary technology with your clients - a great way to save thousands of hours of work!

> This guide refers to branding in the sense of modification of the control panel.  Customizing the frontend (public) part of your website has no restrictions and is obviously highly encouraged.  No copyright notices need to stay in the frontend of your website.

## Exceptions to total re-branding

* You cannot alter the copyright notices in the Hero source code.
* You must leave a copyright notice in the control panel footer.

## Branding the application as your own

Hero provides a simple way to re-brand the application via `/app/config/config.php`.  In this file, you will find 3 lines of code like the following:

```
$config['app_name'] = 'Hero';
$config['app_link'] = 'http://www.example.com/';
$config['app_support'] = 'http://www.example.com/support';
```

These can be modified and the changes will be reflected across the entire application immediately.

As part of your licensing, you may even be able to generate branded User Guides and Installation packages directly in your Licensee Control Panel.

## Advanced Branding

If you want to change the look and feel of the control panel, you can copy the `/branding/default` folder to `/branding/custom` and modify to taste.  When files exist here, they will automatically take precedence over the default CSS stylesheets, JavaScript files, and images.

> If you copy over the CSS stylesheet(s), you will want to copy the entire `images` folder as well.  The CSS stylesheets reference images relative to this file so, without this folder, the image links will break.

You can also create a folder at `/branding/custom/views` and duplicate/modify the view files at `/app/views/`.  These are the highly-important global control panel header/footer files.