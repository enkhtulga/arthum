<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit47461ade21ebc4d322a815ff37c5d44d
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit47461ade21ebc4d322a815ff37c5d44d', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit47461ade21ebc4d322a815ff37c5d44d', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit47461ade21ebc4d322a815ff37c5d44d::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}