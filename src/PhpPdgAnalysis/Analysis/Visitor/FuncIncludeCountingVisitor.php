<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncIncludeCountingVisitor extends AbstractAnalysisVisitor {
	private $funcIncludeCounts;
	public $funcsWithIncludeCount;
	public $includeCount;

	public function enterLibrary() {
		$this->funcIncludeCounts = [0];
		$this->funcsWithIncludeCount = 0;
		$this->includeCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcIncludeCounts, 0);
		}

		if ($node instanceof Node\Expr\Include_) {
			$this->funcIncludeCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcIncludeCount = array_shift($this->funcIncludeCounts);
			if ($funcIncludeCount > 0) {
				$this->funcsWithIncludeCount++;
				$this->includeCount += $funcIncludeCount;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithIncludeCount,
			$this->includeCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithIncludeCount",
			"includeCount"
		];
	}
}