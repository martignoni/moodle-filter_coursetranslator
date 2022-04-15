<?php

/**
 * Add Translate Link to Edit Settings
 *
 * @param object $navigation
 * @param object $course
 * @return void
 */
function filter_translatable_extend_navigation_course($navigation, $course) {
    $url = new moodle_url("/filter/translatable/translate.php?course_id=$course->id");
    $title = get_string('translate_page_title', 'filter_translatable');
    $translatecontent = navigation_node::create(
        $title,
        $url,
        navigation_node::TYPE_CUSTOM,
        $title,
        'translate',
        new pix_icon('translate', 'translate', 'filter_translatable')
    );
    $navigation->add_node($translatecontent);
}

