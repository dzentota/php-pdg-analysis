<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\TryCatch;

class FuncExceptionCountingVisitor extends AbstractAnalysisVisitor {
	private $funcTryCounts;
	private $funcsWithTryCount;
	private $tryCount;

	private $funcThrowCounts;
	private $funcsWithThrowCount;
	private $throwCount;

	private $tryWithCatchCount;
	private $catchCount;
	private $tryWithFinallyCount;

	private $funcLastTryStmtStacks;
	private $funcThrowInTryCounts;
	private $funcsWithThrowInTryCount;
	private $throwInTryCount;

	public function enterLibrary() {
		$this->funcTryCounts = [0];
		$this->funcsWithTryCount = 0;
		$this->tryCount = 0;

		$this->funcThrowCounts = [0];
		$this->funcsWithThrowCount = 0;
		$this->throwCount = 0;

		$this->tryWithCatchCount = 0;
		$this->catchCount = 0;
		$this->tryWithFinallyCount = 0;

		$this->funcLastTryStmtStacks = [[]];
		$this->funcThrowInTryCounts = [0];
		$this->funcsWithThrowInTryCount = 0;
		$this->throwInTryCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcTryCounts, 0);
			array_unshift($this->funcThrowCounts, 0);
			array_unshift($this->funcLastTryStmtStacks, []);
			array_unshift($this->funcThrowInTryCounts, 0);
		}
		if ($node instanceof TryCatch) {
			$this->funcTryCounts[0]++;
			if (!empty($node->catches)) {
				$this->tryWithCatchCount++;
				$this->catchCount += count($node->catches);
			}
			if ($node->finallyStmts !== null) {
				$this->tryWithFinallyCount++;
			}
			array_unshift($this->funcLastTryStmtStacks[0], $node->stmts[count($node->stmts) - 1]);
		}
		if ($node instanceof Node\Stmt\Throw_) {
			$this->funcThrowCounts[0]++;
			if (count($this->funcLastTryStmtStacks[0]) > 0) {
				$this->funcThrowInTryCounts[0]++;
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
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
			$funcLastTryStmtStack = array_shift($this->funcLastTryStmtStacks);
			if (!empty($funcLastTryStmtStack)) {
				throw new \LogicException("This should not happen.");
			}
			$funcThrowInTryCount = array_shift($this->funcThrowInTryCounts);
			if ($funcThrowInTryCount > 0) {
				$this->funcsWithThrowInTryCount++;
				$this->throwInTryCount += $funcThrowInTryCount;
			}
		}
		if (count($this->funcLastTryStmtStacks[0]) > 0 && $node === $this->funcLastTryStmtStacks[0][0]) {
			array_shift($this->funcLastTryStmtStacks[0]);
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithTryCount,
			$this->tryCount,
			$this->funcsWithThrowCount,
			$this->throwCount,
			$this->tryWithCatchCount,
			$this->catchCount,
			$this->tryWithFinallyCount,
			$this->funcsWithThrowInTryCount,
			$this->throwInTryCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcsWithTryCount',
			'tryCount',
			'funcsWithThrowCount',
			'throwCount',
			'tryWithCatchCount',
			'catchCount',
			'tryWithFinallyCount',
			'funcsWithThrowInTryCount',
			'throwInTryCount',
		];
	}
}