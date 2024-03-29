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
 * Course Translator
 *
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @see        https://docs.moodle.org/dev/String_API
 */

defined('MOODLE_INTERNAL') || die();

// General strings.
$string['pluginname'] = 'Course Translator';
$string['filtername'] = 'Course Translator';
$string['coursetranslator:edittranslations'] = 'Edit Translations';
$string['edittranslation'] = 'Edit Translation';

// DeepL strings.
$string['apikey'] = 'API Key for DeepL Translate';
$string['apikey_desc'] = 'Copy your api key from DeepL to use machine translation.';
$string['usedeepl'] = 'Use DeepL';
$string['usedeepl_desc'] = 'Check this checkbox if you want the plugin to use the DeepL translate api, otherwise auto generated translations are same as original.';
$string['ondemand_autotranslate'] = 'Enable on demand autotranslate';
$string['ondemand_autotranslate_desc'] = 'Enable autotranslated content on page load. This can cause a long page load when generating autotranslations for the first time.';
$string['useautotranslate'] = 'Enable autotranslate for translation page';
$string['useautotranslate_desc'] = 'Enable autotranslate on the translation page. This gives translators the abilty to autotranslate content without enabling autotranslate on individual page loads.';
$string['supported_languages'] = 'bg,cs,da,de,el,en,es,et,fi,fr,hu,it,ja,lt,lv,nl,pl,pt,ro,ru,sk,sl,sv,zh'; // Do not change between translations.

// Template strings.
$string['t_select_target_language'] = 'Select Target Language';
$string['t_word_count'] = 'Word Count: {$a}';
$string['t_char_count'] = 'Character Count: {$a}';
$string['t_char_count_spaces'] = 'Character Count with Spaces: {$a}';
$string['t_autotranslate'] = 'Autotranslate';
$string['t_id'] = 'ID';
$string['t_source_text'] = 'Source Text';
$string['t_translation'] = 'Translation: {$a}';
$string['t_autosaved'] = 'Autosaved';
$string['t_selectall'] = 'Select All';

// Glossary strings.
$string['g_page_title'] = 'Course Translator Glossary';
