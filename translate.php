<?php


// get libs
require_once('../../config.php');
require_once './classes/output/translate_page.php';

// needed vars for processing
$course_id = required_param('course_id', PARAM_INT);
$lang = optional_param('course_lang', 'en', PARAM_NOTAGS);
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$translatables = $DB->get_records('filter_translatable', array('course_id' => $course_id, 'lang' => $lang));

// setup page
$context = context_course::instance($course_id);
$PAGE->set_context($context);
require_login();
require_capability('filter/translatable:edittranslations', $context);

// get js data
$jsconfig = new stdClass();
$jsconfig->apikey = get_config('filter_translatable', 'apikey');
$jsconfig->autotranslate = boolval(get_config('filter_translatable', 'useautotranslate'))
    && in_array($lang, explode(',', get_string('supported_languages', 'filter_translatable')))
    ? true
    : false;
$jsconfig->lang = $lang;
$jsconfig->current_lang = current_language();
$jsconfig->course_id = $course_id;

// set initial page layout
$title = get_string('translate_page_title', 'filter_translatable');
$PAGE->set_url('/filter/translatable/translate.php', array('course_id' => $course_id));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('base');
$PAGE->set_course($course);
$PAGE->requires->css('/filter/translatable/styles.css');
$PAGE->requires->js_call_amd('filter_translatable/translatable', 'init', array($jsconfig));

// get the renderer
$output = $PAGE->get_renderer('filter_translatable');

// header
echo $output->header();

// course name heading
echo $output->heading($course->fullname);

// content
$renderable = new \filter_translatable\output\translate_page($translatables, $course);
echo $output->render($renderable, $course);

// footer
echo $output->footer();