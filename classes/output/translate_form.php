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

namespace filter_multilingual\output;

use moodleform;

/**
 * Translate Form Output
 *
 * Provides output class for /filter/multilingual/translate.php
 *
 * @package    filter_multilingual
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker();

        if (isset($this->_customdata['multilinguals'])) {

            foreach ($this->_customdata['multilinguals'] as $item) {
                // Open translation item.
                $mform->addElement('html', '<div class="row align-items-start border-bottom py-3">');

                // Checkbox.
                $mform->addElement('html', '<div class="col-1">');
                $mform->addElement('html', '<div class="form-check">');
                $mform->addElement('html', '<input
                    class="form-check-input filter-multilingual_select"
                    type="checkbox"
                    data-id="' . $item->id . '"
                    disabled
                />');
                $mform->addElement('html', '<label class="form-check-label d-none">' . $item->id . '</label>');
                $mform->addElement('html', '</div>');
                $mform->addElement('html', '</div>');

                // ID.
                $mform->addElement('html', '<div class="col-1">' . $item->id . '</div>');

                // Source Text.
                $mform->addElement('html', '<div
                    class="col-5 px-0 pr-5 filter-multilingual__source-text"
                    data-id="' . $item->id . '"
                >');
                if ($item->textformat === 'plain') {
                    $mform->addElement('html', '<div>' . $item->sourcetext . '</div>');
                } else {
                    $mform->addElement('html', '<div class="filter-multilingual__scroll">' . $item->sourcetext . '</div>');
                }
                $mform->addElement('html', '</div>');

                // Translation Input.
                $mform->addElement('html', '<div
                    class="col-5 px-0 filter-multilingual__translation multilingual-editor"
                    data-id="' . $item->id . '"
                >');

                // Plain text input.
                if ($item->textformat === 'plain') {
                    $mform->addElement('html', '<div
                        class="format-' . $item->textformat . ' border py-2 px-3"
                        contenteditable="true"
                        data-format="' . $item->textformat . '"
                    >' . $item->translation . '</div>');
                }
                // HTML input.
                if ($item->textformat === 'html') {
                    $mform->addElement('editor', 'id_' . $item->id, $item->id);
                    $mform->setType('id_' . $item->id, PARAM_RAW);
                    $mform->setDefault('id_' . $item->id, array('text' => trim($item->translation)));
                }

                $mform->addElement('html', '</div>');

                // Close translation item.
                $mform->addElement('html', '</div>');
            }
        }
    }

    public function process(\stdClass $data) {

    }

    public function require_access() {
        require_capability('filter/multilingual:edittranslations', \context_system::instance()->id);
    }
}
