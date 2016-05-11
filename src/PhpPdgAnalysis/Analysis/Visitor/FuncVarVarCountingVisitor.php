<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncVarVarCountingVisitor extends AbstractAnalysisVisitor {
	private $funcVarVarCounts;
	public $funcsWithVarVarCount;
	public $varVarCount;

	public function enterLibrary() {
		$this->funcVarVarCounts = [0];
		$this->funcsWithVarVarCount = 0;
		$this->varVarCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcVarVarCounts, 0);
		}

		if ($node instanceof Node\Expr\Variable && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarVarCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcVarVarCount = array_shift($this->funcVarVarCounts);
			if ($funcVarVarCount > 0) {
				$this->varVarCount += $funcVarVarCount;
				$this->funcsWithVarVarCount++;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithVarVarCount,
			$this->varVarCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithVarVarCount",
			"varVarCount",
		];
	}
}