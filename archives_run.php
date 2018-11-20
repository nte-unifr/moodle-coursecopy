<?php

/*
Author : 					Fr�d�ric Aebi
Date creation : 			08.02.2011
Date last modification : 	10.02.2011
Description :				Allows a moodle administrator to archive multiple courses (for example all the
							courses of a semester of a year).
							- The category of the archived courses is set to 80 (stands for archived courses)
							- An entry is also made in the TODO table of the database just to know the old 							category id of the course
*/

define('PAGE_TITLE', 'Archive/move courses');

require_once('../../config.php');
//require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once('archives_run.class.php');

/*
Style for the table :
I did not wanted to screw up some moodle CSS files, so I hardcoded it... sorry
*/
echo "<style>#archive_table{border:solid 1px #000;}#archive_table th {text-align:left;background-color:#F0F0F0;border-bottom:solid 1px #000;}#archive_table td.odd{background-color:#FFF;}#archive_table td.even{background-color:#F0F0F0;}</style>";

// Checking access
require_capability('moodle/site:approvecourse', context_system::instance());

// Getting params
$step 			 = 	optional_param('step', PAGE_STEP_1, PARAM_INT);		// Of type : Integer
$course_category = 	optional_param('COURSE_CATEGORY', $CFG->archive_category, PARAM_INT);	// Of type : Integer
$year_text 		 = 	optional_param('YEAR_TEXT', '', PARAM_INT);	// Of type : Integer
$spr_s 			 = 	optional_param('SPR_S_CHECK', '', PARAM_INT);		// Of type : Integer
$aut_s 			 = 	optional_param('AUT_S_CHECK', '', PARAM_INT);		// Of type : Integer
$two_s 			 = 	optional_param('TWO_S_CHECK', '', PARAM_INT);		// Of type : Integer
//$hidden 		 = 	optional_param('HIDDEN_CHECK', '', PARAM_INT);		// Of type : Integer
//$archived 		 = 	optional_param('ARCHIVED_CHECK', '', PARAM_INT);	// Of type : Integer

// Setting up page layout
$PAGE->set_context(context_system::instance());
$url = new moodle_url('/backup/archives_run.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(PAGE_TITLE);
$PAGE->set_heading(PAGE_TITLE);
$PAGE->navbar->add('Archive/move courses');

$archive_obj = new Archive($step, $year_text, $spr_s, $aut_s, $two_s, $course_category);

$PAGE->navbar->add('Step '.$step);

echo $OUTPUT->header();
$archive_obj->display();
echo $OUTPUT->footer();

?>
