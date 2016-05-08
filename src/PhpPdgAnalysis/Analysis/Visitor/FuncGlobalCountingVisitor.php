<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FuncGlobalCountingVisitor extends NodeVisitorAbstract implements AnalysisVisitorInterface {
	private $funcGlobalCounts;
	public $funcsWithGlobalCount;
	public $globalCount;

	public function beforeTraverse(array $nodes) {
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
		return [
			"funcsWithAssignRefCount" => $this->funcsWithGlobalCount,
			"globalCount" => $this->globalCount,
		];
	}

	public function getSuppliedAnalysisKeys() {
		return ["funcsWithAssignRefCount", "globalCount"];
	}
}