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
 * Moodle Edit Translations Permissions
 *
 * Adds filter/translatable:eddittranslations permissions for checking against
 * the webservice.
 *
 * @package    filter_translatable
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  based on work by 2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Filter Capabilities.
$capabilities = [
    'filter/translatable:edittranslations' => [
        'captype' => 'write',
        'riskbitmaskt' => 'RISK_CONFIG',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ]
];
