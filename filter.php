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
class filter_coursetranslator extends moodle_text_filter
{

    /**
     * Course Translator Construct
     *
     * @param context $context
     * @param array $localconfig
     */
    public function __construct($context, array $localconfig) {
        global $COURSE;
        parent::__construct($context, $localconfig);
        $this->localconfig = $localconfig;
        $this->course = $COURSE;
        $this->tablename = 'filter_coursetranslator';

        switch ($context->contextlevel) {
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

        // Set options.
        $this->options = $options;

        // No need to translate empty or numeric text.
        if (
            empty($text)
            || is_numeric($text)
            || $this->filterctx === false
            || $text === 'Moodle'
        ) {
            return $text;
        }

        // Get current language.
        $language = current_language();
        if ($CFG->lang === $language) {
            return $text;
        }

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
     * Get Translation Edit Link
     *
     * @param object $instance
     * @return void
     */
    private function get_translate_link($instance) {
        $translateurl = new moodle_url('/filter/coursetranslator/translate.php', array(
            'course_id' => $this->course->id,
            'mod_id' => $instance->id,
            'course_lang' => current_language(),
        ));
        $instance->set_after_edit_icons(
            '<a href="' . $translateurl . '" target="_blank"><i class="fa fa-globe" aria-hidden="true"></i></a>
        '
        );
    }

    /**
     * Get $modconfig
     *
     * @param int $id
     * @param string $name
     * @return array
     */
    private function get_modconfig($id, $name) {
        return array(
            'mod_id' => $id,
            'mod_name' => $name
        );
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
        global $DB, $CFG, $PAGE, $SESSION;

        // Get modinfo to avoid database queries.
        $modinfo = get_fast_modinfo($this->course);
        $modconfig = null;

        $coursecheck = $this->get_coursecheck($text);
        if ($coursecheck) {
            $modconfig = $coursecheck;
        }

        $sectionscheck = $this->get_sectionscheck($text, $modinfo);
        if ($sectionscheck) {
            $modconfig = $sectionscheck;
        }

        $activitiescheck = $this->get_activitiescheck($text, $modinfo);
        if ($activitiescheck) {
            $modconfig = $activitiescheck;
        }

        // Could not build modconfig, return original text.
        if (!$modconfig) {
            return $text;
        }
        ksort($modconfig);
        $hashstring = implode('/', $modconfig);
        $hashkey = sha1(trim($text));

        // Get translation from database.
        $records = $DB->get_records($this->tablename, ['hashkey' => $hashkey, 'lang' => $language], 'id ASC', 'translation', 0, 1);
        if (isset(reset($records)->translation)) {
            $translatedtext = reset($records)->translation;
        }

        // Get translation if it exists else generate translation.
        if (isset($translatedtext)) {
            $DB->set_field($this->tablename, 'lastaccess', time(), ['hashkey' => $hashkey, 'lang' => $language]);
        } else {
            $translatedtext = $this->generate_translation_update_database($text, $language, $hashkey, $format);
        }

        // Return the translated text for display.
        return $translatedtext;

        // $this->get_translate_link($instance);
    }

    /**
     * Course Check
     *
     * @param string $text
     * @return array|false
     */
    public function get_coursecheck($text) {
        if ($this->context->contextlevel === CONTEXT_COURSE) {
            $modconfig = array(
                'mod_id' => $this->course->id,
                'mod_name' => 'course',
                'ctx_instance_id' => $this->context->id,
                'ctx_path' => $this->context->path,
            );
            switch ($text) {
                case $this->course->fullname:
                    $modconfig['mod_col'] = 'fullname';
                    break;

                case $this->course->shortname:
                    $modconfig['mod_col'] = 'shortname';
                    break;

                case $this->course->summary:
                    $modconfig['mod_col'] = 'summary';
                    break;

                default:
                    unset($modconfig);
                    break;
            }
            // Return modconfig.
            if ($modconfig) {
                return $modconfig;
            } else {
                return false;
            }
        } else {
            // Nothing found.
            return false;
        }
    }

    /**
     * Sections Check
     *
     * @param string $text
     * @param object $modinfo
     * @return array|false
     */
    public function get_sectionscheck($text, $modinfo) {

        if ($this->context->contextlevel === CONTEXT_COURSE) {
            // Parse through sections.
            $sections = $modinfo->get_section_info_all();
            foreach ($sections as $section) {
                if ($text === $section->summary) {
                    $modconfig = array(
                        'mod_id' => $section->id,
                        'ctx_instance_id' => $this->context->id,
                        'ctx_path' => $this->context->path,
                        'mod_name' => 'section',
                        'mod_col' => 'summary',
                    );
                    return $modconfig;
                }
                if ($text === $section->name) {
                    $modconfig = array(
                        'mod_id' => $section->id,
                        'ctx_instance_id' => $this->context->id,
                        'ctx_path' => $this->context->path,
                        'mod_name' => 'section',
                        'mod_col' => 'name',
                    );
                    return $modconfig;
                }
            }
        } else {
            // Nothing found.
            return false;
        }
    }

    /**
     * Activities Check
     *
     * @param string $text
     * @param object $modinfo
     * @return array|false
     */
    public function get_activitiescheck($text, $modinfo) {
        global $DB;

        if ($this->context->contextlevel === CONTEXT_MODULE) {
            // Parse through modules.
            $activities = $modinfo->get_array_of_activities($this->course);
            foreach ($activities as $activity) {
                if ($activity->cm === $this->context->instanceid) {
                    $record = $DB->get_record($activity->mod, array('id' => $activity->id));
                    $data = array_merge((array) $record, (array) $activity);
                    $modconfig = array(
                        'mod_id' => $activity->id,
                        'ctx_instance_id' => $this->context->id,
                        'ctx_path' => $this->context->path,
                        'mod_name' => $activity->mod,
                    );
                    $recordcol = array_search($text, (array) $record);
                    $activitycol = array_search($text, (array) $activity);
                    if ($activitycol) {
                        $modconfig['mod_col'] = $activitycol;
                    } else if ($recordcol) {
                        $modconfig['mod_col'] = $recordcol;
                    } else {
                        unset($modconfig);
                    }
                    return $modconfig;
                }
            }
        } else {
            // Nothing found.
            return false;
        }
    }

    /**
     * Update Translation Database
     *
     * @param string $text
     * @param string $language
     * @param string $hashkey
     * @param int $format
     * @param array $modconfig
     * @return void
     */
    public function generate_translation_update_database($text, $language, $hashkey, $format) {
        global $DB, $PAGE, $CFG;

        // Processing vars.
        $this->courseid = $this->course->id;
        $translation = $this->generate_translation($text, $language);

        // Build the translation record.
        $record = (object) [
            'course_id' => isset($this->courseid) ? $this->courseid : 0, // Set to 0 if course_id not found to avoid null errors.
            'hashkey' => $hashkey,
            'sourcetext' => $text,
            'textformat' => boolval($format) ? 'html' : 'plain',
            'lang' => $language,
            'sourcelang' => $CFG->lang,
            'url' => str_replace($CFG->wwwroot, '', $PAGE->url->out()),
            'automatic' => true,
            'translation' => $translation,
            'timecreated' => time(),
        ];

        // Insert the record into the database.
        // $DB->insert_record($this->tablename, $record);

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
