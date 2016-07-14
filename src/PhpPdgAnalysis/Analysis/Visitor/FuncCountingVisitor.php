<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncCountingVisitor extends AbstractAnalysisVisitor {
	private $funcCount;
	private $classMethodCount;
	private $closureCount;
	private $scriptCount;

	public function enterLibrary() {
		$this->funcCount = 0;
		$this->classMethodCount = 0;
		$this->closureCount = 0;
		$this->scriptCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->scriptCount++;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\Function_) {
			$this->funcCount++;
		}
		if ($node instanceof Node\Stmt\ClassMethod) {
			$this->classMethodCount++;
		}
		if ($node instanceof Node\Expr\Closure) {
			$this->closureCount++;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcCount,
			$this->classMethodCount,
			$this->closureCount,
			$this->scriptCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcCount",
			"methodCount",
			"closureCount",
			"scriptCount",
		];
	}
}