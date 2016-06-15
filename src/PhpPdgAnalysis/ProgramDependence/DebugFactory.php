<?php

namespace PhpPdgAnalysis\ProgramDependence;

use PHPCfg\Func as CfgFunc;
use PhpPdg\ProgramDependence\FactoryInterface;

class DebugFactory implements FactoryInterface {
	private $message_count = 0;
	private $wrapped_factory;

	public function __construct(FactoryInterface $wrapped_factory) {
		$this->wrapped_factory = $wrapped_factory;
	}

	public function create(CfgFunc $cfg_func, $filename = null) {
		echo sprintf("#%d - creating pdg for %s\n", $this->message_count++, $cfg_func->getScopedName());
		return $this->wrapped_factory->create($cfg_func, $filename);
	}
}