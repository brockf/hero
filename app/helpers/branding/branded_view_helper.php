<?php

function branded_view ($file) {
	if (file_exists(BASEPATH . '../branding/custom/views/' . $file . '.php')) {
		return '../../../branding/custom/views/' . $file;
	}
	else {
		return $file;
	}
}