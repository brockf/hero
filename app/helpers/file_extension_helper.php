<?php

function file_extension ($file) {
	return strtolower(end(explode(".", $file)));
}