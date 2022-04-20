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
 * Course Translator Filter
 *
 * @package    filter
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  based on work by 2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Filters
 */
class filter_coursetranslator extends moodle_text_filter {

    // Database table name.
    const TABLENAME = 'filter_coursetranslator';


    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);
        $this->localconfig = $localconfig;

        switch($context->contextlevel) {
            case CONTEXT_SYSTEM:
                $this->filterctx = false;
                break;
            case CONTEXT_USER:
                $this->filterctx = false;
                break;
            case CONTEXT_COURSECAT:
                $this->filterctx = false;
                break;
            case CONTEXT_COURSE:
                $this->filterctx = $context;
                break;
            case CONTEXT_MODULE:
                $this->filterctx = $context;
                break;
            default:
                $this->filterctx = false;
        }
    }

    /**
     * Filter
     *
     * @param string $text
     * @param array $options
     * @return void
     */
    public function filter($text, array $options = []) {
        global $CFG;

        // set options
        $this->options = $options;

        // No need to translate empty or numeric text.
        if (empty($text) || is_numeric($text) || $this->filterctx === false) {
            return $text;
        }

        // Get current language.
        $language = current_language();
        // if ($CFG->lang === $language) {
        //     return $text;
        // }

        // Get the text format. Set Plain Format to 0.
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
        global $DB, $CFG, $PAGE, $COURSE, $SESSION;

        // Get modinfo to avoid database queries
        $modinfo = get_fast_modinfo($COURSE);

        // Added translate url
        foreach($modinfo->instances as $instances) {
            foreach($instances as $instance) {
                $translateurl = new moodle_url('/filter/coursetranslator/translate.php', array(
                    'course_id' => $COURSE->id,
                    'course_lang' => current_language()
                ));
                $instance->set_after_edit_icons('<a href="' . $translateurl . '"><i class="fa fa-globe" aria-hidden="true"></i></a>');
            }
        };

        // Parse course for uid building
        switch($text) {
            // Course related uids
            case $COURSE->fullname:
                $uid = 'course/fullname/';
                break;
            case $COURSE->shortname:
                $uid = 'course/shortname/';
                break;
            case $COURSE->summary:
                $uid = 'course/summary/';
                break;
            default:
                $uid = null;
                break;
        }

        // Parse sections for uid building
        $sections = $modinfo->get_section_info_all();
        if (isset($sections)) {
            foreach ($sections as $section) {
                if (isset($section->name) && $section->name === $text) {
                    $uid = $section->id . '/name/';
                }
                if (isset($section->summary) && $section->summary === $text) {
                    $uid = $section->id . '/summary/';
                }
            }
        }

        // Parse activities for uid building
        $activities = $modinfo->get_array_of_activities($COURSE);
        foreach ($activities as $activity) {
            if (isset($activity->name) && $activity->name === $text) {
                $uid = $activity->mod . '/name/';
            }
            if (isset($activity->content) && $activity->content === $text) {
                $uid = $activity->mod . '/content/';
            }
            $activityid = optional_param('id', 0, PARAM_INT);
            if (!isset($activity->content) && intval($activityid) === intval($activity->cm)) {

                $fields = "*";
                $record = $DB->get_record($activity->mod, array('id' => $activity->id), $fields);

                if (isset($record->intro) && $text === $record->intro) {
                    $uid = $activity->mod . '/intro/';
                }
                if (isset($record->content) && $text === $record->content) {
                    $uid = $activity->mod . '/content/';
                }
            }
        }

        // uid check
        if (!$uid) {
            return $text;
        }

        // Generate hashkey.
        $hashstring = $this->context->path . '/' . $COURSE->id . '/' . $uid;
        $hashkey = sha1(trim($hashstring));

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
            'textformat' => boolval($format) ? 'html' : 'plain',
            'timecreated' => time(),
            'lang' => $language,
            'sourcelang' => $CFG->lang,
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
        if (boolval(get_config('filter_coursetranslator', 'usedeepl')) === false) {
            return $text;
        }

        // Autotranslate not enabled.
        if (boolval(get_config('filter_coursetranslator', 'ondemand_autotranslate')) === false) {
            return $text;
        }

        // Supported languages.
        $supportedlangsstring = get_string('supported_languages', 'filter_coursetranslator');
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
            'source_lang' => $CFG->lang,
            'target_lang' => $language,
            'preserve_formatting' => 1,
            'auth_key' => get_config('filter_coursetranslator', 'apikey'),
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
