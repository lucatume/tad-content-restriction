<?php

// autoload_real_52.php generated by xrstf/composer-php52

class ComposerAutoloaderInit667d0e0d249f26b4ac49a1533d4d1c28 {
	private static $loader;

	public static function loadClassLoader($class) {
		if ('xrstf_Composer52_ClassLoader' === $class) {
			require dirname(__FILE__).'/ClassLoader52.php';
		}
	}

	/**
	 * @return xrstf_Composer52_ClassLoader
	 */
	public static function getLoader() {
		if (null !== self::$loader) {
			return self::$loader;
		}

		spl_autoload_register(array('ComposerAutoloaderInit667d0e0d249f26b4ac49a1533d4d1c28', 'loadClassLoader'), true /*, true */);
		self::$loader = $loader = new xrstf_Composer52_ClassLoader();
		spl_autoload_unregister(array('ComposerAutoloaderInit667d0e0d249f26b4ac49a1533d4d1c28', 'loadClassLoader'));

		$vendorDir = dirname(dirname(__FILE__));
		$baseDir   = dirname($vendorDir);
		$dir       = dirname(__FILE__);

		$map = require $dir.'/autoload_namespaces.php';
		foreach ($map as $namespace => $path) {
			$loader->add($namespace, $path);
		}

		$classMap = require $dir.'/autoload_classmap.php';
		if ($classMap) {
			$loader->addClassMap($classMap);
		}

		$loader->register(true);

//		require $vendorDir . '/guzzlehttp/psr7/src/functions_include.php'; // disabled because of PHP 5.3 syntax
//		require $vendorDir . '/guzzlehttp/promises/src/functions_include.php'; // disabled because of PHP 5.3 syntax
//		require $vendorDir . '/guzzlehttp/guzzle/src/functions_include.php'; // disabled because of PHP 5.3 syntax
		require $vendorDir . '/lucatume/tad-reschedule/tad-reschedule.php';
		require $vendorDir . '/webdevstudios/cmb2/init.php';
		require $vendorDir . '/lucatume/tad-reschedule/tad-reschedule.php';

		return $loader;
	}
}
