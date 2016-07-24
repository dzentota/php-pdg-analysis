<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class CallUserFuncCountingVisitor extends AbstractAnalysisVisitor {
	private $callUserFuncCount;
	private $callUserFuncArrayCount;
	private $callUserMethodCount;
	private $callUserMethodArrayCount;

	public function enterLibrary($libraryname) {
		$this->callUserFuncCount = 0;
		$this->callUserFuncArrayCount = 0;
		$this->callUserMethodCount = 0;
		$this->callUserMethodArrayCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Expr\FuncCall) {
			if ($node->name instanceof Node\Name) {
				switch ($node->name->toString()) {
					case 'call_user_func':
						$this->callUserFuncCount++;
						break;
					case 'call_user_func_array':
						$this->callUserFuncArrayCount++;
						break;
					case 'call_user_method':
						$this->callUserMethodCount++;
						break;
					case 'call_user_method_array':
						$this->callUserMethodArrayCount++;
						break;
				}
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->callUserFuncCount,
			$this->callUserFuncArrayCount,
			$this->callUserMethodCount,
			$this->callUserMethodArrayCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"callUserFuncCount",
			"callUserFuncArrayCount",
			"callUserMethodCount",
			"callUserMethodArrayCount",
		];
	}
}