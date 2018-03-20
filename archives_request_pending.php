<?php

/*
Author : 					Frédéric Aebi
Date creation : 			28.03.2011
Date last modification : 	23.05.2012
Description :				archives_request_pending.php file
*/
define('PAGE_TITLE', 'Moodle 2.0 Test @ NTE');
define('ARCHIVE_CATEGORY', 75);
define('COURSE_CONTEXT_LEVEL', 50);
define('TEACHER_ROLE_ID', 3);
define('TEACHERROLES', '3,4,9');

require_once('../../config.php');
require_once($CFG->dirroot . '/course/externallib.php');
require_once("archives_request_pending.class.php");

//global $DB;

// Checking capability
require_capability('moodle/site:approvecourse', context_system::instance());

// Setting parameters
$requester_ids = optional_param_array('requester_id', NULL, PARAM_RAW);
$approved_courses =	optional_param_array('approved_courses', NULL, PARAM_RAW);
$approved_course_names = optional_param_array('approved_course_names', NULL, PARAM_RAW);
$deleted_courses =	optional_param_array('deleted_courses', NULL, PARAM_RAW);
$session_year =	optional_param('session_year', '', PARAM_CLEANHTML);
$debug_into_file = optional_param('debug', '', PARAM_CLEANHTML);
$log_file_reset = optional_param('log_file_reset', '', PARAM_CLEANHTML);

$archive = new Archive($DB->get_records("nte_duplicatecourse", NULL));
$courses_to_duplicate = $archive->getCourses();
$requesters = $archive->getRequesters();

require_once("coursecopylib.php");


/****************************************************************************
BACKUP AND RESTORE PROCESS
*****************************************************************************/

// Debug variables
// Allow a nice output of what happens here
$debug = array();
$errors = array();

