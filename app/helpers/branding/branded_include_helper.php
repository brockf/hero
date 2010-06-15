<?php

function branded_include ($file) {
	if (file_exists(BASEPATH . '../branding/custom/' . $file)) {
		return site_url('branding/custom/' . $file);
	}
	else {
		return site_url('branding/default/' . $file);
	}
}