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
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->test = 'test';

        // process translatable content
        $translatable_content = [];
        foreach($this->translatables as $item) {
            array_push($translatable_content, $item);
        }

        $langs = [];
        // process langs
        foreach($this->langs as $key => $lang) {
            array_push($langs, array(
                'code' => $key,
                'lang' => $lang
            ));
        }

        $data->translatables = $translatable_content;
        $data->course = $this->course;
        $data->langs = $langs;

        return $data;
    }
}