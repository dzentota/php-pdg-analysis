<?php

namespace PhpPdgAnalysis\Analysis\SystemDependence;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\NsFuncCall;
use PHPCfg\Op\Expr\StaticCall;
use PHPCfg\Operand;
use PHPCfg\Operand\Literal;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\BuiltinFuncNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;
use PhpPdg\SystemDependence\System;
use PHPTypes\Type;

class ResolvedCallCountsAnalysis implements SystemAnalysisInterface {
	public function analyse(System $system) {
		$funcCallNodes = 0;
		$resolvedFuncCallNodeCount = 0;
		$funcCallEdgeToFuncCount = 0;
		$funcCallEdgeToBuiltinFuncCount = 0;
		$funcCallEdgeToUndefinedFuncCount = 0;
		$funcCallEdgeToRemainingCount = 0;

		$funcCallUnresolvedDueToVariableFuncCall = 0;
		$funcCallUnresolvedDueToOther = 0;

		$methodCallNodes = 0;
		$typedMethodCallNodes = 0;
		$resolvedMethodCallNodeCount = 0;
		$methodCallEdgeToFuncCount = 0;
		$methodCallEdgeToBuiltinFuncCount = 0;
		$methodCallEdgeToUndefinedFuncCount = 0;
		$methodCallEdgeToRemainingCount = 0;

		$methodCallUnresolvedDueToVariableMethodCall = 0;
		$methodCallUnresolvedDueToUntyped = 0;
		$methodCallUnresolvedDueToOther = 0;

		foreach ($system->sdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$op = $node->op;
				$call_edges = $system->sdg->getEdges($node, null, ['type' => 'call']);
				$call_edge_count = count($call_edges);

				if ($op instanceof FuncCall || $op instanceof NsFuncCall) {
					$funcCallNodes++;
					if ($call_edge_count > 0) {
						$resolvedFuncCallNodeCount++;

						foreach ($call_edges as $call_edge) {
							$to_node = $call_edge->getToNode();
							if ($to_node instanceof FuncNode) {
								$funcCallEdgeToFuncCount++;
							} else if ($to_node instanceof BuiltinFuncNode) {
								$funcCallEdgeToBuiltinFuncCount++;
							} else if ($to_node instanceof UndefinedFuncNode) {
								$funcCallEdgeToUndefinedFuncCount++;
							} else {
								$funcCallEdgeToRemainingCount++;
							}
						}
					} else if ($op->name instanceof Literal === false) {
						$funcCallUnresolvedDueToVariableFuncCall++;
					} else {
						$funcCallUnresolvedDueToOther++;
					}

				} else if ($op instanceof MethodCall || $op instanceof StaticCall) {
					$methodCallNodes++;

					/** @var Operand $classOperand */
					$classOperand = $op instanceof MethodCall ? $op->var : $op->class;
					$resolves_to_class = $this->operandResolvesToClass($classOperand);
					if ($resolves_to_class === true) {
						$typedMethodCallNodes++;
					}

					if ($call_edge_count > 0) {
						$resolvedMethodCallNodeCount++;
						foreach ($call_edges as $call_edge) {
							$to_node = $call_edge->getToNode();
							if ($to_node instanceof FuncNode) {
								$methodCallEdgeToFuncCount++;
							} else if ($to_node instanceof BuiltinFuncNode) {
								$methodCallEdgeToBuiltinFuncCount++;
							} else if ($to_node instanceof UndefinedFuncNode) {
								$methodCallEdgeToUndefinedFuncCount++;
							} else {
								$methodCallEdgeToRemainingCount++;
							}
						}
					} else if ($op->name instanceof Literal === false) {
						$methodCallUnresolvedDueToVariableMethodCall++;
					} else if ($resolves_to_class === false) {
						$methodCallUnresolvedDueToUntyped++;
					} else {
						$methodCallUnresolvedDueToOther++;
					}
				}
			}
		}

		return array_combine($this->getSuppliedAnalysisKeys(), [
			$funcCallNodes,
			$resolvedFuncCallNodeCount,
			$funcCallEdgeToFuncCount,
			$funcCallEdgeToBuiltinFuncCount,
			$funcCallEdgeToUndefinedFuncCount,

			$funcCallUnresolvedDueToVariableFuncCall,
			$funcCallUnresolvedDueToOther,

			$methodCallNodes,
			$typedMethodCallNodes,
			$resolvedMethodCallNodeCount,
			$methodCallEdgeToFuncCount,
			$methodCallEdgeToBuiltinFuncCount,
			$methodCallEdgeToUndefinedFuncCount,

			$methodCallUnresolvedDueToVariableMethodCall,
			$methodCallUnresolvedDueToUntyped,
			$methodCallUnresolvedDueToOther,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcCallNodes',
			'resolvedFuncCallNodeCount',
			'funcCallEdgeToFuncCount',
			'funcCallEdgeToBuiltinFuncCount',
			'funcCallEdgeToUndefinedFuncCount',

			'funcCallUnresolvedDueToVariableFuncCall',
			'funcCallUnresolvedDueToOther',

			'methodCallNodes',
			'typedMethodCallNodes',
			'resolvedMethodCallNodeCount',
			'methodCallEdgeToFuncCount',
			'methodCallEdgeToBuiltinFuncCount',
			'methodCallEdgeToUndefinedFuncCount',

			'methodCallUnresolvedDueToVariableMethodCall',
			'methodCallUnresolvedDueToUntyped',
			'methodCallUnresolvedDueToOther',
		];
	}

	private function operandResolvesToClass(Operand $operand) {
		if ($operand->type !== null) {
			$type = $operand->type;
			if ($type === 'string') {
				return $operand instanceof Literal;
			} else {
				assert(is_object($type));
				assert($type instanceof Type);
				/** @var $type Type */
				if ($type->type === Type::TYPE_STRING) {
					return $operand instanceof Literal;
				} else {
					return $this->typeResolvesToClass($operand->type);
				}
			}
		}
		return false;
	}

	private function typeResolvesToClass(Type $type) {
		switch ($type->type) {
			case Type::TYPE_OBJECT:
				return $type->userType !== null;
			case Type::TYPE_UNION:
				foreach ($type->subTypes as $subType) {
					if ($this->typeResolvesToClass($subType) === true) {
						return true;
					}
				}
		}
		return false;
	}
}