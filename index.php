<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * Configurable Reports - A Moodle block/report for creating customizable reports
 *
 * report_configurablereports version information.
 *
 * @package   report_configurablereports
 * @author:     Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date:       2013-09-07
 *
 * @copyright  Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);       // course id
$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

// needed to setup proper $COURSE
require_login($course);

//setting page url
$PAGE->set_url('/report/configurablereports/index.php', array('id'=>$id));
//setting page layout to report
$PAGE->set_pagelayout('report');

if ($course->id == SITEID)
    $context = context_system::instance();
else
    $context = context_course::instance($course->id);

//checking if user is capable of viewing this report in $context
require_capability('report/configurablereports:view', $context);

//global $DB, $USER, $CFG ,$COURSE;

require_once($CFG->dirroot."/blocks/configurable_reports/locallib.php");

// Site (Shared) reports
$reports = $DB->get_records('block_configurable_reports',array('courseid' => SITEID),'name ASC');

if ($reports) {
    foreach($reports as $report){
        if($report->visible && cr_check_report_permissions($report, $USER->id, $context)){
            $rname = format_string($report->name);
            $items[] = '<a href= "'.$CFG->wwwroot.'/blocks/configurable_reports/viewreport.php?id='.$report->id.'&courseid='.$course->id.'" alt="'.$rname.'">'.$rname.'</a>';
        }
    }
    echo "<hr/>";
}

// Course reports
$reports = $DB->get_records('block_configurable_reports',array('courseid' => $course->id),'name ASC');

if ($reports) {
    foreach($reports as $report){
        if($report->visible && cr_check_report_permissions($report, $USER->id, $context)){
            $rname = format_string($report->name);
            $items[] = '<a href= "'.$CFG->wwwroot.'/blocks/configurable_reports/viewreport.php?id='.$report->id.'&courseid='.$course->id.'" alt="'.$rname.'">'.$rname.'</a>';
        }
    }
}

if(has_capability('block/configurable_reports:managereports', $context)
    || has_capability('block/configurable_reports:manageownreports', $context)){
    $items[] = '<hr/><a href="'.$CFG->wwwroot.'/blocks/configurable_reports/managereport.php?courseid='.
        $course->id.'">'.(get_string('managereports','block_configurable_reports')).'</a>';
}


//making log entry
add_to_log($course->id, 'course', 'report configurablereports', "report/configurablereports/index.php?id=$course->id", $course->id);

//setting page title and page heading
$PAGE->set_title($course->shortname .': '. get_string('pluginname', 'block_configurable_reports'));
$PAGE->set_heading($course->fullname);

//Displaying header and heading
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($course->fullname));

// Display list of reports that are available to the user
// (based on permissions defined on the configurable reports block, in general and per report)
if (!empty($items)) {
    foreach($items as $report) {
        echo "$report<br/>";
    }
} else {
    echo get_string('noreportsavailable','block_configurable_reports');
}

//display page footer
echo $OUTPUT->footer();
