<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\NodeVisitorAbstract;

abstract class AbstractAnalysisVisitor extends NodeVisitorAbstract implements AnalysisVisitorInterface {
	public function enterLibrary($libraryname) {}

	public function leaveLibrary($libraryname) {}
}

