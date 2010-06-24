<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance();
$CI->load->model('settings/settings_model');

$CI->settings_model->make_writeable_folder($CI->config->item('path_image_thumbs'));