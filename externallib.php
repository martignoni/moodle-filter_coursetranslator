<?php

require_once("$CFG->libdir/externallib.php");

class filter_translatable_external extends external_api {

    public static function create_translation_parameters() {
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

        $params = self::validate_parameters(self::create_translation_parameters(), array('translation' => $translation));

        // $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

        $translation = array();

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
        //     require_capability('moodle/course:managegroups', $context);

        //     // finally create the group
        //     $group->id = groups_create_group($group, false);
        //     $groups[] = (array)$group;
        // }

        // $transaction->allow_commit();

        return $translation;
    }

}