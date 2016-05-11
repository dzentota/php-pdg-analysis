<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class DuplicateNameCountingVisitor extends AbstractAnalysisVisitor {
	private $classNameCounts;
	private $duplicateClassNameCount;

	private $namespacedClassNameCounts;
	private $duplicateNamespacedClassNameCount;

	private $funcNameCounts;
	private $duplicateFuncNameCount;

	private $namespacedFuncNameCounts;
	private $duplicateNamespacedFuncNameCount;

	public function enterLibrary() {
		$this->classNameCounts = [];
		$this->duplicateClassNameCount = 0;
		$this->namespacedClassNameCounts = [];
		$this->duplicateNamespacedClassNameCount = 0;
		$this->funcNameCounts = [];
		$this->duplicateFuncNameCount = 0;
		$this->namespacedFuncNameCounts = [];
		$this->duplicateNamespacedFuncNameCount = 0;
	}

	public function leaveLibrary() {
		foreach ($this->classNameCounts as $ct) {
			if ($ct > 1) {
				$this->duplicateClassNameCount++;
			}
		}
		foreach ($this->namespacedClassNameCounts as $ct) {
			if ($ct > 1) {
				$this->duplicateNamespacedClassNameCount++;
			}
		}
		foreach ($this->funcNameCounts as $ct) {
			if ($ct > 1) {
				$this->duplicateFuncNameCount++;
			}
		}
		foreach ($this->namespacedFuncNameCounts as $ct) {
			if ($ct > 1) {
				$this->duplicateNamespacedFuncNameCount++;
			}
		}
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Stmt\ClassLike) {
			if (isset($this->classNameCounts[$node->name])) {
				$this->classNameCounts[$node->name]++;
			} else {
				$this->classNameCounts[$node->name] = 1;
			}
			$namespacedNameStr = implode('::', $node->namespacedName->parts);
			if (isset($this->namespacedClassNameCounts[$namespacedNameStr])) {
				$this->namespacedClassNameCounts[$namespacedNameStr]++;
			} else {
				$this->namespacedClassNameCounts[$namespacedNameStr] = 1;
			}
		}
		if ($node instanceof Node\Stmt\Function_) {
			if (isset($this->funcNameCounts[$node->name])) {
				$this->funcNameCounts[$node->name]++;
			} else {
				$this->funcNameCounts[$node->name] = 1;
			}
			$namespacedNameStr = implode('::', $node->namespacedName->parts);
			if (isset($this->namespacedFuncNameCounts[$namespacedNameStr])) {
				$this->namespacedFuncNameCounts[$namespacedNameStr]++;
			} else {
				$this->namespacedFuncNameCounts[$namespacedNameStr] = 1;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->duplicateClassNameCount,
			$this->duplicateNamespacedClassNameCount,
			$this->duplicateFuncNameCount,
			$this->duplicateNamespacedFuncNameCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'duplicateClassNameCount',
			'duplicateNamespacedClassNameCount',
			'duplicateFuncNameCount',
			'duplicateNamespacedFuncNameCount',
		];
	}
}