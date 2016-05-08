<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FunctionCountingVisitor extends NodeVisitorAbstract implements AnalysisVisitorInterface {
	private $funcCount;

	public function beforeTraverse(array $nodes) {
		$this->funcCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->funcCount++;
		}
	}

	public function getAnalysisResults() {
		return [
			"funcCount" => $this->funcCount
		];
	}

	public function getSuppliedAnalysisKeys() {
		return ["funcCount"];
	}
}