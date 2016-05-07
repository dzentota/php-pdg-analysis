<?php

namespace PhpPdgAnalysis\Analyser;

use PhpParser\NodeTraverser;
use PhpPdgAnalysis\Analyser\Visitor\AnalysingVisitorInterface;

abstract class AbstractVisitingAnalyser implements AnalyserInterface {
	public function __construct() {

	}

	public function analyse(\SplFileInfo $libraryPath) {
		$traverser = new NodeTraverser();
		$visitor = $this->getVisitor();
		$traverser->addVisitor($visitor);
		$traverser->traverse($nodes);
		return $visitor->getAnalysisResults();
	}

	/**
	 * @return AnalysingVisitorInterface
	 */
	abstract protected function getVisitor();
}