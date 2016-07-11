<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncVarFeatureCountingVisitor extends AbstractAnalysisVisitor {
	private $funcVarVarCounts;
	public $funcsWithVarVarCount;
	public $varVarCount;

	private $funcVarFuncCallCounts;
	public $funcsWithVarFuncCallCount;
	public $varFuncCallCount;

	private $funcVarMethodCallCounts;
	public $funcsWithVarMethodCallCount;
	public $varMethodCallCount;

	private $funcVarPropertyFetchCounts;
	public $funcsWithVarPropertyFetchCount;
	public $varPropertyFetchCount;

	private $funcVarInstanceCounts;
	public $funcsWithVarInstanceCount;
	public $varInstanceCount;

	public function enterLibrary() {
		$this->funcVarVarCounts = [];
		$this->funcsWithVarVarCount = 0;
		$this->varVarCount = 0;

		$this->funcVarFuncCallCounts = [];
		$this->funcsWithVarFuncCallCount = 0;
		$this->varFuncCallCount = 0;

		$this->funcVarMethodCallCounts = [];
		$this->funcsWithVarMethodCallCount = 0;
		$this->varMethodCallCount = 0;

		$this->funcVarPropertyFetchCounts = [];
		$this->funcsWithVarPropertyFetchCount = 0;
		$this->varPropertyFetchCount = 0;

		$this->funcVarInstanceCounts = [];
		$this->funcsWithVarInstanceCount = 0;
		$this->varInstanceCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
		}

		if ($node instanceof Node\Expr\Variable && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarVarCounts[0]++;
		}

		if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarFuncCallCounts[0]++;
		}

		if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarMethodCallCounts[0]++;
		}

		if ($node instanceof Node\Expr\PropertyFetch && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarPropertyFetchCounts[0]++;
		}

		if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Expr\Variable) {
			$this->funcVarInstanceCounts[0]++;
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

	private function pushFunc() {
		array_unshift($this->funcVarVarCounts, 0);
		array_unshift($this->funcVarFuncCallCounts, 0);
		array_unshift($this->funcVarMethodCallCounts, 0);
		array_unshift($this->funcVarPropertyFetchCounts, 0);
		array_unshift($this->funcVarInstanceCounts, 0);
	}

	private function popFunc() {
		$funcVarVarCount = array_shift($this->funcVarVarCounts);
		if ($funcVarVarCount > 0) {
			$this->varVarCount += $funcVarVarCount;
			$this->funcsWithVarVarCount++;
		}

		$funcVarFuncCallCount = array_shift($this->funcVarFuncCallCounts);
		if ($funcVarFuncCallCount > 0) {
			$this->varFuncCallCount += $funcVarFuncCallCount;
			$this->funcsWithVarFuncCallCount++;
		}

		$funcVarMethodCallCount = array_shift($this->funcVarMethodCallCounts);
		if ($funcVarMethodCallCount > 0) {
			$this->varMethodCallCount += $funcVarMethodCallCount;
			$this->funcsWithVarMethodCallCount++;
		}

		$funcVarPropertyFetchCount = array_shift($this->funcVarPropertyFetchCounts);
		if ($funcVarPropertyFetchCount > 0) {
			$this->varPropertyFetchCount += $funcVarPropertyFetchCount;
			$this->funcsWithVarPropertyFetchCount++;
		}

		$funcVarInstanceCount = array_shift($this->funcVarInstanceCounts);
		if ($funcVarInstanceCount > 0) {
			$this->varInstanceCount += $funcVarInstanceCount;
			$this->funcsWithVarInstanceCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithVarVarCount,
			$this->varVarCount,
			$this->funcsWithVarFuncCallCount,
			$this->varFuncCallCount,
			$this->funcsWithVarMethodCallCount,
			$this->varMethodCallCount,
			$this->funcsWithVarPropertyFetchCount,
			$this->varPropertyFetchCount,
			$this->funcsWithVarInstanceCount,
			$this->varInstanceCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithVarVarCount",
			"varVarCount",
			"funcsWithVarFuncCallCount",
			"varFuncCallCount",
			"funcsWithVarMethodCallCount",
			"varMethodCallCount",
			"funcsWithVarPropertyFetchCount",
			"varPropertyFetchCount",
			"funcsWithVarInstanceCount",
			"varInstanceCount",
		];
	}
}