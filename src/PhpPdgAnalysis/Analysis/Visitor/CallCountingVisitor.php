<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class CallCountingVisitor extends AbstractAnalysisVisitor {
	private $funcCallCount;
	private $methodCallCount;
	private $staticCallCount;

	public function enterLibrary() {
		$this->funcCallCount= 0;
		$this->methodCallCount = 0;
		$this->staticCallCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Expr\FuncCall) {
			$this->funcCallCount++;
		}
		if ($node instanceof Node\Expr\MethodCall) {
			$this->methodCallCount++;
		}
		if ($node instanceof Node\Expr\StaticCall) {
			$this->staticCallCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcCallCount,
			$this->methodCallCount,
			$this->staticCallCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcCallCount",
			"methodCallCount",
			"staticCallCount",
		];
	}
}