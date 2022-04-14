<?php

require_once("$CFG->libdir/externallib.php");

// https://m3.moodle.local/webservice/rest/server.php?wstoken=54b9edb6361ccc688b41d53038e61019&wsfunction=filter_translatable_update_translation&translation[0][id]=539&translation[0][course_id]=3&translation[0][translation]=Orientation%20to%20Graduate%20Studies

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

        $params = self::validate_parameters(self::update_translation_parameters(), array('translation' => $translation));

        $transaction = $DB->start_delegated_transaction();

        $translations = array();

        foreach ($params['translation'] as $translation) {
            $translation = (object)$translation;

            // check for null values and throw errors

            // security checks
            $context = context_course::instance($translation->course_id);
            self::validate_context($context);
            require_capability('filter/translatable:edittranslations', $context);

            // update the record
            $translation->id = $DB->update_record('filter_translatable', $translation);
            $translations[] = (array)$translation;

        }

        // commit the transaction
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
