<?php
    require("../../config.php");
	require_once($CFG->dirroot.'/course/lib.php');
	
	$url = new moodle_url('/local/coursecopy/sort_courses.php');
	$PAGE->set_url($url);
	$PAGE->set_context(get_system_context());
	$PAGE->set_pagelayout('admin');

	$displaylist = array();
	$notused = array();
	make_categories_list($displaylist, $notused);

	foreach ($displaylist as $id => $dummy) {

		$category = $DB->get_record('course_categories', array('id' => $id));

		if (!$context = get_context_instance(CONTEXT_COURSECAT, $id)) {
			echo("Category $id not known!");
		}
	
		if (has_capability('moodle/category:manage', $context)) {
			/// Resort the category if requested
	
			if ($courses = get_courses($id, "fullname ASC", 'c.id,c.fullname,c.sortorder')) {
				$i = 1;
				foreach ($courses as $course) {
					$DB->set_field('course', 'sortorder', $category->sortorder+$i, array('id'=>$course->id));
					$i++;
				}
				fix_course_sortorder(); // should not be needed
			}

		} else { echo "Problème de permissions"; }
	}
	echo "Tri terminé";    

?>