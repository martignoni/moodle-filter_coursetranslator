<?php

namespace filter_translatable\output;

use renderable;
use renderer_base;
use templatable;
use stdClass;

class translate_page implements renderable, templatable {

    public function __construct($translatables, $course) {
        $this->translatables = $translatables;
        $this->course = $course;
        $this->langs = get_string_manager()->get_list_of_translations();
        $this->lang = $this->langs[current_language()];
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        // process translatable content
        $translatable_content = [];
        $wordcount = 0;
        foreach($this->translatables as $item) {
            // build the wordcount
            $wordcount = $wordcount + str_word_count($item->sourcetext);
            // add the item to translatables
            array_push($translatable_content, $item);
        }

        $langs = [];
        // process langs
        foreach($this->langs as $key => $lang) {
            array_push($langs, array(
                'code' => $key,
                'lang' => $lang,
                'selected' => current_language() === $key ? "selected": ""
            ));
        }

        $data->translatables = $translatable_content;
        $data->course = $this->course;
        $data->langs = $langs;
        $data->lang = $this->lang;
        $data->wordcount = $wordcount;

        return $data;
    }
}