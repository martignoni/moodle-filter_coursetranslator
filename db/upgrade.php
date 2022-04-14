<?php

function xmldb_filter_translatable_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2020042801) {

        // Define table filter_translatable to be created.
        $table = new xmldb_table('filter_translatable');

        // Adding fields to table filter_translatable.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course_id', XMLDB_TYPE_BIGINT, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('hashkey', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sourcetext', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('textformat', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('lastaccess', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
        $table->add_field('lang', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('automatic', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('translation', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('hidefromtable', XMLDB_TYPE_INTEGER, '1', null, null, null, '0');

        // Adding keys to table filter_translatable.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table filter_translatable.
        $table->add_index('hashkeyindex', XMLDB_INDEX_NOTUNIQUE, ['hashkey']);

        // Conditionally launch create table for filter_translatable.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // translatable savepoint reached.
        upgrade_plugin_savepoint(true, 2020042801, 'filter', 'translatable');
    }

    if ($oldversion < 2022041300) {
        // translatable savepoint reached.
        upgrade_plugin_savepoint(true, 2022041300, 'filter', 'translatable');
    }
}
