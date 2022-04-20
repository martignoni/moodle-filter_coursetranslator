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

namespace filter_coursetranslator\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;
use filter_coursetranslator\output\translate_form;

/**
 * Translate Page Output
 *
 * Provides output class for /filter/coursetranslator/translate.php
 *
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_page implements renderable, templatable {

    /**
     * Constructor
     *
     * @param object $coursetranslators Course Translator items
     * @param object $course Moodle course record
     */
    public function __construct($coursetranslators, $course) {
        $this->coursetranslators = $coursetranslators;
        $this->course = $course;
        $this->langs = get_string_manager()->get_list_of_translations();
        $this->current_lang = optional_param('course_lang', 'en', PARAM_NOTAGS);

        // Moodle Form.
        $mform = new translate_form(null, [
            'coursetranslators' => $coursetranslators,
            'course' => $course,
            // 'lang' => $lang
        ]);

        $this->mform = $mform;

    }

    /**
     * Export Data to Template
     *
     * @param renderer_base $output
     * @return object
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        // Process coursetranslator content.
        $coursetranslatorcontent = [];
        $wordcount = 0;
        foreach ($this->coursetranslators as $item) {
            // Build the wordcount.
            $wordcount = $wordcount + str_word_count($item->sourcetext);
            // Add the item to coursetranslators.
            array_push($coursetranslatorcontent, $item);
        }

        $langs = [];
        // Process langs.
        foreach ($this->langs as $key => $lang) {
            array_push($langs, array(
                'code' => $key,
                'lang' => $lang,
                'selected' => $this->current_lang === $key ? "selected" : ""
            ));
        }

        // Data for mustache template.
        $data->coursetranslators = $coursetranslatorcontent;
        $data->course = $this->course;
        $data->langs = $langs;
        $data->lang = $this->langs[$this->current_lang];
        $data->wordcount = $wordcount;

        // Hacky fix but the only way to adjust html...
        // This could be overridden in css and I might look at that fix for the future.
        $renderedform = $this->mform->render();
        $renderedform = str_replace('col-md-3 col-form-label d-flex pb-0 pr-md-0', 'd-none', $renderedform);
        $renderedform = str_replace('class="col-md-9 form-inline align-items-start felement"', '', $renderedform);
        $data->mform = $renderedform;

        return $data;
    }
}
