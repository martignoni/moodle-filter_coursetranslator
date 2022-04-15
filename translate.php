<?php

require_once('../../config.php');
require_once './classes/output/translate_page.php';
$course_id = required_param('course_id', PARAM_INT);
$lang = optional_param('course_lang', 'en', PARAM_NOTAGS);
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$translatables = $DB->get_records('filter_translatable', array('course_id' => $course_id, 'lang' => $lang));

// setup page
$context = context_course::instance($course_id);
$PAGE->set_context($context);
require_login();
require_capability('filter/translatable:edittranslations', $context);

// get webservice token
$wstoken = get_config('filter_translatable', 'wstoken');

// set initial page layout
$title = get_string('translate_page_title', 'filter_translatable');
$PAGE->set_url('/filter/translatable/translate.php', array('course_id' => $course_id));
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('standard');
$PAGE->requires->css('/filter/translatable/style.css');
$PAGE->requires->js('/filter/translatable/javascript/jquery-3.6.0.min.js');
$PAGE->requires->js('/filter/translatable/javascript/translatable.js');

// get the renderer
$output = $PAGE->get_renderer('filter_translatable');

// header
echo $output->header();

// webservice token is set
if ($wstoken) {
    // course name heading
    echo $output->heading($course->fullname);

    // content
    $renderable = new \filter_translatable\output\translate_page($translatables, $course);
    echo $output->render($renderable, $course);
}
// need to set webservice
else {
    // need to set webservice token
    $wstoken_url = new moodle_url("/admin/settings.php?section=filtersettingtranslatable");
    var_dump($jswstoken);
    echo $output->heading(get_string('wstoken_missing', 'filter_translatable'));
    echo '<p>' . format_text(get_string('wstoken_instruction', 'filter_translatable', $wstoken_url->__toString())) . '</p>';
}

// footer
echo $output->footer();