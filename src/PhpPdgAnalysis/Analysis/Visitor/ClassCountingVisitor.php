<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class ClassCountingVisitor extends AbstractAnalysisVisitor {
	private $classCount;

	public function enterLibrary() {
		$this->classCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Class_) {
			$this->classCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->classCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"classCount",
		];
	}
}