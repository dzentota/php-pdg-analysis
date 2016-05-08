<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\NodeVisitorAbstract;

class ExceptionCountingVisitor extends NodeVisitorAbstract {
	public $tryCount;
	public $tryWithCatchCount;
	public $totalCatchCount;
	public $tryWithfinallyCount;
	public $throwCount;
	public $throwInTryCount;
	public $prevTryCounts = [];
	public $lastTryStatements = [];

	public function beforeTraverse(array $nodes) {
		$this->tryCount = 0;
		$this->tryWithCatchCount = 0;
		$this->totalCatchCount = 0;
		$this->tryWithfinallyCount = 0;
		$this->throwCount = 0;
		$this->throwInTryCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_push($this->prevTryCounts, $this->lastTryStatements);
		}
		if ($node instanceof TryCatch) {
			if (!empty($node->stmts)) {
				$this->lastTryStatements[] = $node->stmts[count($node->stmts) - 1];
			}
			$this->lastTryStatements++;
			$this->tryCount++;
			if (!empty($node->catches)) {
				$this->tryWithCatchCount++;
				$this->totalCatchCount += count($node->catches);
			}
			if ($node->finallyStmts !== null) {
				$this->tryWithfinallyCount++;
			}
		} else if ($node instanceof Node\Stmt\Throw_) {
			if (!empty($this->lastTryStatements)) {
				$this->throwInTryCount++;
			}
			$this->throwCount++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->lastTryStatements = array_pop($this->prevTryCounts);
		} else if (!empty($this->lastTryStatements) && $node === $this->lastTryStatements[count($this->lastTryStatements) - 1]) {
			array_pop($this->lastTryStatements);
		}
	}
}