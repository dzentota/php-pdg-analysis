<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class CallUserFuncCountingVisitor extends AbstractAnalysisVisitor {
	private $callUserFuncCount;
	private $callUserFuncArrayCount;

	public function enterLibrary() {
		$this->callUserFuncCount = 0;
		$this->callUserFuncArrayCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Expr\FuncCall) {
			if ($node->name instanceof Node\Name) {
				if ($node->name->toString() === 'call_user_func') {
					$this->callUserFuncCount++;
				}
				if ($node->name->toString() === 'call_user_func_array') {
					$this->callUserFuncArrayCount++;
				}
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->callUserFuncCount,
			$this->callUserFuncArrayCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"callUserFuncCount",
			"callUserFuncArrayCount",
		];
	}
}