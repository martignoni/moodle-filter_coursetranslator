<?php

require_once('../../config.php');
require_once './classes/output/translate_page.php';
$course_id = required_param('course_id', PARAM_INT);
$lang = optional_param('lang', 'en', PARAM_NOTAGS);
$course = $DB->get_record('course', array('id' => $course_id), '*', MUST_EXIST);
$translatables = $DB->get_records('filter_translatable', array('course_id' => $course_id, 'lang' => $lang));

// setup page
$context = context_course::instance($course_id);
$PAGE->set_context($context);
require_login();
require_capability('filter/translatable:edittranslations', $context);

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
echo $output->heading($course->fullname);

// content
$renderable = new \filter_translatable\output\translate_page($translatables, $course);
echo $output->render($renderable, $course);

// footer
echo $output->footer();