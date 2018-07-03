<?php

/*
Author : 					Frédéric Aebi
Date creation : 			08.02.2011
Date last modification : 	23.03.2018
Description :				Archive object class : used to display archives_run page
*/

/*****************************************************************************
DEFINING CONSTANTS
*****************************************************************************/

define('YEAR', date("Y", time()));
define('SUBYEAR', substr(YEAR, strlen(YEAR)-2, strlen(YEAR)));

define('SPRING_SEMESTER', '[SP '.SUBYEAR.'] [SP '.YEAR.'] [FS '.SUBYEAR.'] [FS '.YEAR.']');
define('AUTUMN_SEMESTER', '[SA '.SUBYEAR.'] [SA '.YEAR.'] [HS '.SUBYEAR.'] [HS '.YEAR.']');

define('PAGE_STEP_1', 1);
define('PAGE_STEP_2', 2);
define('PAGE_STEP_3', 3);

class Archive {

/*****************************************************************************
DEFINING CLASS ATTRIBUTES
*****************************************************************************/

	// Constructor parameters
	private $page_step;

	// Begin checkboxe variables
	private $archive_category_id;
	private $archive_year;
	private $archive_semester_spring;
	private $archive_semester_autumn;
	private $archive_two_semesters;
	private $display_hidden_courses;
	private $display_archived_courses;
	// End checkboxes variables

	// Arrays containing informations about the user selection on STEP 1
	// The courses shown on STEP 2 will depend on this arrays;
	private $fullname_args;

/*****************************************************************************
CONSTRUCTOR
*****************************************************************************/
    public function __construct($step, $year_text, $spr_s, $aut_s, $two_s, $hidden, $archived) {
        global $CFG;
		$this->page_step = $step;
		$this->archive_category_id = $CFG->archive_category;
		$this->fullname_args = array();

		// Affect values using private setters
		$this->setArchiveYear($year_text);
		$this->setSpringSemester($spr_s);
		$this->setAutumnSemester($aut_s);
		$this->setTwoSemesters($two_s);
		$this->setDisplayHiddenCourses($hidden);
		$this->setDisplayArchivedCourses($archived);
	}


/*****************************************************************************
DISPLAY FUNCTIONS
*****************************************************************************/
	public function display() {
		switch ($this->page_step) {
			case PAGE_STEP_2 :
				// Check for errors
				$error_string = $this->checkForm();
				if ($error_string != "") {
					$this->displayHelpText();
					echo "<font color='red'>".$error_string."</font><br/>";
					$this->displaySelectionForm();
				} else {
					echo "<h1>Search results</h1>";
					$this->displayResult();
				}
				break;
			case PAGE_STEP_3 :
				echo "<h1>Archivation confirmation</h1>";
				$this->displayArchivationConfirmation();
				break;
			// Default step (also first step) : show form for selection
			default :
				$this->displayHelpText();
				$this->displaySelectionForm();
				break;
		}
	}

	private function displayHelpText() {
		echo "<h1>Archive courses</h1>";
		echo "<h2>STEP 1</h2>";
		echo "Select the parameters for the courses you want to archive. I will try to find the corresponding courses.<br/>";
		echo "<h2>STEP 2</h2>";
		echo "Here you must confirm my selection.<br/><br/>";

		echo "The research is divided between the results as [SP ".YEAR."] and also some uncertain results as [sp".SUBYEAR."copy] [".YEAR."].<br/>";
		echo "Uncertain motifs are alwalys displayed but certain motifs are immediatly removed depending on the selected settings.<br/>";
		echo "Codes for the semesters are SP, FS, SA, HS.<br/><br/><hr/><br/>";
	}

