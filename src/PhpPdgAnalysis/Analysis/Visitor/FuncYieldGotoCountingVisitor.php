<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncYieldGotoCountingVisitor extends AbstractAnalysisVisitor {
	private $funcYieldCounts;
	private $funcsWithYieldCount;
	private $yieldCount;

	private $funcGotoCounts;
	private $funcsWithGotoCount;
	private $gotoCount;

	public function enterLibrary($libraryname) {
		$this->funcYieldCounts = [];
		$this->funcsWithYieldCount = 0;
		$this->yieldCount = 0;

		$this->funcGotoCounts = [];
		$this->funcsWithGotoCount = 0;
		$this->gotoCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
		}

		if ($node instanceof Node\Expr\Yield_) {
			$this->funcYieldCounts[0]++;
		}
		if ($node instanceof Node\Stmt\Goto_) {
			$this->funcGotoCounts[0]++;
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
		array_unshift($this->funcYieldCounts, 0);
		array_unshift($this->funcGotoCounts, 0);
	}

	public function popFunc() {
		$funcYieldCount = array_shift($this->funcYieldCounts);
		if ($funcYieldCount > 0) {
			$this->funcsWithYieldCount++;
			$this->yieldCount += $funcYieldCount;
		}
		$funcGotoCount = array_shift($this->funcGotoCounts);
		if ($funcGotoCount > 0) {
			$this->funcsWithGotoCount++;
			$this->gotoCount += $funcGotoCount;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithYieldCount,
			$this->yieldCount,
			$this->funcsWithGotoCount,
			$this->gotoCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcsWithYieldCount',
			'yieldCount',
			'funcsWithGotoCount',
			'gotoCount',
		];
	}
}