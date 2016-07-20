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
		$filenames = $cfg_system->getFilenames();
		$func_count = 0;
		foreach ($filenames as $file_path) {
			$func_count += 1 + count($cfg_system->getScript($file_path)->functions);
		}
		echo sprintf("#%d - creating sdg for system with %d files and %d funcs\n", $this->message_count++, count($filenames), $func_count);
		return $this->wrapped_factory->create($cfg_system);
	}
}