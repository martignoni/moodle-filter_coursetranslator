<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/externallib.php");

/**
 * Filter Multilingual Web Service
 *
 * Adds a webservice available via ajax for the Translate Content page.
 *
 * @package    filter_multilingual
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/External_functions_API
 */
class mulitlingual_webservice extends external_api {

    /**
     * Update Translation Parameters
     *
     * Adds validaiton parameters for translations
     *
     * @return external_function_parameters
     */
    public static function update_translation_parameters() {
        return new external_function_parameters(
            array(
                'translation' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'            => new external_value(PARAM_INT, 'id of translation'),
                            'course_id'     => new external_value(PARAM_INT, 'id of course'),
                            'translation'   => new external_value(PARAM_RAW, 'translated text'),
                        )
                    )
                )
            )
        );
    }

    /**
     * Update Translation
     *
     * Add translation to the database
     *
     * @param object $translation
     * @return array
     */
    public static function update_translation($translation) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::update_translation_parameters(), array('translation' => $translation));

        $transaction = $DB->start_delegated_transaction();

        $translations = array();

        foreach ($params['translation'] as $translation) {
            $translation = (object)$translation;

            // Check for null values and throw errors @todo.

            // Security checks.
            $context = context_course::instance($translation->course_id);
            self::validate_context($context);
            require_capability('filter/multilingual:edittranslations', $context);

            // Update the record.
            $translation->id = $DB->update_record('filter_multilingual', $translation);
            $translations[] = (array)$translation;
        }

        // Commit the transaction.
        $transaction->allow_commit();

        return $translations;
    }

    /**
     * Return Translation
     *
     * Returns updated translation to the user from web service.
     *
     * @return external_multiple_structure
     */
    public static function update_translation_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'            => new external_value(PARAM_INT, 'id of translation'),
                    'course_id'     => new external_value(PARAM_INT, 'id of course'),
                    'translation'   => new external_value(PARAM_RAW, 'translated text'),
                )
            )
        );
    }

}
