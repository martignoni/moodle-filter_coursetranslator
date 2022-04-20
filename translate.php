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
 * Filter Course Translator Translate Page
 *
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Output_API
 */

// Get libs.
require_once('../../config.php');
require_once('./classes/output/translate_page.php');

// Needed vars for processing.
$courseid = required_param('course_id', PARAM_INT);
$lang = optional_param('course_lang', 'en', PARAM_NOTAGS);
$textid = optional_param('text_id', null, PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Get coursetranslator records.
if (!is_null($textid)) {
    $coursetranslators = $DB->get_records('filter_coursetranslator', array('course_id' => $courseid, 'lang' => $lang, 'id' => $textid));
} else {
    $coursetranslators = $DB->get_records('filter_coursetranslator', array('course_id' => $courseid, 'lang' => $lang));
}

// Setup page.
$context = context_course::instance($courseid);
$PAGE->set_context($context);
require_login();
require_capability('filter/coursetranslator:edittranslations', $context);

// Get js data.
$jsconfig = new stdClass();
$jsconfig->apikey = get_config('filter_coursetranslator', 'apikey');
$jsconfig->autotranslate = boolval(get_config('filter_coursetranslator', 'useautotranslate'))
    && in_array($lang, explode(',', get_string('supported_languages', 'filter_coursetranslator')))
    ? true
    : false;
$jsconfig->lang = $lang;
$jsconfig->current_lang = current_language();
$jsconfig->course_id = $courseid;

// Set initial page layout.
$title = get_string('translate_page_title', 'filter_coursetranslator');
$PAGE->set_url('/filter/coursetranslator/translate.php', array('course_id' => $courseid));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('base');
$PAGE->set_course($course);
$PAGE->requires->css('/filter/coursetranslator/styles.css');
$PAGE->requires->js_call_amd('filter_coursetranslator/coursetranslator', 'init', array($jsconfig));

// Get the renderer.
$output = $PAGE->get_renderer('filter_coursetranslator');

// Output header.
echo $output->header();

// Course name heading.
echo $output->heading($course->fullname);

// Output translation grid.
$renderable = new \filter_coursetranslator\output\translate_page($coursetranslators, $course);
echo $output->render($renderable, $course);

// Output footer.
echo $output->footer();
