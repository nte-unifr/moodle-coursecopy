<?php

/*
Author : 					Frédéric Aebi
Date creation : 			22.03.2011
Date last modification : 	22.03.2011
Description :				archives_request.php file : used by the teachers to ask an administrator for 
							a copy of a course.	
*/

require_once('../../config.php');

/*
Style for the table :
I did not wanted to screw up some moodle CSS files, so I hardcoded it... sorry
*/
echo "<style>#archive_table{border:solid 1px #000;}#archive_table th {text-align:left;background-color:#F0F0F0;border-bottom:solid 1px #000;}#archive_table td.odd{background-color:#FFF;}#archive_table td.even{background-color:#E4EDF2;} th,td {padding:5px} </style>";

global $USER;
global $DB;
global $CFG;

function isValidForm($form) {
    return (isset($_POST['okButton'])) ? true : false;
}

// Getting params
$courseid = required_param('id', PARAM_INT);

// Getting data about the course and checking capability
if (! $course = $DB->get_record("course", array("id" => $courseid)))
    print_error("Course ID is incorrect!");
	
if (! $course_category = $DB->get_record("course_categories", array("id" => $course->category)))
    print_error("Category not known!");

// check if course is pending
$coursePending = $DB->get_record("nte_duplicatecourse", array("reusecourseid" => $courseid),"id");

require_capability('moodle/course:update', context_course::instance($courseid));

// Back to course link
$link = $CFG->wwwroot.'/course/view.php?id='.$courseid;

// Setting up page layout

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);

$url = new moodle_url('/local/coursecopy/archives_request.php?id='.$courseid);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('reuseCourseRequest', 'local_coursecopy'));
//$PAGE->set_context('CONTEXT_COURSE');

// Getting strings
$str_title = get_string('reuseCourseRequest', 'local_coursecopy');
$str_course = get_string('course_title', 'local_coursecopy');
$str_category = get_string('category', 'local_coursecopy');
$str_comment = get_string('comment', 'local_coursecopy');
$str_pending = get_string('coursePending', 'local_coursecopy');
$str_intro1 = get_string('reuseRequestIntro1', 'local_coursecopy');
$str_intro2 = get_string('reuseRequestIntro2', 'local_coursecopy');
$str_okButton = "OK";
$str_backButton = get_string('backtocourse', 'local_coursecopy');


echo $OUTPUT->header();

if (($form = data_submitted()) and confirm_sesskey()) {
	if (isValidForm($form)) {
		$course_to_duplicate = new stdClass();
		$course_to_duplicate->reusecourseid = $course->id;
        $course_to_duplicate->requesteruserid = $USER->id;
        $course_to_duplicate->reusecomment = $form->reusecomment;
        $course_to_duplicate->timemodified = time();
		
		if ($DB->insert_record('nte_duplicatecourse', $course_to_duplicate)) {
			
			$emailSubject =  'Moodle - demande copie de cours';
			$emaiMessage = 'Une copie de cours a été demandée. Liste des demandes en cours : '.$CFG->wwwroot.'/local/coursecopy/archives_request_pending.php';
			
            $admin_user = get_admin();
			$admin_user->email = 'moodle@unifr.ch';
			if (email_to_user($admin_user, $admin_user, $emailSubject, $emaiMessage))
				notice(get_string('feedback_ok','local_coursecopy'), $link);
			else
				notice(get_string('feedback_not_ok','local_coursecopy'), $link);
		}
		
		echo $OUTPUT->footer();
		exit();
	} else {
		redirect($CFG->wwwroot);
	}
} else {
	//$form = stripslashes_safe($form);
}


/********************************************************************************
BEGIN HTML
*********************************************************************************/
?><legend><?php echo $str_title; ?></legend>
<?php
if ($coursePending) {
    	echo '<p style="color:red; font-weight:bold; font-size:120%;">' . $str_pending . '</p>';
    	echo '<a href="' . $link . '">' . $str_backButton . '</a>';
} else {  
?>  
<?php echo $str_intro1."<br/>"; ?>
<?php echo $str_intro2."<br/><br/>"; ?>
<form method="post" action="archives_request.php?id=<?php echo $courseid; ?>" name="request">
	<input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>"/>
    <table id="archive_table" width="60%">
        <tr>
        	<th><label><?php echo $str_course." :"; ?></label></th>
        	<th><label><strong><?php  echo $course->fullname; ?></strong><label></th>
        <tr/>
        <tr>
        	<td class='odd'><label><?php  echo $str_category." :"; ?></label></td>
        	<td class='odd'><label><?php  echo format_string($course_category->name); ?></label></td>
        </tr>
        <tr valign="top">
        	<td class='odd'><label><?php echo $str_comment." :"; ?></label></td>
        	<td class='odd'><textarea name="reusecomment" rows="5"></textarea></td>
        </tr>
        <tr>
        	<td class='odd'>&nbsp;</td>
        	<td class='odd'>
            	<input type="submit" name="okButton" value="<?php echo $str_okButton; ?>" />
                &nbsp;&nbsp;&nbsp;
                <a href="<?php echo $link; ?>"><?php echo $str_backButton; ?></a>
            </td>
        </tr>
    </table>
</form><?php
}
/********************************************************************************
END HTML
*********************************************************************************/

echo $OUTPUT->footer();

?>