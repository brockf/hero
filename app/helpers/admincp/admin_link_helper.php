<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Admin Link
*
* Generates an icon link to the frontend of your site from the control panel.
*
* @param string $link
*
* @return string HTML for the link
*/

function admin_link ($link) {
	return '<a href="' . $link . '" target="_blank"><img src="' . branded_include('images/link.png') . '" alt="Open in Browser" title="Open in Browser" /></a>';
}