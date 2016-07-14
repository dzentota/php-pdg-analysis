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

	public function create(array $filenames) {
		echo sprintf("#%d - creating sdg for system with %d files\n", $this->message_count++, count($filenames));
		return $this->wrapped_factory->create($filenames);
	}
}