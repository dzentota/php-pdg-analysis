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

	public function create($systemdir) {
		echo sprintf("#%d - creating sdg for system dir `$systemdir`\n", $this->message_count++);
		return $this->wrapped_factory->create($systemdir);
	}
}