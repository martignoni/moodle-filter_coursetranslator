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

use moodleform;

/**
 * Translate Form Output
 *
 * Provides output class for /filter/coursetranslator/translate.php
 *
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class translate_form extends moodleform {

    /**
     * Moodle Form Definition
     *
     * Our Mustache Renderer recieves this as $mform->render() for output.
     *
     * @return void
     */
    public function definition() {

        // Initialize moodleform.
        $mform = $this->_form;
        $mform->disable_form_change_checker();

        // Open Form.
        $mform->addElement('html', '<div class="container-fluid filter-coursetranslator__form">');

        if (isset($this->_customdata['coursetranslators'])) {

            foreach ($this->_customdata['coursetranslators'] as $item) {
                // Open translation item.
                $mform->addElement('html', '<div class="row align-items-start border-bottom py-3">');

                // Checkbox.
                $mform->addElement('html', '<div class="col-1">');
                $mform->addElement('html', '<div class="form-check">');
                $mform->addElement('html', '<input
                    class="form-check-input filter-coursetranslator__checkbox"
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
                    class="col-5 px-0 pr-5 filter-coursetranslator__source-text"
                    data-id="' . $item->id . '"
                >');
                if ($item->textformat === 'plain') {
                    $mform->addElement('html', '<div>' . $item->sourcetext . '</div>');
                } else {
                    $mform->addElement('html', '<div class="filter-coursetranslator__scroll">' . $item->sourcetext . '</div>');
                }
                $mform->addElement('html', '</div>');

                // Translation Input.
                $mform->addElement('html', '<div
                    class="col-5 px-0 filter-coursetranslator__translation filter-coursetranslator__editor"
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

        // Close form.
        $mform->addElement('html', '</div>');
    }

    /**
     * Process Data
     *
     * @param \stdClass $data
     * @return void
     */
    public function process(\stdClass $data) {

    }

    /**
     * Setup Access Capabilities
     *
     * @return void
     */
    public function require_access() {
        require_capability('filter/coursetranslator:edittranslations', \context_system::instance()->id);
    }
}