	private function displaySelectionForm() {

        global $CFG;

		// Begin form
		echo "<form method='post' action=''>";
		echo "<input type='hidden' name='step' value='".PAGE_STEP_2."'>";

		// Begin table
		echo "<table id='archive_table'>";

		// Begin table header
		echo "<tr>";
		echo "<th>Parameters</th>";
		echo "<th>Value</th>";
		echo "</tr>";
		// End table header

		// Begin table rows
		echo "<tr>";
		echo "<td class='odd'>Archive category ID id</td>";
		echo "<td class='odd'>".$CFG->archive_category."</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='even'>year (enter a 2 digit value eg. ".SUBYEAR.")</td>";
		echo "<td class='even'><input type='text' name='YEAR_TEXT' size=2 value='".$this->archive_year."'></td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='odd'>archive semester, spring<br/>".SPRING_SEMESTER."</td>";
		echo "<td class='odd'>";
		$this->checkBox($this->archive_semester_spring, "SPR_S_CHECK");
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='odd'>archive semester, autumn<br/>".AUTUMN_SEMESTER."</td>";
		echo "<td class='odd'>";
		$this->checkBox($this->archive_semester_autumn, "AUT_S_CHECK");
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='odd'>archive 2 semesters<br/>[".(YEAR-1)."-".YEAR."] [".(SUBYEAR-1)."-".SUBYEAR."] ([".(SUBYEAR-1)."] is not safe)</td>";
		echo "<td class='odd'>";
		$this->checkBox($this->archive_two_semesters, "TWO_S_CHECK");
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='even'>0=display hidden courses OR 1=display visible courses</td>";
		echo "<td class='even'>";
		$this->checkBox($this->display_hidden_courses, "HIDDEN_CHECK");
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td class='odd'>display courses in the category Archives already</td>";
		echo "<td class='odd'>";
		$this->checkBox($this->display_archived_courses, "ARCHIVED_CHECK");
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<td></td>";
		echo "<td></td>";
		echo "</tr>";
		// End table rows

		echo "</table>";
		// End table

		echo "<input type='submit' name'submit_btn' value='Search'>";

		// End form
		echo "</form>";
	}

	private function displayResult() {
		global $DB,$CFG;
		$rs = $DB->get_records_sql($this->getSqlQuery(), NULL);

		echo "Courses containing following strings in their name are listed with state \"<font color='green'>Name is OK</font>\" :<br/>";
		for ($i=0;$i<sizeof($this->fullname_args); $i++) {
			echo $this->fullname_args[$i]." ";
		}
		echo "<br/><br/>";

		if (empty($rs)) {
			echo "<b>No course has been found</b>";
			return;
		}

		// Begin form
		echo "<form method='post' action=''>";
		echo "<input type='hidden' name='step' value='".PAGE_STEP_3."'>";

		// Begin table
		echo "<table id='archive_table' style='width:100%;'>";

		// Begin table header
		echo "<tr>";
		echo "<th>Category</th>";
		echo "<th>Shortname</th>";
		echo "<th>Course ID</th>";
		echo "<th>Course name</th>";
		echo "<th>Selection</th>";
		echo "<th>State</th>";
		echo "</tr>";
		// End table header

		// Begin table rows
		$cpt = 1;
		$style = "";
		foreach($rs as $course) {
			$course_category = $DB->get_record("course_categories", array("id" => $course->category));

			// Check if we are on an even or an odd row
			// This is just to stylize the table
			$style = "";
			$category_title = "";
			if ($cpt % 2 == 0)
				$style .= " class='even'";
			else
				$style .= " class='odd'";

			if ($course->category == $CFG->archive_category) {
				$style .= " style='color:#999;'";
				$category_title = "ARCHIVED";
			} else
				$category_title = $course_category->name;

			echo "<tr>";

			echo "<td ".$style.">";
			echo $category_title;
			echo "</td>";

			echo "<td ".$style.">";
			echo $course->shortname;
			echo "</td>";

			echo "<td ".$style.">";
			echo $course->id;
			echo "</td>";

			echo "<td ".$style.">";
			echo "<a href=\"" . $CFG->wwwroot ."/course/edit.php?id=". $course->id . "\">" . $course->fullname . "</a>";
			echo "</td>";

			if ($this->isCourseStateOK($course) == TRUE) {
				echo "<td ".$style.">";
				echo "<input type='checkbox' name='COURSE_TO_ARCHIVE[".$course->id."]' value='COURSE_TO_ARCHIVE[".$course->id."]' checked>";
				echo "</td>";
				echo "<td ".$style.">";
				echo "<font color='green'>name is OK</font>";
				echo "</td>";
			} else {
				echo "<td ".$style.">";
				echo "<input type='checkbox' name='COURSE_TO_ARCHIVE[".$course->id."]' value='COURSE_TO_ARCHIVE[".$course->id."]'>";
				echo "</td>";
				echo "<td ".$style.">";
				echo "<font color='red'>name is NOT OK</font>";
				echo "</td>";
			}

			echo "</tr>";

			$cpt++;
		}
		// End table rows

		echo "</table>";
		// End table

		echo "<input type='submit' name'submit_btn' value='Archive selected courses'>";

		// End form
		echo "</form>";
	}

