<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class CreateFunctionCountingVisitor extends AbstractAnalysisVisitor {
	private $createFunctionCount;

	public function enterLibrary($libraryname) {
		$this->createFunctionCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\Expr\FuncCall) {
			$name = $node->name;
			if ($name instanceof Node\Name && $name->toString() === 'create_function') {
				$this->createFunctionCount++;
			}
		}
	}


	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->createFunctionCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'createFunctionCount',
		];
	}
}