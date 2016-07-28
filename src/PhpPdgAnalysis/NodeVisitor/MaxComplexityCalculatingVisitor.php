<?php

namespace PhpPdgAnalysis\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MaxComplexityCalculatingVisitor extends NodeVisitorAbstract {
	private $stack;
	private $current;
	private $done;

	public function getMaxComplexity() {
		return empty($this->done) === false ? max($this->current, max($this->done)) : $this->current;
	}

	public function beforeTraverse(array $nodes) {
		$this->stack = [];
		$this->current = 1;
		$this->done = [];
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->stack[] = $this->current;
			$this->current = 1;
		} else if (
			$node instanceof Node\Stmt\If_
			|| $node instanceof Node\Stmt\ElseIf_
			|| $node instanceof Node\Expr\Ternary
			|| $node instanceof Node\Stmt\Foreach_
			|| $node instanceof Node\Stmt\For_
			|| $node instanceof Node\Stmt\While_
			|| $node instanceof Node\Expr\BinaryOp\BooleanAnd
			|| $node instanceof Node\Expr\BinaryOp\LogicalAnd
			|| $node instanceof Node\Expr\BinaryOp\BooleanOr
			|| $node instanceof Node\Expr\BinaryOp\LogicalOr
			|| $node instanceof Node\Expr\BinaryOp\Spaceship
			|| $node instanceof Node\Stmt\Case_
			|| $node instanceof Node\Expr\BinaryOp\Coalesce
		) {
			$this->current++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->done[] = $this->current;
			$this->current = array_shift($this->stack);
		}
	}
}