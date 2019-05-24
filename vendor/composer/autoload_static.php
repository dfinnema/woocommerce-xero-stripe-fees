<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc27180d44c300c0fb2a2e60f0843deca
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LaLit\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LaLit\\' => 
        array (
            0 => __DIR__ . '/..' . '/digitickets/lalit/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc27180d44c300c0fb2a2e60f0843deca::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc27180d44c300c0fb2a2e60f0843deca::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}