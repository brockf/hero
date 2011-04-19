<?php

/*
* Admin Link
*
* Generates an admin link with the link icon, for use in dataset listings
*
* @param string $link
*
* @return string HTML for the link
*/
function admin_link ($link) {
	return '<a href="' . $link . '" target="_blank"><img src="' . branded_include('images/link.png') . '" alt="Open in Browser" title="Open in Browser" /></a>';
}