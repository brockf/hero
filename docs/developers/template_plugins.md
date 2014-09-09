# Template Plugins

Hero offers a variety of template plugins out-of-the-box that can be used throughout your template files to do things like retrieve content, access setting values, and generate proper URL's.

However, developers can also create their **own** template plugins.  Template plugins can be included:

* In a module folder at `/app/modules/yourmodule/template_plugins/`, when the folder is referenced in the [module definition file](/docs/developers/modules.md) in the `front_preload()` section.
* In a theme folder, at `/themes/yourtheme/plugins/`.
* In the common plugins folder, at `/themes/_plugins/`.

## Function Plugins vs. Block Plugins

*Functions* are template plugins like `{setting name="site_name"}` [documented here](/docs/designers/reference/global_plugins.md).  They only include one tag and return a string of data (or empty string) upon their call.  Function plugins are defined in files named `function.yourfunction.php` and share a common function structure:

```
function smarty_function_yourfunction ($params, &$smarty) {
	// function code
}
```

More information on creating template functions can be found at [Smarty's documentation](http://www.smarty.net).

*Blocks* are template plugins that include an opening and closing tag, like:

```
{restricted in_group="3"}
This content is shown to those in group #3.
{/restricted}
```

They, like function plugins, can be passed parameters which alter their execution.  However, unlike functions, they are also passed the content between the two tags and can manipulate it, return it, or discard it as they wish.

Block plugins are defined in files named `block.yourblock.php` and share a common function structure:

```
function smarty_block_yourblock ($params, $tagdata, &$smarty, &$repeat){	
	// do what you will with the $params array and $tagdata string
}
```

## Accessing the application superobject

Within template functions and blocks, you can access the CodeIgniter superobject (and all of its libraries, models, and methods) at `$smarty->CI`.  For example, to make a database call within a template plugin:

```
function smarty_function_myfunction ($params, &$smarty) {
	$smarty->CI->db->select('my_id');
	$result = $smarty->CI->db->get('my_table');
	
	// etc...
}
```

[More information on the superobject is here](/docs/developers/codeigniter.md).

## Looping through data in a block plugin

Many block plugins allow designers to retrieve X number of items from the database and loop through/display these items within the template.  The items are displayed through template variables contained between the block plugin's opening and closing tags.  [Smarty](/docs/designers/smarty.md) makes this looping/variable assignment possible, but it's not very easy.

Hero includes a proprietary library extension (*loop_data*) for Smarty that makes looping through data within a block plugin much easier.  As an example, we have the [{topics}](/docs/designers/reference/publish.md) template plugin.  This plugin retrieves your site's defined topics within your templates:

```
function smarty_block_topics ($params, $tagdata, &$smarty, &$repeat){
	if (!isset($params['var'])) {
		show_error('You must specify a "var" parameter for template {topics} calls.  This parameter specifies the variable name for the returned array.');
	}
	// deal with filters
	$filters = array();
	
	// param: sort
	if (isset($params['sort'])) {
		$filters['sort'] = $params['sort'];
	}
	
	// param: sort_dir
	if (isset($params['sort_dir'])) {
		$filters['sort_dir'] = $params['sort_dir'];
	}
	
	// param: limit
	if (isset($params['limit'])) {
		$filters['limit'] = $params['limit'];
	}
	
	// param: offset
	if (isset($params['offset'])) {
		$filters['offset'] = $params['offset'];
	}
	
	// initialize block loop
	$data_name = $smarty->CI->smarty->loop_data_key($filters);
	
	if ($smarty->CI->smarty->loop_data($data_name) === FALSE) {
		// make content request
		$smarty->CI->load->model('publish/topic_model');
		$topics = $smarty->CI->topic_model->get_topics($filters);
	}
	else {
		$topics = FALSE;
	}
	
	$smarty->CI->smarty->block_loop($data_name, $topics, (string)$params['var'], $repeat);
			
	echo $tagdata;
}
```

Most of the code above deals with building the `$filters` array from the tag parameters for the `get_topics()` call.  However, after that, we see the "loop_data" calls that make the recursive magic happen.

*First, we generate a unique key based on the filters in the call* with `$smarty->CI->loop_data_key()`.  Because, through recursion, this function will be called repeatedly, this key is used later to store the returned data from `get_topics()` so that we don't begin an infinite loop of retrieving topics.

*Second, we check to see if data has already been cached for this request* with `$smarty->CI->loop_data()`.  If this method returns `FALSE`, no content has yet been retrieved (i.e., this is our first time executing this function).

*Third, if the previous method returns FALSE and we don't have any data*, we retrieve the data and store it in a variable.  In this case, we populate the `$topics` variable with the data retrieved from `get_topics()`.  *If we already had loop data,* we set `$topics = FALSE`.  In doing so, the next method knows to retrieve data from the cache.

*Finally, we call the magic method*, `$smarty->CI->block_loop()`, which will do the rest of the looping and variable assignment for us.  The four parameters should be passed as-is, with only the second variable being customized to refer to the data we retrieved in step 3.