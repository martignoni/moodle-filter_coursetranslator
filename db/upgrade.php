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
 * DB Migrations
 *
 * Creates filter_coursetranslator table for storing and accessing translations.
 * Provides migration paths for plugin upgrades.
 *
 * @package    filter_coursetranslator
 * @copyright  2022 Kaleb Heitzman <kaleb@jamfire.io>
 * @copyright  based on work by 2020 Farhan Karmali <farhan6318@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param      string $oldversion
 * @see        https://docs.moodle.org/dev/Upgrade_API
 */
function xmldb_filter_coursetranslator_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Initial Release.
    if ($oldversion < 2022042000) {
        // Define table filter_coursetranslator to be created.
        $table = new xmldb_table('filter_coursetranslator');

        // Adding fields to table filter_coursetranslator.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_BIGINT, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hashkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sourcetext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('textformat', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sourcelang', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('automatic', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('translation', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('lastaccess', XMLDB_TYPE_INTEGER, '11', null, null, null, null);

        // Adding keys to table filter_coursetranslator.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table filter_coursetranslator.
        $table->add_index('hashkeyindex', XMLDB_INDEX_NOTUNIQUE, ['hashkey']);

        // Conditionally launch create table for filter_coursetranslator.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Course Translator savepoint reached.
        upgrade_plugin_savepoint(true, 2022042000, 'filter', 'coursetranslator');
    }

    // Event observers added.
    if ($oldversion < 2022042001) {
        // Course Translator savepoint reached.
        upgrade_plugin_savepoint(true, 2022042001, 'filter', 'coursetranslator');
    }

}
