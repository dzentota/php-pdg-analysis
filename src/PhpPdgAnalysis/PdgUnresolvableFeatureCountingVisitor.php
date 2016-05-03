<?php

namespace PhpPdgAnalysis;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class PdgUnresolvableFeatureCountingVisitor extends NodeVisitorAbstract {
	public $funcCount;

	private $funcVarVarCounts;
	public $funcsWithVarVarCount;
	public $varVarCount;

	private $funcEvalCounts;
	public $funcsWithEvalCount;
	public $evalCount;

	private $funcPregEvalCounts;
	public $funcsWithPregEvalCount;
	public $pregEvalCount;

	private $funcRefArrayItemCounts;
	public $funcsWithRefArrayItemCount;
	public $refArrayItemCount;

	private $funcGlobalCounts;
	public $funcsWithGlobalCount;
	public $globalCount;

	private $funcIncludeCounts;
	public $funcsWithIncludeCount;
	public $includeCount;

	public $funcsWithAnyCount;

	public function beforeTraverse(array $nodes) {
		$this->funcCount = 0;

		$this->funcVarVarCounts = [0];
		$this->funcsWithVarVarCount = 0;
		$this->varVarCount = 0;

		$this->funcEvalCounts = [0];
		$this->funcsWithEvalCount = 0;
		$this->evalCount = 0;

		$this->funcPregEvalCounts = [0];
		$this->funcsWithPregEvalCount = 0;
		$this->pregEvalCount = 0;

		$this->funcRefArrayItemCounts = [0];
		$this->funcsWithRefArrayItemCount = 0;
		$this->refArrayItemCount = 0;

		$this->funcGlobalCounts = [0];
		$this->funcsWithGlobalCount = 0;
		$this->globalCount = 0;

		$this->funcIncludeCounts = [0];
		$this->funcsWithIncludeCount = 0;
		$this->includeCount = 0;

		$this->funcsWithAnyCount = 0;
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$this->funcCount++;
			array_unshift($this->funcVarVarCounts, 0);
			array_unshift($this->funcEvalCounts, 0);
			array_unshift($this->funcPregEvalCounts, 0);
			array_unshift($this->funcRefArrayItemCounts, 0);
			array_unshift($this->funcGlobalCounts, 0);
			array_unshift($this->funcIncludeCounts, 0);
		}

		if ($node instanceof Node\Expr\Variable && $node->name instanceof Node\Expr\Variable) {
			$this->funcVarVarCounts[0]++;
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

		if ($node instanceof Node\Expr\ArrayItem && $node->byRef === true) {
			$this->funcRefArrayItemCounts[0]++;
		}

		if ($node instanceof Node\Stmt\Global_) {
			$this->funcGlobalCounts[0]++;
		}
		if ($node instanceof Node\Expr\Variable) {
			$name = $node->name;
			if ($name instanceof Node\Name && $name->toString() === 'GLOBALS') {
				$this->funcGlobalCounts[0]++;
			}
		}
		if ($node instanceof Node\Expr\Include_) {
			$this->funcIncludeCounts[0]++;
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike) {
			$funcVarVarCount = array_shift($this->funcVarVarCounts);
			if ($funcVarVarCount > 0) {
				$this->varVarCount += $funcVarVarCount;
				$this->funcsWithVarVarCount++;
			}

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

			$funcRefArrayItemCount = array_shift($this->funcRefArrayItemCounts);
			if ($funcRefArrayItemCount > 0) {
				$this->funcsWithRefArrayItemCount++;
				$this->refArrayItemCount += $funcRefArrayItemCount;
			}

			$funcGlobalCount = array_shift($this->funcGlobalCounts);
			if ($funcGlobalCount > 0) {
				$this->funcsWithGlobalCount++;
				$this->globalCount += $funcGlobalCount;
			}

			$funcIncludeCount = array_shift($this->funcIncludeCounts);
			if ($funcIncludeCount > 0) {
				$this->funcsWithIncludeCount++;
				$this->includeCount += $funcIncludeCount;
			}

			if ($funcVarVarCount > 0 || $funcEvalCount > 0 || $funcPregEvalCount > 0 || $funcRefArrayItemCount > 0 || $funcGlobalCount > 0 || $funcIncludeCount > 0) {
				$this->funcsWithAnyCount++;
			}
		}
	}
}