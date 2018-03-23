<?php

/*
Author : 					Frédéric Aebi
Date creation : 			28.03.2011
Date last modification : 	23.03.2018
Description :				Archive Request Pending object class
*/

define('NTE_TAB', chr(9)) ;
define('NTE_RET', chr(13)) ;


class Archive {

/*****************************************************************************
DEFINING CLASS ATTRIBUTES
*****************************************************************************/

	private $requesters;
	private $courses;
	private $comments;


/*****************************************************************************
CONSTRUCTOR
*****************************************************************************/
    public function __construct($nte_duplicatecourses) {
		$this->init($nte_duplicatecourses);
	}


/*****************************************************************************
FUNCTIONS
*****************************************************************************/

	// Initializes class attributes
	public function init($nte_duplicatecourses) {
		global $DB;
		foreach ($nte_duplicatecourses as $key => $nte_duplicatecourse) {
			$this->requesters[$nte_duplicatecourse->reusecourseid] = $DB->get_record("user", array('id' => $nte_duplicatecourse->requesteruserid));
			$this->courses[$nte_duplicatecourse->reusecourseid] = $DB->get_record("course", array('id' => $nte_duplicatecourse->reusecourseid));
			$this->comments[$nte_duplicatecourse->reusecourseid] = $nte_duplicatecourse->reusecomment;
		}
	}

	// Function used to display the content of the page
	public function display() {

		global $DB;
		global $CFG;
		global $USER;

		echo "<h1>Administrating pending requests ";
		echo "(see <a href='".$CFG->wwwroot."/local/coursecopy/archives_request_pending_logfile.php' target='_blank'>logfile</a>)</h1>";

		if (empty($this->courses)) {
			echo "<b>No course has been found</b>";
			return;
		}

		// Begin form
		echo "<form method='post'>";

		// Help text
		echo "1) First select a year and check the new names.<br/>";
		echo "2) Second correct the new names and select the courses to duplicate.<br/>";
		echo "3) Enter the current year to search (enter a 2 digit value eg. 09):&nbsp;";
		echo "<input type='text' onChange='auto_names(this.value)' name='session_year' value='' size=2>";
		echo "&nbsp;required for automatic rename.<br/><br/>";
		echo "<input type='checkbox' name='debug' checked>&nbsp;&nbsp;Enable or disable debug mode.<br/>";
		echo "<input type='checkbox' name='log_file_reset'>&nbsp;&nbsp;Enable or disable logFileReset mode.<br/><br/>";

		// Begin table
		echo "<table id='archive_table' style='width:100%; font-size:14px;'>";

		// Begin table header
		echo "<tr>";
		echo "<th>Shortname</th>";
		echo "<th>Fullname</th>";
		echo "<th>Category</th>";
		echo "<th>User</th>";
		echo "<th>Comment</th>";
		echo "<th>Approve</th>";
		echo "<th>New name</th>";
		echo "<th>Delete</th>";
		echo "<th></th>";
		echo "<th></th>";
		echo "</tr>";
		// End table header

		// Begin table rows
		$cpt = 1;
		$style = "";
		foreach($this->courses as $key => $course) {
			$course_category = $DB->get_record("course_categories", array("id" => $course->category));

			// Check if we are on an even or an odd row
			// This is just to stylize the table
			$style = "";
			if ($cpt % 2 == 0)
				$style .= " class='even'";
			else
				$style .= " class='odd'";

			echo "<tr>";

			echo "<td ".$style." style='width:150px;'>";
			echo $course->shortname;
			echo "</td>";

			echo "<td ".$style." style='width:150px;'>";
			echo "<a href='".$CFG->wwwroot."/course/view.php?id=".$key."'>".$course->fullname."</a>";
			echo "</td>";

			echo "<td ".$style." style='width:75px;'>";
			echo format_string($course_category->name);
			echo "</td>";

			echo "<td ".$style." style='width:75px;'>";
			echo $this->requesters[$key]->id." /<a href='mailto:".$this->requesters[$key]->email."'>".$this->requesters[$key]->firstname." ".$this->requesters[$key]->lastname."</a>";
			echo "</td>";

			echo "<td ".$style." style='width:150px;'>";
			echo $this->comments[$key];
			echo "</td>";

			echo "<td ".$style." style='width:50px; text-align:center;'>";
			echo "<input type='checkbox' id='approved_courses[".$course->id."]' name='approved_courses[".$course->id."]' value='approved_courses[".$course->id."]'>";
			echo "</td>";

			echo "<td ".$style.">";
			echo "<input type='text' style='width:400px; height:25px;' id='approved_course_names[".$course->id."]' name='approved_course_names[".$course->id."]' onChange='auto_checkbox(".$course->id.")'>";
			echo "</td>";

			echo "<td ".$style." style='width:50px; text-align:center;'>";
			echo "<input type='checkbox' name='deleted_courses[".$course->id."]' value='deleted_courses[".$course->id."]'>";
			echo "</td>";

			echo "<td ".$style.">";
			echo "<a href='".$CFG->wwwroot."/backup/backup.php?id=".$course->id."'><img src='".$CFG->wwwroot."/theme/image.php?theme=standard&image=t%2Fbackup&rev=220'/></a>";
			echo "</td>";

			$context = context_course::instance($course->id);

			echo "<td ".$style.">";
			echo "<a href='".$CFG->wwwroot."/backup/restorefile.php?contextid=".$context->id."'><img src='".$CFG->wwwroot."/theme/image.php?theme=standard&image=t%2Frestore&rev=220'/></a>";

			echo "</td>";

			echo "</tr>";

			$cpt++;
		}
		// End table rows

		echo "</table>";
		// End table

		echo "<input type='submit' name'submit_btn' value='Manage selected courses'>";

		// End form
		echo "</form>";
	}

