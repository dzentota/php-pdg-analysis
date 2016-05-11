<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node\Expr\Yield_;
use PhpParser\Node;

class YieldCountingVisitor extends AbstractAnalysisVisitor {
	private $funcYieldCounts;
	private $funcsWithYieldCount;
	private $yieldCount;

	public function enterLibrary() {
		$this->funcYieldCounts = [0];
		$this->funcsWithYieldCount = 0;
		$this->yieldCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcYieldCounts, 0);
		}
		if ($node instanceof Yield_) {
			$this->funcYieldCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		$funcYieldCount = array_shift($this->funcYieldCounts);
		if ($funcYieldCount > 0) {
			$this->funcsWithYieldCount++;
			$this->yieldCount += $funcYieldCount;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithYieldCount,
			$this->yieldCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcsWithYieldCount',
			'yieldCount',
		];
	}
}