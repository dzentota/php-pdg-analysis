<?php

namespace PhpPdgAnalysis\SystemDependence;

use PhpPdg\CfgBridge\System as CfgBridgeSystem;
use PhpPdg\SystemDependence\FactoryInterface;
use PhpPdg\SystemDependence\FilesystemFactoryInterface;

class DebugFilesystemFactory implements FilesystemFactoryInterface {
	private $message_count = 0;
	private $wrapped_factory;

	public function __construct(FilesystemFactoryInterface $wrapped_factory) {
		$this->wrapped_factory = $wrapped_factory;
	}

	public function create($dirname) {
		echo sprintf("#%d - creating sdg for system dir `$dirname`\n", $this->message_count++);
		return $this->wrapped_factory->create($dirname);
	}
}