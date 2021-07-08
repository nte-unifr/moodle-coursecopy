<?php

function local_coursecopy_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('moodle/course:update', context_course::instance($course->id))) {
        $coursecopynode = navigation_node::create(get_string('ask_for_copy', 'local_coursecopy'),$url = new moodle_url('/local/coursecopy/archives_request.php', array('id'=>$course->id)), navigation_node::TYPE_CUSTOM, null, null,new pix_icon('t/copy', ''));
        //print_r($navigation);
        $navigation->add_node($coursecopynode,'import');
    }
}

?>