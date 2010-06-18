<?php

function valid_domain ($domain) {
	if (!preg_match('/^[a-zA-Z0-9-]+\.[a-zA-Z.]{2,5}$/i', $domain)) {
		return FALSE;
	}
	else {
		return TRUE;
	}
}