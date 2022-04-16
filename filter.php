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

/**
 * Translatable Filter
 *
 * @package    filter
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  based on work by 2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Filters
 */
class filter_translatable extends moodle_text_filter {

    // Database table name.
    const TABLENAME = 'filter_translatable';

    /**
     * Filter
     *
     * @param string $text
     * @param array $options
     * @return void
     */
    public function filter($text, array $options = []) {
        global $CFG;

        // No need to translate empty or numeric text.
        if (empty($text) or is_numeric($text)) {
            return $text;
        }

        // Get current language.
        $language = current_language();
        if ($CFG->lang === $language) {
            return $text;
        }

        // Get the text format.
        $format = 0;
        if (isset($options['originalformat'])) {
            if ($options['originalformat'] === FORMAT_HTML) {
                $format = FORMAT_HTML;
            } else if ($options['originalformat'] === FORMAT_PLAIN) {
                $format = 0;
            }
        }

        // Return the translation.
        return $this->get_translation($text, $language, $format);
    }

    /**
     * Get Translation
     *
     * @param string $text
     * @param string $language
     * @param int $format
     * @return void
     */
    public function get_translation($text, $language, $format) {
        global $DB, $CFG, $SESSION;

        // Generate hashkey.
        $hashkey = sha1(trim($text));

        // Get records based on hashkey.
        $records = $DB->get_records(self::TABLENAME, ['hashkey' => $hashkey, 'lang' => $language], 'id ASC', 'translation', 0, 1);
        if (isset(reset($records)->translation)) {
            $translatedtext = reset($records)->translation;
        }

        // Get translation if it exists else generate translation.
        if (isset($translatedtext)) {
            $DB->set_field(self::TABLENAME, 'lastaccess', time(), ['hashkey' => $hashkey, 'lang' => $language]);
        } else {
            $translatedtext = $this->generate_translation_update_database($text, $language, $hashkey, $format);
        }

        // Check for permission to translate.
        if (has_capability('filter/translatable:edittranslations', $this->context)) {

            // Get translations.
            $records = $DB->get_records(self::TABLENAME, ['hashkey' => $hashkey, 'lang' => $language], 'id ASC', 'id', 0, 1);
            $id = reset($records)->id;

            if (!isset($SESSION->filter_translatable)) {
                $SESSION->filter_translatable = new stdClass();
                $SESSION->filter_translatable->strings = [];
            } else {
                $SESSION->filter_translatable->strings[$id] = $translatedtext;
            }
        }

        // Return the translated text for display.
        return $translatedtext;
    }

    /**
     * Update Translation Database
     *
     * @param string $text
     * @param string $language
     * @param string $hashkey
     * @param int $format
     * @return void
     */
    public function generate_translation_update_database($text, $language, $hashkey, $format) {
        global $DB, $PAGE, $CFG, $COURSE;

        // Processing vars.
        $courseid = $COURSE->id;
        $translation = $this->generate_translation($text, $language);

        if ($translation) {
            $hidefromtable = 0;
        } else {
            $translation = $text;
            $hidefromtable = 1;
        }

        // Build the translation record.
        $record = (object) [
            'course_id' => isset($courseid) ? $courseid : 0, // Set to 0 if course_id not found to avoid null errors.
            'hashkey' => $hashkey,
            'sourcetext' => $text,
            'textformat' => $format ? 'html' : 'plain',
            'timecreated' => time(),
            'lang' => $language,
            'url' => str_replace($CFG->wwwroot, '', $PAGE->url->out()),
            'automatic' => true,
            'translation' => $translation,
            'hidefromtable' => $hidefromtable
        ];

        // Insert the record into the database.
        $DB->insert_record(self::TABLENAME, $record);

        // Return the translation.
        return $translation;
    }

    /**
     * Generate a Translation
     *
     * @param string $text
     * @param string $language
     * @return void
     */
    public function generate_translation($text, $language) {
        global $CFG;

        // Return existing text if machine translation disabled.
        if (boolval(get_config('filter_translatable', 'usedeepl')) === false) {
            return $text;
        }

        // Autotranslate not enabled.
        if (boolval(get_config('filter_translatable', 'ondemand_autotranslate')) === false) {
            return $text;
        }

        // Supported languages.
        $supportedlangsstring = get_string('supported_languages', 'filter_translatable');
        $supportedlanguages = explode(',', $supportedlangsstring);

        // Get the language.
        $language = str_replace('_wp', '', $language);
        $supported = in_array(strtolower($language), array_map('strtolower', $supportedlanguages));

        // Language unsupported.
        if (!$supported) {
            return $text;
        }

        // Build new curl request.
        require_once($CFG->libdir. "/filelib.php");
        $curl = new curl();
        $params = [
            'text' => $text,
            'source_lang' => 'en',
            'target_lang' => $language,
            'preserve_formatting' => 1,
            'auth_key' => get_config('filter_translatable', 'apikey'),
            'tag_handling' => 'xml',
            'split_sentences' => 'nonewlines'
        ];
        $resp = $curl->post('https://api.deepl.com/v2/translate?', $params);
        $resp = json_decode($resp);

        // Get the translation and return translation.
        if (!empty($resp->translations[0]->text)
                && $resp->translations[0]->detected_source_language !== $language) {
            return $resp->translations[0]->text;
        } else {
            return $text;
        }
    }
}
