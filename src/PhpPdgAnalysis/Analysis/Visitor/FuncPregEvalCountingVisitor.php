<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncPregEvalCountingVisitor extends AbstractAnalysisVisitor {
	private $funcPregEvalCounts;
	public $funcsWithPregEvalCount;
	public $pregEvalCount;

	public function enterLibrary() {
		$this->funcPregEvalCounts = [0];
		$this->funcsWithPregEvalCount = 0;
		$this->pregEvalCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			array_unshift($this->funcPregEvalCounts, 0);
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
							$this->funcPregEvalCounts[0]++;
						}
					}
				}
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcPregEvalCount = array_shift($this->funcPregEvalCounts);
			if ($funcPregEvalCount > 0) {
				$this->funcsWithPregEvalCount++;
				$this->pregEvalCount += $funcPregEvalCount;
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithPregEvalCount,
			$this->pregEvalCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithPregEvalCount",
			"pregEvalCount",
		];
	}
}