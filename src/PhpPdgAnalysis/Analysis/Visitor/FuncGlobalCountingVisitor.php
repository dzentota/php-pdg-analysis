<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncGlobalCountingVisitor extends AbstractAnalysisVisitor {
	private $funcGlobalCounts;
	public $funcsWithGlobalCount;
	public $globalCount;

	public function enterLibrary() {
		$this->funcGlobalCounts = [0];
		$this->funcsWithGlobalCount = 0;
		$this->globalCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcGlobalCounts, 0);
		}

		if ($node instanceof Node\Stmt\Global_) {
			$this->funcGlobalCounts[0]++;
		}
		if ($node instanceof Node\Expr\Variable) {
			$name = $node->name;
			if ($name instanceof Node\Name && $name->toString() === 'GLOBALS') {
				$this->funcGlobalCounts[0]++;
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcGlobalCount = array_shift($this->funcGlobalCounts);
			if ($funcGlobalCount > 0) {
				$this->funcsWithGlobalCount++;
				$this->globalCount += $funcGlobalCount;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithGlobalCount,
			$this->globalCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithGlobalCount",
			"globalCount",
		];
	}
}