if (!empty($approved_courses)) {
	foreach($approved_courses as $key => $approved_course) {
		if ($approved_course_names[$key] <> '') {

            $userto = $requesters[$key];

			// Create new course
			$new_category = $courses_to_duplicate[$key]->category;
			// If the course has been archived before, we need to get the category
			// that it had before the archivation.
			if ($new_category == ARCHIVE_CATEGORY) {
				$prev_archived_course = $DB->get_record("nte_archives", array("archivedcourseid" => $key));
				if ($prev_archived_course) {
					$new_category = $prev_archived_course->archivedcategoryid;
				}
			}

			$new_shortname = $courses_to_duplicate[$key]->shortname." [".date('Y-m-d H:i:s', time())."]";
			$new_fullname = $approved_course_names[$key];

			$options = array(
				array ('name' => 'activities', 'value' => 1),
				array ('name' => 'blocks', 'value'  => 1),
				array ('name' => 'filters', 'value' => 1),
				array ('name' => 'users', 'value' => 0),
			    array ('name' => 'role_assignments', 'value' => 0),
				array ('name' => 'comments', 'value' => 0),
				array ('name' => 'userscompletion', 'value' => 0),
				array ('name' => 'logs', 'value' => 0),
				array ('name' => 'grade_histories', 'value' => 0)
			);

			try {
				$newcourse = core_course_external::duplicate_course($key, $new_fullname, $new_shortname,
					$new_category, 0, $options);
			} catch (exception $e) {
				// Some debugging information to see what went wrong
			//     echo '<pre>';
			//     print_r($e);
			//     exit;
				$errors[] = 'Impossible de dupliquer le cours ('.$e->errorcode.')';
				$errors[] = 'Action interrompue.';
			}

			if (count($errors)) {
    			include('./course-duplicate-error.php');
    			exit;
			}

/*			$new_course = $DB->get_record('course', array('id'=>$courseid));
			$new_course->startdate = time();
			$new_course->timecreated = time();
			$new_course->timemodified = time();
			$DB->update_record('course', $new_course); */

			$enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'status' => 0, 'courseid' => $key));
			if ($enrol) {
				// let's make an identical "enrol_manual" plugin into target course
				$newenrol = clone($enrol);
				unset($newenrol->id);
				$newenrol->courseid = $newcourse['id'];
				$newcourseenrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $newcourse['id']));
				if ($newcourseenrol) {
					$newenrol->id = $newcourseenrol->id;
					$DB->update_record('enrol', $newenrol);
				}
				else {
					$newenrol->id = $DB->insert_record($newenrol);
				}
			}
			else {
				$errors[] = 'Aucun plugin d\'inscription manuelle n\'a été trouvé dans le cours originel; aucun enseignant n\'a été réinscrit.';
			}
			// now, let's figure out who was enrolled...
			$enrolled_users = $DB->get_records('user_enrolments', array('enrolid' => $enrol->id));
			$course_context = context_course::instance($key);
			// ...and who among these are teachers
			$teachers = array();
			foreach ($enrolled_users as $enrolled_user) {
				$teachers[] = $DB->get_records_sql("SELECT * FROM {role_assignments} WHERE userid = {$enrolled_user->userid} AND roleid IN (".TEACHERROLES.") AND contextid = {$course_context->id};");
			}
			if (count($teachers)) {
		//         $errors[] = 'ct='.count($teachers);
		//         $errors[] = '<pre>'.print_r($teachers, true).'</pre>';
				require_once($CFG->dirroot . '/enrol/manual/lib.php');
				$enrol_manual_instance = new enrol_manual_plugin();
				foreach ($teachers as $teacher) {
					$role_assignment = array_pop($teacher);
					if (!is_object($role_assignment)) {
                        continue;
                    }
					$enrol_manual_instance->enrol_user($newenrol, $role_assignment->userid, $role_assignment->roleid);
				}
                $enrol_manual_instance->enrol_user($newenrol, $userto->id, $role_assignment->roleid);
			}
			else {
				$errors[] = 'Aucun enseignant à réinscrire trouvé.';
			}

			// For debug
			$debug['restore'][$key]['course']['id'] = $newcourse['id'];
			$debug['restore'][$key]['course']['category'] = $new_category;
			$debug['restore'][$key]['course']['fullname'] = $new_fullname;

			// Delete course from nte_duplicatecourse table
			$DB->delete_records("nte_duplicatecourse", array('reusecourseid' => $key));

			// Sending email to teacher to inform him that the new course
			// has been successfully created

/*			$userto = $DB->get_record("user", array("id" => $teacher->id));*/

			$userfrom = get_admin();
			$subject = $mail_str[$userto->lang]['course_duplicated_subject'];
			$link = $CFG->wwwroot.'/course/view.php?id='. $newcourse['id'];
			$messagehtml = $mail_str[$userto->lang]['course_duplicated_message_begin'] . "<a href=\"$link\">$link</a>" . $mail_str[$userto->lang]['course_duplicated_message_end'];

			email_to_user($userto, $userfrom, $subject, $messagehtml, $messagehtml);

			$debug['restore'][$key]['mail'] = "OK";
		}
	}
}


/****************************************************************************
DELETE COURSES FROM nte_duplicatecourse TABLE
*****************************************************************************/
if (!empty($deleted_courses)) {

	$this_course = $archive->getCourses();

	foreach($deleted_courses as $key => $deleted_course) {
		$DB->delete_records("nte_duplicatecourse", array('reusecourseid' => $key));
		$debug['delete'][$key]['id'] = $this_course[$key]->id;
		$debug['delete'][$key]['category'] = $this_course[$key]->category;
		$debug['delete'][$key]['fullname'] = $this_course[$key]->fullname;
	}
}

/****************************************************************************
SETTING UP THE PAGE LAYOUT
*****************************************************************************/

$url = new moodle_url('/local/coursecopy/archives_request_pending.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Course Copy Requests");
$PAGE->set_heading("Course Copy Requests");
$PAGE->navbar->add('Pending requests');

echo $OUTPUT->header();


if (empty($debug))
	$archive->display();
else
	$archive->display_debug($debug, $debug_into_file, $log_file_reset);

echo $OUTPUT->footer();

?>
