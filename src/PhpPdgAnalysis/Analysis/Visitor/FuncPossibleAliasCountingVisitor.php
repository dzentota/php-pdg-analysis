<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncPossibleAliasCountingVisitor extends AbstractAnalysisVisitor {
	private $funcAssignRefCounts;
	private $funcsWithAssignRefCount;
	private $assignRefCount;

	private $funcGlobalCounts;
	private $funcsWithGlobalCount;
	private $globalCount;

	private $funcsWithAnyPossibleAliasCount;

	public function enterLibrary($libraryname) {
		$this->funcAssignRefCounts = [];
		$this->funcsWithAssignRefCount = 0;
		$this->assignRefCount = 0;

		$this->funcGlobalCounts = [];
		$this->funcsWithGlobalCount = 0;
		$this->globalCount = 0;

		$this->funcsWithAnyPossibleAliasCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
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
			$this->popFunc();
		}
	}

	public function afterTraverse(array $nodes) {
		$this->popFunc();
	}

	public function pushFunc() {
		array_unshift($this->funcAssignRefCounts, 0);
		array_unshift($this->funcGlobalCounts, 0);
	}

	public function popFunc() {
		$funcAssignRefCount = array_shift($this->funcAssignRefCounts);
		if ($funcAssignRefCount > 0) {
			$this->funcsWithAssignRefCount++;
			$this->assignRefCount += $funcAssignRefCount;
		}

		$funcGlobalCount = array_shift($this->funcGlobalCounts);
		if ($funcGlobalCount > 0) {
			$this->funcsWithGlobalCount++;
			$this->globalCount += $funcGlobalCount;
		}

		if ($funcAssignRefCount > 0 || $funcGlobalCount > 0) {
			$this->funcsWithAnyPossibleAliasCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithAssignRefCount,
			$this->assignRefCount,
			$this->funcsWithGlobalCount,
			$this->globalCount,
			$this->funcsWithAnyPossibleAliasCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithAssignRefCount",
			"assignRefCount",
			"funcsWithGlobalCount",
			"globalCount",
			"funcsWithAnyPossibleAliasCount",
		];
	}
}