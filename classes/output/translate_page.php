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
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->test = 'test';

        $translatable_content = [];
        foreach($this->translatables as $item) {
            array_push($translatable_content, $item);
        }
        $data->translatables = $translatable_content;
        $data->course = $this->course;
        return $data;
    }
}