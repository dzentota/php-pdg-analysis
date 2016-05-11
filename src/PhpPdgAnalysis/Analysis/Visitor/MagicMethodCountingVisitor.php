<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class MagicMethodCountingVisitor extends AbstractAnalysisVisitor {
	private $magicMethodNames = [
		'__construct',
		'__destruct',
		'__call',
		'__callStatic',
		'__get',
		'__set',
		'__isset',
		'__unset',
		'__sleep',
		'__wakeup',
		'__toString',
		'__invoke',
		'__set_state',
		'__clone',
		'__debugInfo',
	];

	private $magicMethodCounts;

	public function enterLibrary() {
		$this->magicMethodCounts = array_fill_keys(array_map(function ($magicMethodName) {
			return $magicMethodName;
		}, $this->magicMethodNames), 0);
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\ClassMethod) {
			if (isset($this->magicMethodCounts[$node->name])) {
				$this->magicMethodCounts[$node->name]++;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), array_map(function ($magicMethodName) {
			return $this->magicMethodCounts[$magicMethodName];
		}, $this->magicMethodNames));
	}

	public function getSuppliedAnalysisKeys() {
		return array_map(function ($magicMethodName) {
			return $magicMethodName . 'Count';
		}, $this->magicMethodNames);
	}
}