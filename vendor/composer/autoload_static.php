<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3b699db90daa452fa8ec0c2b183a268c
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OctaviusRocks\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OctaviusRocks\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3b699db90daa452fa8ec0c2b183a268c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3b699db90daa452fa8ec0c2b183a268c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3b699db90daa452fa8ec0c2b183a268c::$classMap;

        }, null, ClassLoader::class);
    }
}
