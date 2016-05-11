<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class TraitCountingVisitor extends AbstractAnalysisVisitor {
	private $traitCount;
	private $traitUseCount;

	public function enterLibrary() {
		$this->traitCount = 0;
		$this->traitUseCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Trait_) {
			$this->traitCount++;
		}
		if ($node instanceof Node\Stmt\TraitUse) {
			$this->traitUseCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->traitCount,
			$this->traitUseCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"traitCount",
			"traitUseCount",
		];
	}
}