<?php
return array (
    'yima_locali_plugins' => array(
        'factories'  => array(
            'dateTime' => '\yimaLocalize\LocaliPlugins\DateTimeFactory',
        ),
    ),

    // Translator settings
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.php',
                //yimaLocalize translations using as default Text Domain
                #'text_domain' => 'default',
            ),
        ),
    ),
);
