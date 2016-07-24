<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

abstract class AbstractFuncAnalysis implements FuncAnalysisInterface {
	public function enterLibrary($libraryname) {}

	public function leaveLibrary($libraryname) {}
}