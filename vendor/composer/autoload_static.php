<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0fc9720302c468167de5150308ba0846
{
    public static $files = array (
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'ee2eba616b1493b62ef61c9c04a94bc1' => __DIR__ . '/..' . '/overtrue/validation/src/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Component\\Translation\\' => 30,
        ),
        'O' => 
        array (
            'Overtrue\\Validation\\' => 20,
        ),
        'F' => 
        array (
            'Faker\\' => 6,
        ),
        'C' => 
        array (
            'Carbon\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Component\\Translation\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/translation',
        ),
        'Overtrue\\Validation\\' => 
        array (
            0 => __DIR__ . '/..' . '/overtrue/validation/src',
        ),
        'Faker\\' => 
        array (
            0 => __DIR__ . '/..' . '/fzaninotto/faker/src/Faker',
        ),
        'Carbon\\' => 
        array (
            0 => __DIR__ . '/..' . '/nesbot/carbon/src/Carbon',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0fc9720302c468167de5150308ba0846::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0fc9720302c468167de5150308ba0846::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
