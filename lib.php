<?php

function filter_translatable_extend_navigation_course($navigation, $course, $coursecontext) {
    global $COURSE;

    $url = new moodle_url("/filter/translatable/translate.php?course_id=$COURSE->id");
    $translatecontent = navigation_node::create('Translate Content', $url, navigation_node::TYPE_CUSTOM, 'Translate Content', 'devcourse');
    $navigation->add_node($translatecontent);
}

// function filter_translatable_before_footer() {
//     global $PAGE, $SESSION, $CFG;

//     if (isset($SESSION->filter_translatable->strings)) {
//         $strings = $SESSION->filter_translatable->strings;
//     } else {
//         $strings = [];
//     }

//     if (has_capability('filter/translatable:edittranslations', \context_system::instance())) {
//         if (get_config('filter_translatable', 'showstringsinfooter') !=  0) {
//             foreach ($strings as $key => $value) {
//                 $id = $key;
//                 echo  $value .' <a target="_blank" data-action="translation-edit" data-recordid="'.$id.'" href="'.$CFG->wwwroot.'/admin/tool/translationmanager/edit.php?id='.$id.'">
//                 <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a><br/>';
//             }
//         }
//         $PAGE->requires->js_call_amd('tool_translationmanager/translationmanager', 'init');
//     }
//     unset($SESSION->filter_translatable->strings);
// }