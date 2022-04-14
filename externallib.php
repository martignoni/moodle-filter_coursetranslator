<?php

require_once("$CFG->libdir/externallib.php");

// https://m3.moodle.local/webservice/rest/server.php?wstoken=54b9edb6361ccc688b41d53038e61019&wsfunction=filter_translatable_update_translation&translation[0][id]=538&translation[0][course_id]=3&translation[0][translation]=%D0%9A%D1%83%D1%80%D1%81:%20Orientation%20to%20Graduate%20Studies
class filter_translatable_external extends external_api {

    public static function update_translation_parameters() {
        return new external_function_parameters(
            array(
                'translation' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'            => new external_value(PARAM_INT, 'id of translation'),
                            'course_id'     => new external_value(PARAM_INT, 'id of course'),
                            'translation'   => new external_value(PARAM_TEXT, 'translated text'),
                        )
                    )
                )
            )
        );
    }

    public static function update_translation($translation) {
        global $CFG, $DB;
        // require_once("$CFG->dirroot/group/lib.php");

        $params = self::validate_parameters(self::update_translation_parameters(), array('translation' => $translation));

        $transaction = $DB->start_delegated_transaction();

        $translations = array();

        foreach ($params['translation'] as $translation) {
            $translation = (object)$translation;

            // security checks
            // $context = get_context_instance(CONTEXT_COURSE, $translation->course_id);
            // self::validate_context($context);
            // require_capability('filter/translatable:edittranslations', $context);

            $translation->id = $DB->update_record('filter_translatable', $translation);
            $translations[] = (array)$translation;

        }

        // foreach ($params['groups'] as $group) {
        //     $group = (object)$group;

        //     if (trim($group->name) == '') {
        //         throw new invalid_parameter_exception('Invalid group name');
        //     }
        //     if ($DB->get_record('groups', array('courseid'=>$group->courseid, 'name'=>$group->name))) {
        //         throw new invalid_parameter_exception('Group with the same name already exists in the course');
        //     }

        //     // now security checks
        //     $context = get_context_instance(CONTEXT_COURSE, $group->courseid);
        //     self::validate_context($context);
        //     require_capability('filter/translatable:edittranslations', $context);

        //     // finally create the group
        //     $group->id = groups_create_group($group, false);
        //     $groups[] = (array)$group;
        // }

        $transaction->allow_commit();

        return $translations;
    }

    public static function update_translation_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'            => new external_value(PARAM_INT, 'id of translation'),
                    'course_id'     => new external_value(PARAM_INT, 'id of course'),
                    'translation'   => new external_value(PARAM_TEXT, 'translated text'),
                )
            )
        );
    }

}
