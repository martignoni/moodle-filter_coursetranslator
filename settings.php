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

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    // use deepl machine translation
    $settings->add(new admin_setting_configcheckbox('filter_translatable/usedeepl', get_string('usedeepl', 'filter_translatable'),
        get_string('usedeepl_desc', 'filter_translatable'), false));

    // deepl apikey
    $settings->add(new admin_setting_configtext('filter_translatable/apikey', get_string('apikey', 'filter_translatable'),
    get_string('apikey_desc', 'filter_translatable'), null, PARAM_RAW_TRIMMED, 40));

    // use ondemand autotranslation
    $settings->add(new admin_setting_configcheckbox('filter_translatable/ondemand_autotranslate', get_string('ondemand_autotranslate', 'filter_translatable'),
        get_string('ondemand_autotranslate_desc', 'filter_translatable'), false));

    // use translation page autotranslation
    $settings->add(new admin_setting_configcheckbox('filter_translatable/useautotranslate', get_string('useautotranslate', 'filter_translatable'),
        get_string('useautotranslate_desc', 'filter_translatable'), false));

}
