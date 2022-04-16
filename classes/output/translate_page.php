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

namespace filter_translatable\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

/**
 * Translate Page Output
 *
 * Provides output class for /filter/translatable/translate.php
 *
 * @package    filter_translatable
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_page implements renderable, templatable {

    /**
     * Constructor
     *
     * @param object $translatables Translatable items
     * @param object $course Moodle course record
     */
    public function __construct($translatables, $course) {
        $this->translatables = $translatables;
        $this->course = $course;
        $this->langs = get_string_manager()->get_list_of_translations();
        $this->current_lang = optional_param('course_lang', 'en', PARAM_NOTAGS);
    }

    /**
     * Export Data to Template
     *
     * @param renderer_base $output
     * @return object
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        // Process translatable content.
        $translatablecontent = [];
        $wordcount = 0;
        foreach ($this->translatables as $item) {
            // Build the wordcount.
            $wordcount = $wordcount + str_word_count($item->sourcetext);
            // Add the item to translatables.
            array_push($translatablecontent, $item);
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
        $data->translatables = $translatablecontent;
        $data->course = $this->course;
        $data->langs = $langs;
        $data->lang = $this->langs[$this->current_lang];
        $data->wordcount = $wordcount;

        return $data;
    }
}
