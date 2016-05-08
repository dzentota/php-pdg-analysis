<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class FuncEvalCountingVisitor extends NodeVisitorAbstract implements AnalysisVisitorInterface{
	private $funcEvalCounts;
	public $funcsWithEvalCount;
	public $evalCount;

	public function beforeTraverse(array $nodes) {
		$this->funcEvalCounts = [0];
		$this->funcsWithEvalCount = 0;
		$this->evalCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcEvalCounts, 0);
		}

		if ($node instanceof Node\Expr\Eval_) {
			$this->funcEvalCounts[0]++;
		}

		if ($node instanceof Node\Expr\FuncCall) {
			$name = $node->name;
			if ($name instanceof Node\Name && $name->toString() === 'preg_replace') {
				$pattern_arg_value = $node->args[0]->value;
				if ($pattern_arg_value instanceof Node\Scalar\String_) {
					$delimiter = $pattern_arg_value->value[0];
					$quoted_delimeter = preg_quote($delimiter, '/');
					if (preg_match("/$quoted_delimeter.*$quoted_delimeter(.*)/", $pattern_arg_value->value, $matches) === 1) {
						$modifiers = $matches[1];
						if (strpos($modifiers, 'e') !== false) {
							$this->funcEvalCounts[0]++;
						}
					}
				}
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcEvalCount = array_shift($this->funcEvalCounts);
			if ($funcEvalCount > 0) {
				$this->funcsWithEvalCount++;
				$this->evalCount += $funcEvalCount;
			}
		}
	}

	public function getAnalysisResults() {
		return [
			"funcsWithEvalCount" => $this->funcsWithEvalCount,
			"evalCount" => $this->evalCount,
		];
	}

	public function getSuppliedAnalysisKeys() {
		return ["funcsWithEvalCount", "evalCount"];
	}
}