	private function displayArchivationConfirmation() {
		global $DB, $CFG;
		$rs = optional_param_array('COURSE_TO_ARCHIVE', NULL, PARAM_RAW);

		echo "The following courses have been successfully archived :<br/>";
		echo "<ul>";

		foreach ($rs as $key => $r) {
			$course = $DB->get_record("course", array("id" => $key));

			/*
			INSERT the course to archive with its current category
			into the nte_archives table of the database.
			*/
            $course_to_archive = new stdClass();
            $course_to_archive->archivedcourseid = $course->id;
            $course_to_archive->archivedcategoryid = $course->category;
            $course_to_archive->timemodified = time();
            $insert_result = $DB->insert_record('nte_archives', $course_to_archive);

			/*
			UPDATE the course to archive with category ID 80 (archived courses)
			into the course table of the database.
			*/
			$course->category = $CFG->archive_category;
			$update_result = $DB->update_record('course', $course);

			echo "<li>".$course->fullname."</li>";

		}

		echo "</ul>";

		echo "<form method='post' action=''>";
		echo "<input type='submit' name'submit_btn' value='Back to archive index'>";
		echo "</form>";
	}

/*****************************************************************************
MISC FUNCTIONS
*****************************************************************************/

	/*
	Check form on STEP 1 :
	Some fields are required. This funciton checks if the required fields are not NULL
	*/
	private function checkForm() {
		$required_fields['YEAR'] = $this->archive_year;
		$required_fields['SEMESTER'][] = $this->archive_semester_spring;
		$required_fields['SEMESTER'][] = $this->archive_semester_autumn;
		$required_fields['TWO_SEMESTER'] = $this->archive_two_semesters;

		$error_string = "";

		if ($required_fields['YEAR'] == "")
			$error_string .= "Error : enter a year (2 digit value eg. ".SUBYEAR.")<br/>";

		$nb_selected_semesters = 0;
		foreach ($required_fields['SEMESTER'] as $sem) {
			if ($sem == TRUE)
				$nb_selected_semesters++;
		}

		/*
		There must be at least one semester selected, otherwise, two_semesters must
		be selected. If these conditions are not OK, add an error message
		*/
		if ($nb_selected_semesters == 0) {
			if ($required_fields['TWO_SEMESTER'] == FALSE) {
				$error_string .= 'Error : select at least one specific semester or the "archive 2 semesters" option.<br/>';
			}
		}

		return $error_string;
	}

	/*
	Generation of the checkboxes on STEP 1. Checkboxes are either checked or unchecked.
	This depends on the values selected by the user on STEP 1. If the page is loaded for
	the first time, the checkboxes take the default values (TRUE/FALSE) defined by the
	setters (called in the constructor) of each attribute of this class.
	*/
	private function checkBox($field, $id) {
		if ($field == TRUE)
			echo "<input type='checkbox' name='".$id."' value='".$id."' checked>";
		else
			echo "<input type='checkbox' name='".$id."' value='".$id."'>";
	}


