<?php
namespace filter_translatable\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    public function render_translate_page($page) {
        $data = $page->export_for_template($this);
        return parent::render_from_template('filter_translatable/translate_page', $data);
    }
}