<?php

/*
Author : 					Frédéric Aebi
Date creation : 			20.04.2011
Date last modification : 	20.04.2011
*/

// Needed to check if coursenames contain a valid year
function strpos_array($haystack, $needles) {
	if (is_array($needles)) {
		foreach ($needles as $str) {
			if ( is_array($str))
				$pos = strpos_array($haystack, $str);
			else
				$pos = strpos($haystack, $str);

			if ($pos !== FALSE)
				return $pos;
		}
	} else
		return strpos($haystack, $needles);
}

// Mail strings
// At the end of the restore, a mail is sent to the teacher who asked for
// a backup/restore. Here are the different language strings.
$mail_str['en']['course_duplicated_subject'] = 'Moodle UNIFR : Course has been copied';

$mail_str['en']['course_duplicated_message_begin'] = 'Hello,<br/><br/>In agreement with your request, a copy of the course was created. Here is the new course:<br/><br/>';
$mail_str['en']['course_duplicated_message_end'] = '<br/><br/>If you have any questions, please refer to an administrator of Moodle : <a href="mailto:moodle@unifr.ch">moodle@unifr.ch</a>.<br/><br/>Best regards,<br/><br/>Jacques Monnard,<br/>Centre NTE';
$mail_str['fr']['course_duplicated_subject'] = 'Moodle UNIFR : Cours Copié';

$mail_str['fr']['course_duplicated_message_begin'] = 'Bonjour,<br/><br/>Suite à votre demande, une copie de cours a été effectuée. Voici le nouveau cours:<br/><br/>';
$mail_str['fr']['course_duplicated_message_end'] = '<br/><br/>Pour toute question, contactez les administrateurs de Moodle : <a href="mailto:moodle@unifr.ch">moodle@unifr.ch</a>.<br/><br/>Avec nos meilleures salutations,<br/><br/>Jacques Monnard,<br/>Centre NTE';
$mail_str['de']['course_duplicated_subject'] = 'Moodle UNIFR : Kurs wurde kopiert';

$mail_str['de']['course_duplicated_message_begin'] = 'Guten Tag,<br/><br/>Aufgrund Ihres Antrags wurde eine Kopie des Kurses erstellt. Hier ist der neue Kurs:';
$mail_str['de']['course_duplicated_message_end'] = '<br/><br/>Falls Sie Fragen haben, kontaktieren Sie bitte die Administratoren von Moodle : <a href="mailto:moodle@unifr.ch">moodle@unifr.ch</a>.<br/><br/>Mit freundlichen Grüssen,<br/><br/>Jacques Monnard,<br/>Centre NTE';

/*
Styles and scripts (javascript) :
I did not wanted to screw up some moodle CSS or JS files, so I hardcoded it... sorry
*/
echo "<style>#archive_table{border:solid 1px #000;}#archive_table th {text-align:left;background-color:#F0F0F0;border-bottom:solid 1px #000;}#archive_table td.odd{background-color:#FFF;}#archive_table td.even{background-color:#F0F0F0;}</style>";
echo "<script language='javascript'>function auto_checkbox(key){if(document.getElementById('approved_course_fullnames['+key+']').value != '') document.getElementById('approved_courses['+key+']').checked = true; else document.getElementById('approved_courses['+key+']').checked = false;}</script>";

?>


<!--
The following script is needed to generate new course names automatically
-->

<script language="javascript">

function auto_names(year) {
	if (year != '' && year != 0) {
		<?php foreach($archive->getCourses() as $key => $course){if(strpos_array($course->fullname,array('[SP','[SA','[FS','[HS','[SS','[AS'))){?>
			document.getElementById(<?php echo "'approved_course_fullnames[".$key."]'";?>).value = "<?php echo str_replace('"','\"',substr($course->fullname, 0, (strlen($course->fullname)-3)));?>"+year+"]";
			document.getElementById(<?php echo "'approved_courses[".$key."]'";?>).checked = false;
		<?php } else {?>
			document.getElementById(<?php echo "'approved_course_fullnames[".$key."]'";?>).value = "<?php echo str_replace('"','\"',$course->fullname);?>";
			document.getElementById(<?php echo "'approved_course_fullnames[".$key."]'";?>).style.background = "#FF0000";
			document.getElementById(<?php echo "'approved_courses[".$key."]'";?>).checked = false;
		<?php }}?>
	} else {
		<?php foreach($archive->getCourses() as $key => $course){?>
			document.getElementById(<?php echo "'approved_course_fullnames[".$key."]'";?>).value = '';
			document.getElementById(<?php echo "'approved_courses[".$key."]'";?>).checked = false;
			document.getElementById(<?php echo "'approved_course_fullnames[".$key."]'";?>).style.background = "#FFFFFF";
		<?php }?>
	}
}

</script>
