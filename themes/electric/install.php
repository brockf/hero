<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

// content types
$CI->load->model('publish/content_type_model');
$type_blog = $CI->content_type_model->new_content_type('Blog', TRUE, TRUE, FALSE, 'blog_post.thtml', 'blog/');
$type_blog = $CI->content_type_model->get_content_type($type_blog);

$type_pages = $CI->content_type_model->new_content_type('Static Pages', TRUE, TRUE, FALSE, 'content.thtml', FALSE);
$type_pages = $CI->content_type_model->get_content_type($type_pages);

// content type custom fields
$CI->load->model('custom_fields_model');
$CI->load->library('custom_fields/fieldtype'); // hack that triggers update to the custom_fields module
$CI->custom_fields_model->new_custom_field($type_blog['custom_field_group_id'], 'Body', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_blog['system_name']);
$CI->custom_fields_model->new_custom_field($type_blog['custom_field_group_id'], 'Attached Download', 'file_upload', FALSE, FALSE, FALSE, FALSE, TRUE, FALSE, $type_blog['system_name']);
$CI->custom_fields_model->new_custom_field($type_pages['custom_field_group_id'], 'Body', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_pages['system_name']);

// configure search with content types
$CI->settings_model->update_setting('search_content_types', 'a:2:{i:' . $type_blog['id'] . ';s:7:"summary";i:' . $type_pages['id'] . ';s:1:"0";}');

// topics
$CI->load->model('publish/topic_model');
$CI->topic_model->new_topic('Default');

// content
$CI->load->model('publish/content_model');
$CI->content_model->new_content($type_blog['id'], 1001, 'Is ' . $CI->config->item('app_name') . ' really a "platform?"', FALSE, FALSE, FALSE, date('Y-m-d H:i:s', strtotime('3 days ago')), array('body' => '<p>It seems like every new web app thinks it\'s a platform.  What makes ' . $CI->config->item('app_name') . ' . worthy of the title?  Well, let\'s see:</p><ol><li>Our file-based theme folders give you absolute control over <i>every bit of HTML</i> that is sent to your visitor\'s browser.</li><li>You can easily drop in totally independent module folders and add custom functionality, modifying the control panel, frontend, template plugins... anything!</li><li>We built ' . $CI->config->item('app_name') . ' on <i>other</i> open-source platforms: <a target="_blank" href="http://www.codeigniter.com">CodeIgniter</a> (a PHP framework) and <a target="_blank" href="http://www.smarty.net">Smarty</a> (the most popular PHP template engine)!  ' . $CI->config->item('app_name') . ' uses unadulterated, cutting edge releases of both of these powerful platforms to make your development faster and easier than every before.</li><li>You can always upgrade to the latest release, simply by uploading the new files.  It upgrades automatically in the background - foolproof every time!</li><li>There\'s nothing it can\'t do for you, most of the time right out-of-the-box, and always with some smart template customization and module building!</li></ol><p>On that note, explore ' . $CI->config->item('app_name') . ' and <i>please</i> replace this default content with your own!</p>', 'attached_download' => ''));
$CI->content_model->new_content($type_blog['id'], 1001, 'Two of our many template tags, working to protect your content and downloads!', FALSE, FALSE, FALSE, date('Y-m-d H:i:s', strtotime('1 day ago')), array('body' => '<p>This blog post serves two purposes: (1) provide some nice default content for you to enjoy, and (2) show off our <b>{protected_link}</b> template tag!  This simple tag, placed in your template, will protect any URL or file from being downloaded by site members who are not in the proper member group(s).</p><p>Just feed it two parameters (e.g., "{protected_link url="uploads/my_file.pdf" groups="2"}") and it will automatically generate a protected link in your template.  This allows you to make certain files subscribers- or members-only.  And, when combined with another tag - <b>{restricted}</b> - you can now protect what users see on your site and what they can download, through simple template tags!</p><p>How about an example?  This blog post has an attached download which will only be able to be downloaded by registered members.  Can you download it?</p>', 'attached_download' => 'themes/electric/images/placeholders/download.jpg'));
$CI->content_model->new_content($type_blog['id'], 1001, 'Welcome to ' . $CI->config->item('app_name') . '!', FALSE, FALSE, FALSE, date('Y-m-d H:i:s'), array('body' => '<p><img src="themes/electric/images/placeholders/caribou.jpg" alt="Caribou CMS" style="margin: 0 15px 15px 0; float: left" />If this is your first time using ' . $CI->config->item('app_name') . ', you\'re in for a treat.  ' . $CI->config->item('app_name') . ' is the fastest and most powerful content management system and web development platform on the market!</p><p>Take a look around this default <b>Electric</b> theme and see what ' . $CI->config->item('app_name') . ' can do right out-of-the-box.  If you like this design, you can hop into the <a href="admincp">control panel</a> and begin managing your content types, content, store products, subscriptions, and anything else you need to put your project together.</p><p>If you aren\'t a big fan of this theme, there are other themes you can switch to at <i>Design > Themes</i> in the control panel.  Or, you can take any theme and start customizing the HTML template files and CSS stylesheets to make it your own.  You can edit the themes at <i>Design > Theme Editor</i> or via FTP (see the <a target="_blank" href="' . $CI->config->item('app_support') . '">user guide</a> for more information).  If you go with this "custom theme" route, we recommend duplicating and renaming a theme folder (in <i>/themes/</i>) so you can upgrade to new releases of ' . $CI->config->item('app_name') . ' without overwriting your custom theme folder.</p><p>Anyways, enough babbling - take a look around and enjoy ' . $CI->config->item('app_name') . '.', 'attached_download' => ''));

