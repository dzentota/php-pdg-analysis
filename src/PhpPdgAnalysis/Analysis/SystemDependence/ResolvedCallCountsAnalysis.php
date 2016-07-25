<?php

namespace PhpPdgAnalysis\Analysis\SystemDependence;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\NsFuncCall;
use PHPCfg\Op\Expr\StaticCall;
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
		$resolvedFuncCallEdgeCounts = [];
		$funcCallEdgeToFuncCount = 0;
		$funcCallEdgeToBuiltinFuncCount = 0;
		$funcCallEdgeToUndefinedFuncCount = 0;

		$methodCallNodes = 0;
		$typedMethodCallNodes = 0;
		$resolvedMethodCallNodeCount = 0;
		$resolvedMethodCallEdgeCounts = [];
		$methodCallEdgeToFuncCount = 0;
		$methodCallEdgeToBuiltinFuncCount = 0;
		$methodCallEdgeToUndefinedFuncCount = 0;


		foreach ($system->sdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$op = $node->op;
				$call_edges = $system->sdg->getEdges($node, null, ['type' => 'call']);
				$call_edge_count = count($call_edges);

				if ($op instanceof FuncCall || $op instanceof NsFuncCall) {
					$funcCallNodes++;
					if ($call_edge_count > 0) {
						$resolvedFuncCallNodeCount++;
						if (isset($resolvedFuncCallEdgeCounts[$call_edge_count])) {
							$resolvedFuncCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedFuncCallEdgeCounts[$call_edge_count] = 1;
						}

						foreach ($call_edges as $call_edge) {
							$to_node = $call_edge->getToNode();
							if ($to_node instanceof FuncNode) {
								$funcCallEdgeToFuncCount++;
							} else if ($to_node instanceof BuiltinFuncNode) {
								$funcCallEdgeToBuiltinFuncCount++;
							} else if ($to_node instanceof UndefinedFuncNode) {
								$funcCallEdgeToUndefinedFuncCount++;
							}
						}
					}
				} else if ($op instanceof MethodCall || $op instanceof StaticCall) {
					$methodCallNodes++;
					if ($op instanceof MethodCall) {
						if (is_object($op->var->type) && $op->var->type instanceof Type && $this->typeResolvesToClass($op->var->type)) {
							$typedMethodCallNodes++;
						}
					} else if ($op instanceof StaticCall) {
						if (is_object($op->class->type) && $op->class->type instanceof Type && $this->typeResolvesToClass($op->class->type)) {
							$typedMethodCallNodes++;
						}
					}
					if ($call_edge_count > 0) {
						$resolvedMethodCallNodeCount++;
						if (isset($resolvedMethodCallEdgeCounts[$call_edge_count])) {
							$resolvedMethodCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedMethodCallEdgeCounts[$call_edge_count] = 1;
						}
						foreach ($call_edges as $call_edge) {
							$to_node = $call_edge->getToNode();
							if ($to_node instanceof FuncNode) {
								$methodCallEdgeToFuncCount++;
							} else if ($to_node instanceof BuiltinFuncNode) {
								$methodCallEdgeToBuiltinFuncCount++;
							} else if ($to_node instanceof UndefinedFuncNode) {
								$methodCallEdgeToUndefinedFuncCount++;
							}
						}
					}
				}
			}
		}

		return array_combine($this->getSuppliedAnalysisKeys(), [
			$funcCallNodes,
			$resolvedFuncCallNodeCount,
			$resolvedFuncCallEdgeCounts,
			$funcCallEdgeToFuncCount,
			$funcCallEdgeToBuiltinFuncCount,
			$funcCallEdgeToUndefinedFuncCount,

			$methodCallNodes,
			$typedMethodCallNodes,
			$resolvedMethodCallNodeCount,
			$resolvedMethodCallEdgeCounts,
			$methodCallEdgeToFuncCount,
			$methodCallEdgeToBuiltinFuncCount,
			$methodCallEdgeToUndefinedFuncCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'funcCallNodes',
			'resolvedFuncCallNodeCount',
			'resolvedFuncCallEdgeCounts',
			'funcCallEdgeToFuncCount',
			'funcCallEdgeToBuiltinFuncCount',
			'funcCallEdgeToUndefinedFuncCount',

			'methodCallNodes',
			'typedMethodCallNodes',
			'resolvedMethodCallNodeCount',
			'resolvedMethodCallEdgeCounts',
			'methodCallEdgeToFuncCount',
			'methodCallEdgeToBuiltinFuncCount',
			'methodCallEdgeToUndefinedFuncCount',
		];
	}

	private function typeResolvesToClass(Type $type) {
		switch ($type->type) {
			case Type::TYPE_STRING:
				return true;
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