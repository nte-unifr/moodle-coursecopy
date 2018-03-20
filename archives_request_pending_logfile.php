<?php

require_once('../../config.php');

$browser = get_file_browser();
$context = context_system::instance();

$fs = get_file_storage();
$files = $fs->get_area_files('1', 'course', 'backup', '0');

if (! empty($files)) {
	foreach ($files as $f) {
		echo "<pre>";
		print_r($f->get_content());
		echo "</pre>";
	}
}

?>