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
 * Course Translator Observer
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Events_API
 */

class filter_coursetranslator_observer {

    public static function course_updated(\core\event\course_updated $event) {
        print_object($event);
        die();
    }

    public static function course_section_updated(\core\event\course_section_updated $event) {
        print_object($event);
        die();
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;
        $data = $event->get_data();
        $context = $event->get_context();
        print_object($DB->get_record($data['other']['modulename'], array('id' => $data['other']['instanceid'])), '*', 0);
        print_object($event->get_context());
        die();
    }
}
