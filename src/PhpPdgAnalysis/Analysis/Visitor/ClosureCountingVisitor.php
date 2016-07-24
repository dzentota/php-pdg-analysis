<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class ClosureCountingVisitor extends AbstractAnalysisVisitor {
	private $closureCount;
	private $closureWithUseCount;

	public function enterLibrary($libraryname) {
		$this->closureCount = 0;
		$this->closureWithUseCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Expr\Closure) {
			$this->closureCount++;
			if (!empty($node->uses)) {
				$this->closureWithUseCount++;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->closureCount,
			$this->closureWithUseCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"closureCount",
			"closureWithUseCount",
		];
	}
}