$about = $CI->content_model->new_content($type_pages['id'], 1001, 'About Us', FALSE, FALSE, FALSE, '2010-04-10 08:55:55', array('body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));
$about = $CI->content_model->get_content($about);

$CI->content_model->new_content($type_pages['id'], 1001, 'Thank You!', 'contact/thanks', FALSE, FALSE, '2010-04-10 08:55:55', array('body' => '<p>Thank you for making contact with us!  We will be in touch shortly.</p>'));

// blog
$CI->load->model('blogs/blog_model');
$blog_id = $CI->blog_model->new_blog($type_blog['id'], 'blog Archives', 'blog', 'All the latest happenings from our company.', array(), array(), 'summary', 'content_date', 'DESC', FALSE, $template = 'blog.thtml');
$blog = $CI->blog_model->get_blog($blog_id);

// RSS feed
$CI->load->model('rss/rss_model');
$feed_id = $CI->rss_model->new_feed($type_blog['id'], 'blog Feed', 'blog_rss', 'All the latest happenings from our company.', array(), array(), 'summary', 'content_date', 'DESC', $template = 'rss_feed.txml');
$feed = $CI->rss_model->get_feed($feed_id);

// forms
$CI->load->model('forms/form_model');
$form = $CI->form_model->new_form('Contact Us', 'contact', '<p>Please complete the form below to send a message to us.</p>', 'Submit Message', 'contact/thanks');
$form = $CI->form_model->get_form($form);

$CI->custom_fields_model->new_custom_field($form['custom_field_group_id'], 'Your Name', 'text', FALSE, FALSE, '450px', FALSE, TRUE, array(), $form['table_name']);
$CI->custom_fields_model->new_custom_field($form['custom_field_group_id'], 'Your Email', 'text', FALSE, FALSE, '450px', FALSE, TRUE, array('email'), $form['table_name']);
$CI->custom_fields_model->new_custom_field($form['custom_field_group_id'], 'Message', 'textarea', FALSE, FALSE, '450px', FALSE, TRUE, array(), $form['table_name']);

if (module_installed('store')) {
	// collections custom fields
	if (!$field_group = $CI->config->item('collections_custom_field_group')) {
		$field_group = $CI->custom_fields_model->new_group('Collections');
		$this->settings_model->new_setting(2, 'collections_custom_field_group', $field_group, 'The custom field group ID for collection data.', 'text', '');
	}
	$CI->custom_fields_model->new_custom_field($field_group, 'Image', 'file_upload', FALSE, FALSE, FALSE, FALSE, TRUE, FALSE, 'collections');
	
	// collections
	$CI->load->model('store/collections_model');
	$collection_id_blueberries = $CI->collections_model->new_collection('Blueberries','Our delicious blend of blueberries make the best pies.', 0, array('image' => 'themes/orchard/images/placeholders/blueberries.jpg'));
	$collection_id_cherries = $CI->collections_model->new_collection('Cherries','Even better than the cough candies!', 0, array('image' => 'themes/orchard/images/placeholders/cherries.jpg'));
	$collection_id_apples = $CI->collections_model->new_collection('Apples','You\'ll only ever eat apple pies again!', 0, array('image' => 'themes/orchard/images/placeholders/apples.jpg'));
	$collection_id_oranges = $CI->collections_model->new_collection('Oranges','Straight from Florida, in the back of a truck!', 0, array('image' => 'themes/orchard/images/placeholders/oranges.jpg'));
	$collection_id_pears = $CI->collections_model->new_collection('Pears','Not for the feint of heart', 0, array('image' => 'themes/orchard/images/placeholders/pears.jpg'));
	$collection_id_strawberries = $CI->collections_model->new_collection('Strawberries','On your cereal, ice cream, pies, and anything else!', 0, array('image' => 'themes/orchard/images/placeholders/strawberries.jpg'));

	// products
	$CI->load->model('store/products_model');
	$CI->products_model->new_product('Fresh Blueberries', 'Order a basket of the freshest blueberries out there!', array($collection_id_blueberries), 4.99, 1, TRUE, FALSE, 0, FALSE, '', TRUE);
}

// menu
$CI->load->model('menu_manager/menu_model');
$menu = $CI->menu_model->new_menu('main_menu');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Home', 'home');
if (module_installed('store')) {
	$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Store', 'store');
}
$CI->menu_model->add_link($menu, FALSE, 'link', $about['link_id'], 'About Us');
$CI->menu_model->add_link($menu, FALSE, 'link', $form['link_id'], 'Contact Us');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'My Account', 'my_account');

$menu = $CI->menu_model->new_menu('footer_menu');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Control Panel', 'control_panel');