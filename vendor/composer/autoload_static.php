<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit51a082672f32144cf70045c22f06574f
{
    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'NetworkLeads\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'NetworkLeads\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit51a082672f32144cf70045c22f06574f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit51a082672f32144cf70045c22f06574f::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
