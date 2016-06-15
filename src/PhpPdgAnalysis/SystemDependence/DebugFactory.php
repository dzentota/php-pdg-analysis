<?php

namespace PhpPdgAnalysis\SystemDependence;

use PhpPdg\CfgBridge\System as CfgBridgeSystem;
use PhpPdg\SystemDependence\FactoryInterface;

class DebugFactory implements FactoryInterface {
	private $message_count = 0;
	private $wrapped_factory;

	public function __construct(FactoryInterface $wrapped_factory) {
		$this->wrapped_factory = $wrapped_factory;
	}

	public function create(CfgBridgeSystem $cfg_system) {
		$func_count = 0;
		foreach ($cfg_system->getFilePaths() as $file_path) {
			$func_count += 1 + count($cfg_system->getScript($file_path)->getScript()->functions);
		}
		echo sprintf("#%d - creating sdg for system with %d files and %d funcs\n", $this->message_count++, count($cfg_system->getFilePaths()), $func_count);
		return $this->wrapped_factory->create($cfg_system);
	}
}