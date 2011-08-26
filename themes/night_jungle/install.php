<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();

// content types
$CI->load->model('publish/content_type_model');
$type_facts = $CI->content_type_model->new_content_type('Did You Know?');
$type_facts = $CI->content_type_model->get_content_type($type_facts);

$type_members = $CI->content_type_model->new_content_type('Members Area', TRUE, TRUE, FALSE, 'members_content.thtml', 'members/');
$type_members = $CI->content_type_model->get_content_type($type_members);

$type_pages = $CI->content_type_model->new_content_type('Static Pages', TRUE, TRUE, FALSE, 'content.thtml', FALSE);
$type_pages = $CI->content_type_model->get_content_type($type_pages);

// content type custom fields
$CI->load->model('custom_fields_model');
$CI->load->library('custom_fields/fieldtype'); // hack that triggers update to the custom_fields module

$CI->custom_fields_model->new_custom_field($type_facts['custom_field_group_id'], 'Fact', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_facts['system_name']);

$CI->custom_fields_model->new_custom_field($type_members['custom_field_group_id'], 'Summary', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_members['system_name']);
$CI->custom_fields_model->new_custom_field($type_members['custom_field_group_id'], 'Body', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_members['system_name']);

$CI->custom_fields_model->new_custom_field($type_pages['custom_field_group_id'], 'Body', 'wysiwyg', FALSE, FALSE, '650px', FALSE, TRUE, FALSE, $type_pages['system_name']);

// configure search with content types
$CI->settings_model->update_setting('search_content_types', 'a:1:{i:' . $type_pages['id'] . ';s:1:"0";}');

// topics
$CI->load->model('publish/topic_model');
$CI->topic_model->new_topic('Default');

if (module_installed('billing')) {
	// subscribers group
	$CI->load->model('users/usergroup_model');
	$usergroup = $CI->usergroup_model->new_group('Subscribers');
	
	// subscription
	$CI->load->model('billing/subscription_plan_model');
	$CI->subscription_plan_model->new_plan('Supporter Subscription','9.99', '9.99', TRUE, 30, 7, FALSE, 24, $usergroup, 0, 'Help support our organization with a low monthly payment.');
}
else {
	$usergroup = 1;
}

// content
$CI->load->model('publish/content_model');
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>Mammals are the only animals with flaps around the ears.</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>There are about one billion cattle in the world of which 200 million are in India.</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>A dog was the first in space and a sheep, a duck and a rooster the first to fly in a hot air balloon.</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>A snail has two pairs of tentacles on its head. One pair is longer than the other and houses the eyes. The shorter pair is used for smelling and feeling its way around. (Some snail species have only one pair of tentacles, thus they have just one eye.)</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>The giant squid has the biggest eyes of any animal: its eyes measure 16 inches (40 cm) in diameter.</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>Sharks and rays also share the same kind of skin: instead of scales, they have small tooth-like spikes called denticles. The spikes are so sharp that shark skin has long been used as sandpaper.</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>Measured in straight flight, the spine-tailed swift is the fastest bird. It flies 106 mph (170 km/h). Second fastest is the Frigate, which reaches 94 mph (150 km/h).</p>'));
$CI->content_model->new_content($type_facts['id'], 1, FALSE, FALSE, FALSE, FALSE, '2010-12-25 10:10:10', array('fact' => '<p>Fish and insects do not have eyelids Ð their eyes are protected by a hardened lens.</p>'));

