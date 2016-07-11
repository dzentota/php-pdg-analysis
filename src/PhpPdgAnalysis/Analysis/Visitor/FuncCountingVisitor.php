<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncCountingVisitor extends AbstractAnalysisVisitor {
	private $funcCount;

	public function enterLibrary() {
		$this->funcCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->funcCount++;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->funcCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcCount"
		];
	}
}