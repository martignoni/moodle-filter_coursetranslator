<?php

/**
 * Add Translate Link to Edit Settings
 *
 * @param object $navigation
 * @param object $course
 * @return void
 */
function filter_translatable_extend_navigation_course($navigation, $course) {

    // get current language
    $lang = current_language();

    // build a moodle url
    $url = new moodle_url("/filter/translatable/translate.php?course_id=$course->id&course_lang=$lang");

    // get title of translate page for navigation menu
    $title = get_string('translate_page_title', 'filter_translatable');

    // navigation node
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

