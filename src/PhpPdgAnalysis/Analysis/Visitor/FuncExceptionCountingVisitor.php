<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncExceptionCountingVisitor extends AbstractAnalysisVisitor {
	private $funcTryCounts;
	private $funcsWithTryCount;
	private $tryCount;

	private $funcThrowCounts;
	private $funcsWithThrowCount;
	private $throwCount;

	private $funcsWithTryAndThrowCount;

	public function enterLibrary($libraryname) {
		$this->funcTryCounts = [];
		$this->funcsWithTryCount = 0;
		$this->tryCount = 0;

		$this->funcThrowCounts= [];
		$this->funcsWithThrowCount = 0;
		$this->throwCount = 0;

		$this->funcsWithTryAndThrowCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
		}

		if ($node instanceof Node\Stmt\TryCatch) {
			$this->funcTryCounts[0]++;
		}
		if ($node instanceof Node\Stmt\Throw_) {
			$this->funcThrowCounts[0]++;
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
		array_unshift($this->funcTryCounts, 0);
		array_unshift($this->funcThrowCounts, 0);
	}

	public function popFunc() {
		$funcTryCount = array_shift($this->funcTryCounts);
		if ($funcTryCount > 0) {
			$this->funcsWithTryCount++;
			$this->tryCount += $funcTryCount;
		}
		$funcThrowCount = array_shift($this->funcThrowCounts);
		if ($funcThrowCount > 0) {
			$this->funcsWithThrowCount++;
			$this->throwCount += $funcThrowCount;
		}
		if ($funcTryCount > 0 && $funcThrowCount > 0) {
			$this->funcsWithTryAndThrowCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithTryCount,
			$this->tryCount,
			$this->funcsWithThrowCount,
			$this->throwCount,
			$this->funcsWithTryAndThrowCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcsWithTryCount',
			'tryCount',
			'funcsWithThrowCount',
			'throwCount',
			'funcsWithTryAndThrowCount',
		];
	}
}