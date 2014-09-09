# Module Development

A *module* is a collection of files that add new functionality to the platform.  These modules can do as much or as little as they want:

* Function entirely in the background (e.g., small integration plugins)
* Create *binds* on *hooks* to execute methods/functions when a system action occurs
* Create and link to new control panel interfaces
* Generate database tables and manipulate the platform with install/update routines
* Create new frontend controllers mapped to URL's.
* Access other Hero models, libraries, and helper functions (referenced in this guide).

Third-party modules created by developers share identical rights with Hero-standard modules, and have access to every facet of the application.

Creating a module is simple and *requires* the following:

* A folder: `/app/modules/modulename/`
* A module definition and installation file: `/app/modules/modulename/modulename.php`

Optionally, you can add the following:

* One or more model class files at: `/app/modules/modulename/models/`
* A control panel controller file at: `/app/modules/modulename/controllers/admincp.php`
* Other controller files at: `/app/modules/modulename/controllers/`
* A views folder for control panel interface screens: `/app/modules/modulename/views/`
* Helper files in: `/app/modules/modulename/helpers/`
* Library files in: `/app/modules/modulename/libraries/`

Developers have access to all standard [CodeIgniter](http://www.codeigniter.com/user_guide) libraries/helpers, as well as a number of additional libraries and helpers specific to Hero (documented in the Reference section of this guide).  For more information on how Hero works with CodeIgniter, [click here](/docs/developers/codeigniter.md).

## Module Definition & Installation File

* Location: `/app/modules/modulename/modulename.php`

This file has three main responsibilities:

1) Perform any installation or upgrade tasks, such as database table creation/modification, settings creation, and writeable folder creation.
2) Universal control preload operations such as displaying navigation items.  These occur upon each page load of the control panel.
3) Universal frontend preload operations such as adding Smarty template plugin folders.

First, we must define the class, mandatory variables, and class constructor (required by all modules):

```
class Modulename extends Module {
	var $version = '1.0';
	var $name = 'modulename';
	
	function __construct () {
		$this->active_module = $this->name;
		
		parent::__construct();
	}
}
```

Here, we've specified the current module name as well as version.  We've also extended the Module class which handles much of the shared logic for modules.

