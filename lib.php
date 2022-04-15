<?php

/**
 * Add Translate Link to Edit Settings
 *
 * @param object $navigation
 * @param object $course
 * @param object $coursecontext
 * @return void
 */
function filter_translatable_extend_navigation_course($navigation, $course, $coursecontext) {
    global $COURSE;

    $url = new moodle_url("/filter/translatable/translate.php?course_id=$COURSE->id");
    $translatecontent = navigation_node::create('Translate Content', $url, navigation_node::TYPE_CUSTOM, 'Translate Content', 'devcourse');
    $navigation->add_node($translatecontent);
}

