<?php

function file_extension ($file) {
	return end(explode(".", $file));
}