> If there is a class conflict (e.g., your frontend controller class is `Modulename` and you can't use `Modulename` for the module definition class), you can use `Modulename_module` (e.g., "`Search_module`") as the class name for the module definition file.

The [CodeIgniter superobject](/docs/developers/codeigniter.md) is now accessible within our module definition class at `$this->CI`.

*No other code is required* in the module definition, but few modules will be that simple.

### Installation/upgrade routines

Hero handles installation/upgrades in one `update()` method.  The method takes the current installed version as a parameter and performs a series of "upgrades" to reach the current version.  It the returns the current version so that the database can be updated.

```
class Modulename extends Module {
	var $version = '1.02';
	var $name = 'modulename';
	
	function __construct () {
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/**
	* Update
	*
	* @param int $db_version The current version of the installed module and its database, if applicable
	*
	* @return int $version The updated version (i.e., the version of this module file)
	*/
	function update ($db_version) {
		if ($db_version < 1.0) {
			// this would be a fresh install, as it's less than 1.0
		}
		
		if ($db_version < 1.02) {
			// this second command is an upgrade, something to run only if they don't
			// already have version 1.02 installed
		}
		
		return $this->version;
	}
}
```

Keep in mind that, if your module is in version 1.05 (for example), and you have 5 `if` statements performing separate operations, all 5 of these statements will be executed upon a fresh install.  You don't need to modify each one repeatedly adding the new logic.

### Uninstallation

Not all modules require uninstallation code, and it's not a necessity for any module.  However, if you place a `uninstall()` method in your module definition file, this method will be executed during an uninstall, just like the `update()` runs.

```
class Modulename extends Module {
	var $version = '1.20';
	var $name = 'modulename';
	
	function __construct () {
		$this->active_module = $this->name;
		
		parent::__construct();
	}
	
	/**
	* Uninstall
	*/
	function uninstall () {
		$this->db->query('DROP TABLE IF EXISTS `my_module_table`');
		$this->db->query('DROP TABLE IF EXISTS `another_table`');
	}
}
```

### Administration preload: Execute code upon each control panel load

We will now add a new item to the control panel navigation via a new method, `admin_preload()`.  This method, if it exists, is run *prior to any control panel screen running*.

```
class Modulename extends Module {
	// ... variables, constructor, updator logic, etc...
	
	function admin_preload () {
		$this->CI->admin_navigation->child_link('configuration',60,$this->name . ' Configuration',site_url('admincp/modulename/configuration'));
	}
}
```

The single line in the admin_preload method above adds a new navigation link to the control panel as a sub-link of the "configuration" tab.  It's weight is "60" (i.e., it appears after something weighted 59 but before links weighted 61), has "Module Name Configuration" as its text, and links to `/admincp/modulename/configuration`.  Your module's control panel controller is always accessible at `/admincp/modulename`, and here we are specifying the configuration method within that controller.

### Frontend preload: Execute code upon each frontend load

Similarly to `admin_preload()`, `front_preload()` is executed prior to every page load in the frontend.  It is less commonly used than `admin_preload()`, but can be useful when your module *adds new Smarty template plugins*.

Here, we will define a new folder filled with Smarty plugins in our module:

```
class Modulename extends Module {
	// ... all other module code ...
	
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/modulename/template_plugins/');
	}
}
```

Now, Smarty will look in this directory for function/block plugin files like `function.your_plugin.php`.  For more information on creating template plugins, via our [documentation on creating a template plugin](/docs/developers/template_plugins.md).

## Models

* Location: `/app/modules/modulename/models/your_model.php`

These model definition files are standard CodeIgniter model files.  They usually contain a series of methods used for creating, updating, and deleting database records related to your module. For more information on models, visit [CodeIgniter's documentation](http://www.codeigniter.com/user_guide).

## Libraries and helper functions

* Location (Libraries): `/app/modules/modulename/libraries/your_library.php`
* Location (Helpers): `/app/modules/modulename/helpers/your_function_helper.php`

These are also CodeIgniter-standard files, following typical [Hero + CodeIgniter structure](/docs/developers/codeigniter.md).  They can provide additional functionality to your module and Hero.

## Control panel controller

* Location: `/app/modules/modulename/controllers/admincp.php`

If your module has at least one control panel screen, you must create a control panel controller file.  This controllers allows for control panel pages to be viewable at `/admincp/modulename`.  This main URL will automatically load the `index()` method in your controller (e.g., your module's control panel home page).  A third URI segment (e.g., `/admincp/modulename/another_method`) will prompt that method to be loaded in your control panel controller (e.g., `Admincp_controller->third_uri_segment()`).  All URL segments after this 3rd segment are passed as the first, second, third, (etc.) arguments to the specified method.

Examples:

* `/admincp/builder` loads the `index()` method of the Admincp controller at `/app/modules/builder/controllers/admincp.php`
* `/admincp/builder/post` loads the `post()` method of the Admincp controller
* `/admincp/builder/post/editing` loads the `post($form)` method of the Admincp controller with `$form == 'editing'`

As a start, your control panel controller will include the following code:

```
class Admincp extends Admincp_Controller {
	function __construct()
	{
		parent::__construct();
				
		// sets the active navigation tab to "publish"
		// other options: storefront, members, reports, design, configuration
		// you can re-specify or change this within any method, as well
		$this->admin_navigation->parent_active('publish');
	}
}
```

You should also have an `index()` method to be displayed at `/admincp/modulename`.  If not, and the user accesses this page, they will get a 404 error.

You can add as many methods to your control panel as you wish.  All HTML should be displayed using standard [CodeIgniter views](http://codeigniter.com/user_guide/general/views.html).

## Frontend controllers

* Location: `/app/modules/modulename/controllers/`

Frontend controller files give your module a presence in the frontend.  For example, the Publish module has a frontend controller called "content" which displays a piece of content retrieved via the content model.

Frontend controllers automatically map to URL's.  For example, if a controller called "test.php" existed at `/app/modules/test/test.php`, it would be accessible at `/modulename/test`, and any methods inside of it are accessible at `/modulename/test/method_name`.

Besides these automatically-mapping URL's, you can map your controllers to arbitrary URL's by tapping into the [universal links model](/docs/developers/reference/link_model.md).  By registering a link with this model, you can map any URL path (such as `/my/fake/url/path`) to the module > controller > method of your choice.  The URL path will be passed to your method as its first argument.  For example, in the Publish module, URL's like `/my_article` are mapped to publish (module) > content (controller) > view (method).  The `content->view()` method takes one argument, `$url_path`, and looks up the content in the database using this `$url_path`.

As a start, your frontend controller must extend the `Front_Controller` class and call its parent's constructor:

```
class Any_controller_name extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
}
```

If a URL maps to your controller but the method is non-existent, a 404 error will be returned.  Also, within any of your methods, you can programatically return a 404 error to the user like so:

```
class Any_controller_name extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		$this->load->model('modulename/module_model.php');
		$item = $this->module_model->get_item($url_path);
		
		if (empty($item)) {
			// no such item exists with that URL
			return show_404($url_path);
		}
		
		// else show the item nicely with a page...
	}
}
```

Frontend controllers will almost always assign variables and display templates with the Smarty template engine and API.  [Click here for more information on Smarty](http://www.smarty.net).

### Frontend controllers and Smarty

Hero uses the [Smarty Template Engine](/docs/designers/smarty.md) to display the web pages in the frontend.  Most of the advanced functionality of Smarty and Hero is covered in the [template plugins guide](/docs/developers/template_plugins.md).  Within controllers, using Smarty is simple.

The Smarty library is accessed at `$this->smarty` and includes all the functionality covered in the [Smarty](http://www.smarty.net) documentation.

Within the controllers, all you have to think about is assigning variables and triggering the Smarty template:

```
class Any_controller_name extends Front_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function view ($url_path) {
		$this->load->model('modulename/module_model.php');
		$item = $this->module_model->get_item($url_path);
		
		if (empty($item)) {
			// no such item exists with that URL
			return show_404($url_path);
		}
		
		$this->smarty->assign('item',$item);
		$this->smarty->display('my_template'); // the ".thtml" extension is appended automatically
	}
}
```

Between those two simple methods, `assign()` and `display()`, you have all you need to know about Smarty in frontend controllers.

## Template Plugins

Your module may need to add functionality to the Hero template files via template plugins (called by tags like `{my_template_plugin}` in any template).  For example, you may want to create a plugin that loads data from your module and displays it in any template.

First, create a `template_plugins` folder in your module's folder, e.g., `/app/modules/mymod/template_plugins/`.

Second, define this folder as holding Smarty template plugins in your module definition file:

```
class Modulename extends Module {
	// ... all other module code ...
	
	function front_preload () {
		$this->CI->smarty->addPluginsDir(APPPATH . 'modules/modulename/template_plugins/');
	}
}
```

Third, use the [template plugin development](/docs/developers/template_plugins.md) documentation to create template plugin files in this folder.  These functions will immediately be available as template plugins site-wide in all your templates.

## Automatic Processes and Cronjobs

If your module requires an automatic process or a cronjob be run on a scheduled basis, check out the documentation for using Hero's built-in [cronjob architecture](/docs/developers/cronjobs.md).

## Resources

This guide has described the basic structure for modules, but what about the code that integrates and builds on the platform?  To find out more about tapping into Hero models and libraries, view the Reference section of this guide.  Also, check out [the section on CodeIgniter](/docs/developers/codeigniter.md) to see all of the versatile libraries that you have at your disposal.

Of course, you should also read the Hero developer [standards and practices](/docs/developers/standards.md) so that your module can be easily shared with others.

Best of luck with developing on the best web platform available!