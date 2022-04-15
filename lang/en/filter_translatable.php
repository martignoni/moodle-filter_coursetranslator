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
 * Full translate
 *
 * @package    filter_translatable
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// filter name strings
$string['pluginname'] = 'Translatable Content';
$string['filtername'] = 'Translatable Content';
$string['translate_page_title'] = 'Translate Content';
$string['translatable:edittranslations'] = 'Edit Translations';

// deepl strings
$string['apikey'] = 'API Key for DeepL Translate';
$string['apikey_desc'] = 'You need to get an API key from DeepL to use the translate api';
$string['usedeepl'] = 'Use DeepL';
$string['usedeepl_desc'] = 'Check this checkbox if you want the plugin to use the DeepL translate api, otherwise auto generated translations are same as original';

// token strings
$string['wstoken'] = 'Web Service Token';
$string['wstoken_desc'] = 'Generate a Web Service token and enter it here.';
$string['wstoken_missing'] = 'Web Service Token is Missing';
$string['wstoken_instruction'] = 'An administrator needs to setup the <a href="{$a}">web service token.</a>';