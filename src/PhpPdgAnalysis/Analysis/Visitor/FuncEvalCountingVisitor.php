<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FuncEvalCountingVisitor extends AbstractAnalysisVisitor {
	private $funcEvalCounts;
	public $funcsWithEvalCount;
	public $evalCount;

	private $funcPregEvalCounts;
	public $funcsWithPregEvalCount;
	public $pregEvalCount;

	public function enterLibrary($libraryname) {
		$this->funcEvalCounts = [];
		$this->funcsWithEvalCount = 0;
		$this->evalCount = 0;

		$this->funcPregEvalCounts = [];
		$this->funcsWithPregEvalCount = 0;
		$this->pregEvalCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->pushFunc();
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->pushFunc();
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
							$this->funcPregEvalCounts[0]++;
						}
					}
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
		array_unshift($this->funcEvalCounts, 0);
		array_unshift($this->funcPregEvalCounts, 0);
	}

	public function popFunc() {
		$funcEvalCount = array_shift($this->funcEvalCounts);
		if ($funcEvalCount > 0) {
			$this->funcsWithEvalCount++;
			$this->evalCount += $funcEvalCount;
		}

		$funcPregEvalCount = array_shift($this->funcPregEvalCounts);
		if ($funcPregEvalCount > 0) {
			$this->funcsWithPregEvalCount++;
			$this->pregEvalCount += $funcPregEvalCount;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->funcsWithEvalCount,
			$this->evalCount,
			$this->funcsWithPregEvalCount,
			$this->pregEvalCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			"funcsWithEvalCount",
			"evalCount",
			"funcsWithPregEvalCount",
			"pregEvalCount",
		];
	}
}