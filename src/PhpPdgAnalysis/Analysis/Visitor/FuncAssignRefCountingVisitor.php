<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncAssignRefCountingVisitor extends AbstractAnalysisVisitor {
	private $funcAssignRefCounts;
	public $funcsWithAssignRefCount;
	public $assignRefCount;

	public function enterLibrary() {
		$this->funcAssignRefCounts = [0];
		$this->funcsWithAssignRefCount = 0;
		$this->assignRefCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcAssignRefCounts, 0);
		}

		if ($node instanceof Node\Expr\AssignRef) {
			$this->funcAssignRefCounts[0]++;
		}
		if ($node instanceof Node\Expr\ArrayItem && $node->byRef === true) {
			$this->funcAssignRefCounts[0]++;
		}
		if ($node instanceof Node\Stmt\Foreach_ && $node->byRef === true) {
			$this->funcAssignRefCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcAssignRefCount = array_shift($this->funcAssignRefCounts);
			if ($funcAssignRefCount > 0) {
				$this->funcsWithAssignRefCount++;
				$this->assignRefCount += $funcAssignRefCount;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithAssignRefCount,
			$this->assignRefCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithAssignRefCount",
			"assignRefCount",
		];
	}
}