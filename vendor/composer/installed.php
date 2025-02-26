<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'johnpbloch/wordpress' => array(
            'pretty_version' => '6.7.2',
            'version' => '6.7.2.0',
            'reference' => '687a120102a523a8638b36ebc4711f8a75b2c8b8',
            'type' => 'package',
            'install_path' => __DIR__ . '/../johnpbloch/wordpress',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'johnpbloch/wordpress-core' => array(
            'pretty_version' => '6.7.2',
            'version' => '6.7.2.0',
            'reference' => '341d968ef716af577f98f5c2201d41c4e291fdcf',
            'type' => 'wordpress-core',
            'install_path' => __DIR__ . '/../../wordpress',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'johnpbloch/wordpress-core-installer' => array(
            'pretty_version' => '2.0.0',
            'version' => '2.0.0.0',
            'reference' => '237faae9a60a4a2e1d45dce1a5836ffa616de63e',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/../johnpbloch/wordpress-core-installer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
        'wordpress/core-implementation' => array(
            'dev_requirement' => true,
            'provided' => array(
                0 => '6.7.2',
            ),
        ),
    ),
);