	// Function used to display debug content
	public function display_debug($debug, $debug_into_file, $log_file_reset) {

		global $DB;
		global $CFG;

		$link = $CFG->wwwroot.'/local/coursecopy/archives_request_pending.php';

		$debug_result = NTE_RET."**************************************************".NTE_RET;
		$debug_result .= "Date : ".date("Y-m-d H:i:s", time()).NTE_RET;
		$debug_result .= "Restore result".NTE_RET;

		echo "<h1>Administrating pending requests ";
		echo "(see <a href='".$CFG->wwwroot."/local/coursecopy/archives_request_pending_logfile.php' target='_blank'>logfile</a>)</h1>";
		echo "<h1>Restore result</h1>";

		if (! empty($debug['restore'])) {
			echo "The following courses have been successfully restored.<br/><br/>";

			// Begin table
			echo "<table id='archive_table'>";

			// Begin table header
			echo "<tr>";
			echo "<th>New ID</th>";
			echo "<th>Category</th>";
			echo "<th>Fullname</th>";
			echo "<th>Mail</th>";
			echo "</tr>";
			// End table header

			$debug_result .= "New ID".NTE_TAB;
			$debug_result .= "Category".NTE_TAB;
			$debug_result .= "Mail sent".NTE_TAB;
			$debug_result .= "Course".NTE_RET;

			// Begin table rows
			$cpt = 1;
			$style = "";
			foreach($debug['restore'] as $result) {
				$category = $DB->get_record("course_categories", array("id" => $result['course']['category']));

				// Check if we are on an even or an odd row
				// This is just to stylize the table
				$style = "";
				if ($cpt % 2 == 0)
					$style .= " class='even'";
				else
					$style .= " class='odd'";

				echo "<tr>";

				echo "<td ".$style.">";
				echo $result['course']['id'];
				echo "</td>";

				echo "<td ".$style.">";
				echo format_string($category->name);
				echo "</td>";

				echo "<td ".$style.">";
				echo "<a href='".$CFG->wwwroot."/course/view.php?id=".$result['course']['id']."'>".$result['course']['fullname']."</a>";
				echo "</td>";

				echo "<td ".$style.">";
				echo $result['mail'];
				echo "</td>";

				echo "</tr>";

				$debug_result .= $result['course']['id'].NTE_TAB;
				$debug_result .= $result['course']['category'].NTE_TAB.NTE_TAB;
				$debug_result .= $result['mail'].NTE_TAB.NTE_TAB;
				$debug_result .= $result['course']['fullname'].NTE_RET;

				$cpt++;
			}
			// End table rows

			echo "</table>";
			// End table
		} else {
			echo "There was no course to restore.<br/><br/>";
			$debug_result .= "There was no course to restore.".NTE_RET;
		}

		echo "<h1>Delete result</h1>";
		$debug_result .= "Delete result".NTE_RET;

		if (! empty($debug['delete'])) {
			echo "The following courses have been deleted from the pending requests list.<br/><br/>";

			// Begin table
			echo "<table id='archive_table'>";

			// Begin table header
			echo "<tr>";
			echo "<th>ID</th>";
			echo "<th>Category</th>";
			echo "<th>Fullname</th>";
			echo "</tr>";
			// End table header

			$debug_result .= "ID".NTE_TAB;
			$debug_result .= "Category".NTE_TAB;
			$debug_result .= "Course".NTE_RET;

			// Begin table rows
			$cpt = 1;
			$style = "";
			foreach($debug['delete'] as $result) {
				$category = $DB->get_record("course_categories", array("id" => $result['category']));

				// Check if we are on an even or an odd row
				// This is just to stylize the table
				$style = "";
				if ($cpt % 2 == 0)
					$style .= " class='even'";
				else
					$style .= " class='odd'";

				echo "<tr>";

				echo "<td ".$style.">";
				echo $result['id'];
				echo "</td>";

				echo "<td ".$style.">";
				echo format_string($category->name);
				echo "</td>";

				echo "<td ".$style.">";
				echo "<a href='".$CFG->wwwroot."/course/view.php?id=".$result['id']."'>".$result['fullname']."</a>";
				echo "</td>";

				echo "</tr>";

				$debug_result .= $result['id'].NTE_TAB;
				$debug_result .= $result['category'].NTE_TAB.NTE_TAB;
				$debug_result .= $result['fullname'].NTE_RET;

				$cpt++;
			}
			// End table rows

			echo "</table>";
			// End table
		} else {
			echo "There was no course to delete.<br/><br/>";
			$debug_result .= "There was no course to delete.".NTE_RET;
		}

		// Back button
		echo '<input type="button" onClick="window.location.href=\''.$link.'\'" value="Back">';

		// Save $debug_result into file
		if ($debug_into_file == "on") {

			$browser = get_file_browser();
			$context = context_system::instance();

			$fs = get_file_storage();
			$files = $fs->get_area_files('1', 'course', 'backup', '0');

			// Reset debug file if $log_file_reset is on
			if ($log_file_reset == "on") {
				if (! empty($files)) {
					foreach ($files as $f) {
						$f->delete();
					}
				}
			} else {
				if (! empty($files)) {
					foreach ($files as $f) {
						$debug_result = $f->get_content().$debug_result;
						$f->delete();
					}
				}
			}

			$fileinfo = array(
				'contextid' => '1',
				'component' => 'course',
				'filearea' => 'backup',
				'itemid' => '0',
				'filepath' => '/',
				'filename' => 'archives_request_pending_logfile.txt');

			$fs->create_file_from_string($fileinfo, $debug_result);
		}
	}

/*****************************************************************************
SETTERS AND GETTERS
*****************************************************************************/
	public function getRequesters() {
		return $this->requesters;
	}

	public function getCourses() {
		return $this->courses;
	}

	public function getComments() {
		return $this->comments;
	}
}

?>
