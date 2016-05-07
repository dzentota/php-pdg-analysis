<?php

namespace PhpPdgAnalysis\Analyser;

use PhpPdgAnalysis\Analyser\AnalyserInterface;

class LibraryInfo implements AnalyserInterface {
	public function analyse(\SplFileInfo $libraryPath) {
		$info = [
			"cakephp" => [
				"name" => "CakePHP",
				"version" => "3.2.8",
				"release" => "24-04-2016",
				"php" => "5.5.9",
			],
			"CodeIgniter" => [
				"name" => "CodeIgniter",
				"version" => "3.0.6",
				"release" => "21-03-2016",
			],
			"doctrine2" => [
				"name" => "Doctrine ORM",
				"version" => "2.5.0",
				"release" => "02-04-2016",
			],
			"drupal" => [
				"name" => "Drupal",
				"version" => "8.1.0",
				"release" => "20-04-2016",
			],
			"gallery3" => [
				"name" => "Gallery",
				"version" => "3.0.9",
				"release" => "26-06-2013",
			],
			"joomla-cms" => [
				"name" => "Joomla",
				"version" => "3.5.1",
				"release" => "05-04-2016",
			],
			"kohana" => [
				"name" => "Kohana",
				"version" => "3.3.5",
				"release" => "10-03-2016",
			],
			"magento2" => [
				"name" => "Magento",
				"version" => "2.0.4",
				"release" => "31-03-2016",
			],
			"mediawiki-1.26.2" => [
				"name" => "MediaWiki",
				"version" => "1.26.2",
				"release" => "21-12-2015",
			],
			"moodle" => [
				"name" => "Moodle",
				"version" => "3.0.3",
				"release" => "12-03-2015",
			],
			"oscommerce2" => [
				"name" => "osCommerce",
				"version" => "2.3.4",
				"release" => "06-06-2014",
			],
			"pear-core" => [
				"name" => "PEAR",
				"version" => "1.10.1",
				"release" => "17-10-2015",
			],
			"phpbb-app" => [
				"name" => "phpBB",
				"version" => "3.1.5",
				"release" => "03-05-2015",
			],
			"phpmyadmin" => [
				"name" => "phpMyAdmin",
				"version" => "4.6.0",
				"release" => "17-05-2016",
			],
			"silverstripe-framework" => [
				"name" => "SilverStripe",
				"version" => "3.3.1",
				"release" => "29-02-2016",
			],
			"smarty" => [
				"name" => "Smarty",
				"version" => "3.1.29",
				"release" => "21-12-2015",
			],
			"squirrelmail" => [
				"name" => "Squirrel Mail",
				"version" => "1.4.22",
				"release" => "12-07-2011",
			],
			"symfony" => [
				"name" => "Symfony",
				"version" => "3.0.4",
				"release" => "30-03-2016",
			],
			"WordPress" => [
				"name" => "WordPress",
				"version" => "4.5",
				"release" => "12-04-2016",
			],
			"zf2" => [
				"name" => "Zend Framework",
				"version" => "2.5.3",
				"release" => "27-01-2016",
			],
		];
		$filename = $libraryPath->getFilename();
		return isset($info[$filename]) ? $info[$filename] : [];
	}
}