	// Generation of the query depending on the selection of user on STEP 1
	private function getSQLQuery() {
        global $CFG;

		$sql = "SELECT * FROM {course} ";
		$close = "WHERE (";


		/*
		Add also simply the year selected by the user to the fullname_args array,
		because unfortunatly not all courses are correctly saved in the database
		with an appropriate name like [SP 09].
		*/
		$this->fullname_args[] = "20" . $this->archive_year;

		foreach($this->fullname_args as $arg) {
			$sql .= $close." fullname LIKE '%".$arg."%' ";
			$close = "OR";
		}

		if (empty($this->fullname_args))
			$close = "WHERE";
		else
			$close = ") AND";

		if ($this->display_hidden_courses == TRUE)
			$sql .= $close." visible = '1' ";
		else
			$sql .= $close." visible = '0' ";

		$close = "AND";

		if ($this->display_archived_courses == FALSE)
			$sql .= $close." category <> '".$CFG->archive_category."' ";

		return $sql;
	}

	/*
	Checks if the fullname of a course is correct (which means that it contains
	a correct part in its name like [SP 09];
	*/
	private function isCourseStateOK($course) {
		for ($i=0;$i<sizeof($this->fullname_args)-1; $i++) {
			if (strpos($course->fullname, $this->fullname_args[$i]) != 0)
				return TRUE;
		}
		return FALSE;
	}

/*****************************************************************************
SETTERS
- All setters are private because there is no need to call them from the outside.
- All setters are setting the values of the classes attributes. The affected
values depend also on the selection of the user on STEP 1 of the archive_run.php
page.
- All setters are called by the constructor of this class
*****************************************************************************/
	private function setArchiveYear($value) {
		if ($value == "")
			$this->archive_year = "";
		else
			$this->archive_year = $value;
	}

	private function setSpringSemester($value) {
		if ($value == "0") {
			$this->archive_semester_spring = TRUE;
			$this->fullname_args[] = '[SP '.(($this->archive_year) + 1) .']';
			$this->fullname_args[] = '[SP 20'.(($this->archive_year) + 1) .']';
			$this->fullname_args[] = '[FS '.(($this->archive_year) + 1) .']';
			$this->fullname_args[] = '[FS 20'.(($this->archive_year) + 1) .']';
		} else
			$this->archive_semester_spring = FALSE;
	}

	private function setAutumnSemester($value) {
		if ($value == "0") {
			$this->archive_semester_autumn = TRUE;
			$this->fullname_args[] = '[SA '.$this->archive_year.']';
			$this->fullname_args[] = '[SA 20'.$this->archive_year.']';
			$this->fullname_args[] = '[HS '.$this->archive_year.']';
			$this->fullname_args[] = '[HS 20'.$this->archive_year.']';
		} else
			$this->archive_semester_autumn = FALSE;
	}

	private function setTwoSemesters($value) {
		if ($value == "0") {
			$this->archive_two_semesters = TRUE;

			$this->fullname_args[] = '[20'.$this->archive_year.'-20'.(($this->archive_year)+1).']';
			$this->fullname_args[] = '['.$this->archive_year.'-'.(($this->archive_year)+1).']';
			$this->fullname_args[] = '[SA '.$this->archive_year.' - SP '.(($this->archive_year)+1).']';
			$this->fullname_args[] = '[HS '.$this->archive_year.' - FS '.(($this->archive_year)+1).']';
			$this->fullname_args[] = '[SA 20'.$this->archive_year.' - SP 20'.(($this->archive_year)+1).']';
			$this->fullname_args[] = '[HS 20'.$this->archive_year.' - FS 20'.(($this->archive_year)+1).']';
		} else
			$this->archive_two_semesters = FALSE;
	}

	private function setDisplayHiddenCourses($value) {
		if ($value == "0")
			$this->display_hidden_courses = TRUE;
		else {
			if ($this->page_step == PAGE_STEP_1)
				$this->display_hidden_courses = TRUE;
			else
				$this->display_hidden_courses = FALSE;
		}
	}

	private function setDisplayArchivedCourses($value) {
		if ($value == "0")
			$this->display_archived_courses = TRUE;
		else
			$this->display_archived_courses = FALSE;
	}
}

?>
