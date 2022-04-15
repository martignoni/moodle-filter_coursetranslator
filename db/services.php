<?php

$services = array(
    'Translatable' => array(
        'functions' => array ('filter_translatable_update_translation'),
        'requiredcapability' => 'filter/translatable:edittranslations',
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' =>  'Translatable',
        'downloadfiles' => 0,
        'uploadfiles'  => 0
    )
);

$functions = array(
    'filter_translatable_update_translation' => array(
        'classname'     => 'filter_translatable_external',
        'methodname'    => 'update_translation',
        'classpath'     => 'filter/translatable/externallib.php',
        'description'   => 'Update Translation',
        'type'          => 'write',
        'ajax'          => true,
        // 'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities'  => 'filter/translatable:edittranslations',
    ),
);