<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class CallUserFuncCountingVisitor extends AbstractAnalysisVisitor {
	private $funcCallUserFuncCounts;
	private $funcsWithCallUserFuncCount;
	private $callUserFuncCount;

	public function enterLibrary($libraryname) {
		$this->funcCallUserFuncCounts = [];
		$this->funcsWithCallUserFuncCount = 0;
		$this->callUserFuncCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
		}

		if ($node instanceof Node\Expr\FuncCall) {
			if ($node->name instanceof Node\Name) {
				if (in_array($node->name->toString(), [
					'call_user_func',
					'call_user_func_array',
					'call_user_method',
					'call_user_method_array',
				], true) === true) {
					$this->funcCallUserFuncCounts[0]++;
				}
			}
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
		array_unshift($this->funcCallUserFuncCounts, 0);
	}

	public function popFunc() {
		$funcCallUserFuncCount = array_shift($this->funcCallUserFuncCounts);
		if ($funcCallUserFuncCount > 0) {
			$this->funcsWithCallUserFuncCount++;
			$this->callUserFuncCount += $funcCallUserFuncCount;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithCallUserFuncCount,
			$this->callUserFuncCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithCallUserFuncCount",
			"callUserFuncCount",
		];
	}
}