$CI->content_model->new_content($type_members['id'], 1, 'Update from Africa', FALSE, FALSE, array($usergroup), '2010-12-25 10:10:10', array('summary' => '<p>Our work in Africa is going swimmingly.  Thank you to all the supporters (like yourself) for helping us out as we give all of the monkeys here a great home.</p>','body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));
$CI->content_model->new_content($type_members['id'], 1, 'Next steps in Brazil', FALSE, FALSE, array($usergroup), '2010-04-10 08:55:55', array('summary' => '<p>Our protection work with the uncontacted tribes has gone very well (though they wouldn\'t know it...).  Next, we plan on tracking down the poachers!</p>','body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));
$CI->content_model->new_content($type_members['id'], 1, 'Protect the Turtles!', FALSE, FALSE, array($usergroup), '2010-04-10 08:22:55', array('summary' => '<p>It\'s important that we get saving the turtles as fast as possible.</p>','body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));

$about = $CI->content_model->new_content($type_pages['id'], 1, 'Our Story', FALSE, FALSE, FALSE, '2010-04-10 08:55:55', array('body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));
$about = $CI->content_model->get_content($about);

$projects = $CI->content_model->new_content($type_pages['id'], 1, 'Current Projects', FALSE, FALSE, FALSE, '2010-04-10 08:55:55', array('body' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin at augue quis massa pulvinar hendrerit. Maecenas non lacus a neque dictum sodales in nec urna. Phasellus placerat accumsan dui, non pretium ipsum consectetur et. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed commodo semper elementum. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Suspendisse eget quam vel odio bibendum vulputate id ullamcorper purus. Pellentesque consequat viverra vehicula. Quisque non rhoncus orci. Maecenas tristique pulvinar augue, non laoreet diam ullamcorper quis. Cras nunc libero, tincidunt eget rhoncus quis, elementum ac ligula.</p><p>Praesent laoreet elit quis erat congue fermentum sit amet eget nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque ac felis lectus. Phasellus eros ante, dignissim et placerat quis, tincidunt ac orci. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Praesent adipiscing, lacus at condimentum euismod, massa orci rhoncus dolor, in cursus enim ante id arcu. Donec facilisis semper quam, sit amet blandit dolor pretium a. Aliquam dignissim condimentum congue. Nunc nec ante nunc, sed fermentum magna. Aenean auctor viverra sapien, ut dapibus nibh venenatis sit amet. Praesent in arcu feugiat diam pellentesque tempus at vitae tortor. Donec tempus sem metus. Nam ut nibh orci, vel rhoncus ligula. Aenean diam velit, varius in consequat sed, fermentum ut tortor. Aliquam tristique sodales nibh et volutpat.</p><p>In a libero at leo tincidunt scelerisque. Integer semper arcu massa, imperdiet bibendum risus. Nulla molestie, justo eu consectetur vestibulum, eros orci auctor odio, in pharetra arcu mauris et elit. Aliquam nec ligula risus, non malesuada risus. Phasellus sit amet ligula ac leo tristique laoreet. Ut consequat eros eget risus adipiscing nec egestas enim laoreet. Quisque sapien lacus, egestas ut tempor nec, porta a felis. Sed mattis adipiscing volutpat. Donec diam justo, aliquam eget faucibus sit amet, vestibulum nec nibh. Donec varius, nunc vitae bibendum porttitor, purus nisl dictum dolor, nec consequat libero magna a felis. Fusce sollicitudin orci non velit semper consectetur. Phasellus turpis est, mollis a vestibulum vitae, venenatis id eros. Suspendisse interdum dictum ligula, in euismod nisl lacinia id. Pellentesque vel lorem lorem, at vestibulum libero. Phasellus in enim in libero dapibus consectetur vel sed nisi. Aliquam erat volutpat.</p>'));
$projects = $CI->content_model->get_content($projects);

$CI->content_model->new_content($type_pages['id'], 1, 'Thank You!', 'contact/thanks', FALSE, FALSE, '2010-04-10 08:55:55', array('body' => '<p>Thank you for making contact with us!  We will be in touch shortly.</p>'));

// blog
$CI->load->model('blogs/blog_model');
$blog_id = $CI->blog_model->new_blog($type_members['id'], 'Members Area', 'members', 'Get members-only resources for your website.', array(), array(), 'summary', 'content_date', 'DESC', FALSE, $template = 'blog.thtml', 25, array($usergroup));
$blog = $CI->blog_model->get_blog($blog_id);

// RSS feed
$CI->load->model('rss/rss_model');
$feed_id = $CI->rss_model->new_feed($type_members['id'], 'News Feed', 'news_rss', 'All the latest happenings from the field (full access requires a subscription).', array(), array(), 'summary', 'content_date', 'DESC', $template = 'rss_feed.txml');
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
	$collection_id_blueberries = $CI->collections_model->new_collection('Blueberries','Our delicious blend of blueberries make the best pies.', 0, array('image' => 'themes/cubed/images/placeholders/blueberries.jpg'));
	$collection_id_cherries = $CI->collections_model->new_collection('Cherries','Even better than the cough candies!', 0, array('image' => 'themes/cubed/images/placeholders/cherries.jpg'));
	$collection_id_apples = $CI->collections_model->new_collection('Apples','You\'ll only ever eat apple pies again!', 0, array('image' => 'themes/cubed/images/placeholders/apples.jpg'));
	$collection_id_oranges = $CI->collections_model->new_collection('Oranges','Straight from Florida, in the back of a truck!', 0, array('image' => 'themes/cubed/images/placeholders/oranges.jpg'));
	$collection_id_pears = $CI->collections_model->new_collection('Pears','Not for the feint of heart', 0, array('image' => 'themes/cubed/images/placeholders/pears.jpg'));
	$collection_id_strawberries = $CI->collections_model->new_collection('Strawberries','On your cereal, ice cream, pies, and anything else!', 0, array('image' => 'themes/cubed/images/placeholders/strawberries.jpg'));
	
	// products
	$CI->load->model('store/products_model');
	$CI->products_model->new_product('Green Apples', 'Top quality applies from the California coast.', array($collection_id_apples), 18.27, 1, TRUE, FALSE, 0, FALSE, '', TRUE);
	$CI->products_model->new_product('Fresh Blueberries', 'Order a basket of the freshest blueberries out there!', array($collection_id_blueberries), 4.99, 1, TRUE, FALSE, 0, FALSE, '', TRUE);
}

// menus
$CI->load->model('menu_manager/menu_model');
$menu = $CI->menu_model->new_menu('main_menu');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Home', 'home');
$CI->menu_model->add_link($menu, FALSE, 'link', $projects['link_id'], 'Current Projects');
if (module_installed('store')) {
	$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Shop', 'store');
}
if (module_installed('billing')) {
	$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Become a Member', 'subscriptions');
}
$CI->menu_model->add_link($menu, FALSE, 'link', $blog['link_id'], 'Members Area', FALSE, FALSE, array($usergroup));
$CI->menu_model->add_link($menu, FALSE, 'specia', FALSE, 'My Account', 'my_account', FALSE, array(1));
$CI->menu_model->add_link($menu, FALSE, 'link', $about['link_id'], 'Our Story');
$CI->menu_model->add_link($menu, FALSE, 'link', $form['link_id'], 'Contact Us');

$menu = $CI->menu_model->new_menu('footer_menu');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Home', 'home');
$CI->menu_model->add_link($menu, FALSE, 'link', $projects['link_id'], 'Current Projects');
if (module_installed('store')) {
	$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Shop', 'store');
}
if (module_installed('billing')) {
	$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Become a Member', 'subscriptions');
}
$CI->menu_model->add_link($menu, FALSE, 'link', $blog['link_id'], 'Members Area', FALSE, FALSE, array($usergroup));
$CI->menu_model->add_link($menu, FALSE, 'link', $about['link_id'], 'Our Story');
$CI->menu_model->add_link($menu, FALSE, 'link', $form['link_id'], 'Contact Us');
$CI->menu_model->add_link($menu, FALSE, 'link', $feed['link_id'], 'RSS Feed');

$menu = $CI->menu_model->new_menu('footer_menu_2');
$CI->menu_model->add_link($menu, FALSE, 'special', FALSE, 'Control Panel', 'control_panel');