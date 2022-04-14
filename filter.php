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
 * Full translate
 *
 * @package    filter
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Translatable Filter for Moodle
 */
class filter_translatable extends moodle_text_filter {

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

        // no need to translate empty or numeric text
        if (empty($text) or is_numeric($text)) {
            return $text;
        }

        // get current language
        $language = current_language();
        if ($CFG->lang == $language) {
            return $text;
        }

        // get the text format
        $format = 0;
        if (isset($options['originalformat'])) {
            if ($options['originalformat'] == FORMAT_HTML) {
                $format = FORMAT_HTML;
            } else if ($options['originalformat'] == FORMAT_PLAIN){
                $format = 0;
            }
        }

        // return the translation
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
        $hashkey = sha1(trim($text));
        $records = $DB->get_records(self::TABLENAME, ['hashkey' => $hashkey, 'lang' => $language], 'id ASC', 'translation', 0, 1);
        if (isset(reset($records)->translation)) {
            $translatedtext = reset($records)->translation;
        }

        // get translation if it exists
        if (isset($translatedtext)) {
            $DB->set_field(self::TABLENAME, 'lastaccess', time(), ['hashkey' => $hashkey, 'lang' => $language]);
        }
        // generate translation
        else {
            $translatedtext = $this->generate_translation_update_database($text, $language, $hashkey, $format);
        }

        // check for permission to translate
        if (has_capability('filter/translatable:edittranslations', $this->context)) {

            $records = $DB->get_records(self::TABLENAME, ['hashkey' => $hashkey, 'lang' => $language], 'id ASC', 'id', 0, 1);
            $id = reset($records)->id;

            if (!isset($SESSION->filter_translatable)) {
                $SESSION->filter_translatable = new stdClass();
                $SESSION->filter_translatable->strings = [];
            } else {
                $SESSION->filter_translatable->strings[$id] = $translatedtext;
            }

            // edit link
            $translatedtext .= '<a target="_blank" data-action="translation-edit" data-recordid="'.$id.'" href="'.$CFG->wwwroot.'/admin/tool/translationmanager/edit.php?id='.$id.'">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
        }

        // return the edit link
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
        $course_id = $COURSE->id;
        $translation = $this->generate_translation($text, $language);

        if ($translation) {
           $hidefromtable = 0;
        } else {
            $translation = $text;
            $hidefromtable = 1;
        }

        // build the translation record
        $record = (object) [
            'course_id' => isset($course_id) ? $course_id : 0, // set to 0 if course_id not found to avoid null errors
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

        // insert the record into the database
        $DB->insert_record(self::TABLENAME, $record);

        // return the translation
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

        // return existing text if machine translation disabled
        if (get_config('filter_translatable', 'usedeepl') ==  0) {
            return $text;
        }

        // get the language
        $language = str_replace('_wp', '', $language);
        require_once($CFG->libdir. "/filelib.php");

        // build new curl request
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

        // get the translation
        if (!empty($resp->data->translations[0]->text)
                && $resp->data->translations[0]->detected_source_language=== $language) {
            return $resp->data->translations[0]->text;
        }
        // fallback if translation fails
        else {
            return $text;
        }
    }
}