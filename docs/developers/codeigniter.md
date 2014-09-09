# CodeIgniter

Hero is built upon a very popular PHP web framework called [CodeIgniter](http://www.codeigniter.com).  It is open source, free, and created by the folks over at [Ellis Lab](http://www.ellislab.com).  There are no restrictions in developing websites or software with it, except that you have to tell people you're using it (which we're more than happy to do!).

## Why build one web framework on another framework?

Developing Hero on CodeIgniter has loads of benefits:

* It decreased our early development time significantly.
* We adopt a syntax and application architecture that is known by thousands of developers.  They can now hop into Hero development in a snap.
* We can focus on features and high-level application development, as opposed to "reinventing the wheel" with input libraries, Active Record libraries, and all of the core system functionality contained in CodeIgniter.

There aren't any cons to speak of - CodeIgniter is open and free to distribute.  Hero + CodeIgniter make a deadly team.

## The CodeIgniter Superobject

As with any CodeIgniter application, almost all of the access to CodeIgniter's libraries comes via the *superobject*, an object of CodeIgniter's main class.

In Hero, it's accessed just like any CodeIgniter application, but here's a review:

Within *controllers*, the superobject is accessed with `$this`:

```
class Do_something extends Front_controller {
	function __construct () {
		parent::__construct();	
	}
	
	function index () {
		// let's load the email library
		$this->load->library('email');
		// now we could send an email...
	}
}
```

Within *models*, it's also accessed with `$this`.  However, to access non-core libraries and/or other models, you may have to load the superobject with `get_instance()`.

```
class My_model extends CI_Model {
	function __construct () {
		parent::CI_Model();
	}
	
	function new_thing () {
		// we can access the database library just like normal
		$this->db->insert('test_table', array('test' => 'value'));
		
		// however, when I want to access another model, I have to first retrieve the superobject by reference
		$CI =& get_instance();
		$CI->load->model('publish/content_model');
		$CI->content_model->new_content('test');
	}
}
```

Within *template plugins*, the CodeIgniter superobject is available at `$smarty->CI`.  Example template plugin file that retrieves something from the query string:

```
function smarty_function_query_string ($params, $smarty) {
	// the name of the element we are retrieving is a parameter: "name"
	// see how we can access the CodeIgniter superobject so easily?
	return $smarty->CI->input->get($params['name']);
}
```

Within *libraries, helper functions, or any other code*, you can always retrieve the CodeIgniter superobject like so:

```
$CI =& get_instance();
```

This loads the superobject by reference (so any changes you make to it have a global scope across the application).

## Differences between programming for Hero and CodeIgniter

While we built Hero on CodeIgniter, there are some differences that new Hero developers should take note of.

### Query strings are OK!

*You can use query strings with Hero*, and access these query strings with `$this->input->get()` just like you would expect.  Sadly, CodeIgniter itself has never played nice with query strings - and that's a big nuisance for web developers.

### Almost everything is modular

Instead of having one `/libraries/` folder and one `/models/` folder storing the models and libraries for every Hero module, each module has its own libraries and models folders stored in the module's folder at `/app/modules`.  This allows you to more easily separate your modules from our modules, and keep a nice clean upgrade process.

### We've changed the Controller parent class

In CodeIgniter, controllers are defined like so:

```
class News extends Controller {
	function News () {
		parent::Controller();
	}
}
```

That's alright, but it doesn't allow us to have separate controllers for the control panel and frontend, and it's not in PHP5 structure.

Here's how Hero declares frontend controllers:

```
class News extends Front_Controller {
	function __construct () {
		parent::__construct();
	}
}
```

Furthermore, if you want to protect this controller as part of the administrator's control panel, you would extend `Admincp_controller` as opposed to the `Front_controller`.

### We've extended the shopping cart library

CodeIgniter's [Cart library](http://codeigniter.com/user_guide/libraries/cart.html) is OK, but lacks some of the truly integrated functionality we needed.  So, we've built two new additions: the MY_Cart library, and (the most important addition) the [Cart model](/docs/developers/reference/cart_model.md).

### The Upload library now accepts upload arrays

You can now upload a series of file elements in a form at once.  Example:

```
<input type="file" name="upload[]" />
<input type="file" name="upload[]" />
<input type="file" name="upload[]" />
```

All three of those file elements share one name ("upload").  Previously, you couldn't use the Upload library to upload these.  Now, you can:

Example code for upload processing:

```
for ($i = 0; $i <= 10; $i++) {
	if (!empty($_FILES['upload']['tmp_name'][$i]) and is_uploaded_file($_FILES['upload']['tmp_name'][$i])) {
		// this is an uploaded file
		if (!$this->upload->do_upload('upload',$i)) {
			die(show_error($this->upload->display_errors()));
		}
		
		// it has been uploaded, continue cycling...
	}
}
```

### The Email library has been modified for mass mail

CodeIgniter's [Email library](http://codeigniter.com/user_guide/libraries/email.html) is awesome.  However, if you use it to email hundreds or thousands of people, all of the mail is sent at once and this creates system performance issues and may trip spam filters to block your mail.

So, in Hero, the email library has been extended so that if you pass `send()` a first argument of `TRUE`, the mail will be queued.  The mail queue is processed every 5 minutes, and sends out X emails (default: 400, but this is configurable) so as to not eat up system resources or trip up spam filters.

Example with queued mail:

```
$this->load->library('email');
$this->email->to('test@example.com');
$this->email->from('admin@example.com');
$this->email->subject('This is a test queued email.');
$this->email->message('Hello!');

$this->email->send(TRUE);
```

If you do not want the mail to be queued, just use the email library as is.  You will never notice the difference.

## Everything else is CodeIgniter standard!

Other than these noted differences, everything else in Hero functions exactly as you would expect if you are familiar with CodeIgniter.

So, if you need more information on anything not documented in this developers' guide, or with any core system functionality, [check out the official CodeIgniter documentation](http://www.codeigniter.com/user_guide).