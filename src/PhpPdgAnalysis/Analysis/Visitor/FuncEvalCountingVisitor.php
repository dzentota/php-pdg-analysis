<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FuncEvalCountingVisitor extends NodeVisitorAbstract implements AnalysisVisitorInterface{
	private $funcEvalCounts;
	public $funcsWithEvalCount;
	public $evalCount;

	public function beforeTraverse(array $nodes) {
		$this->funcEvalCounts = [0];
		$this->funcsWithEvalCount = 0;
		$this->evalCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcEvalCounts, 0);
		}

		if ($node instanceof Node\Expr\Eval_) {
			$this->funcEvalCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcEvalCount = array_shift($this->funcEvalCounts);
			if ($funcEvalCount > 0) {
				$this->funcsWithEvalCount++;
				$this->evalCount += $funcEvalCount;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithEvalCount,
			$this->evalCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithEvalCount",
			"evalCount",
		];
	}
}