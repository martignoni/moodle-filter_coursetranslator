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
 * Filter Multilingual
 *
 * @package    filter_multilingual
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/Web_services_API
 */

defined('MOODLE_INTERNAL') || die();

// Add services definition.
$services = array(
    'Multilingual' => array(
        'functions' => array ('filter_multilingual_update_translation'),
        'requiredcapability' => 'filter/multilingual:edittranslations',
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'Multilingual',
        'downloadfiles' => 0,
        'uploadfiles'  => 0
    )
);

// Add functions for webservices.
$functions = array(
    'filter_multilingual_update_translation' => array(
        'classname'     => 'filter_multilingual_external',
        'methodname'    => 'update_translation',
        'classpath'     => 'filter/multilingual/externallib.php',
        'description'   => 'Update Translation',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'filter/multilingual:edittranslations',
    ),
);
