<?php

namespace PhpPdgAnalysis\Analysis\SystemDependence;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\NsFuncCall;
use PHPCfg\Op\Expr\StaticCall;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\BuiltinFuncNode;
use PhpPdg\SystemDependence\Node\CallNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;
use PhpPdg\SystemDependence\Node\UndefinedNsFuncNode;
use PhpPdg\SystemDependence\System;

class ResolvedCallCountsAnalysis implements SystemAnalysisInterface {
	public function analyse(System $system) {
		$resolvedCallNodeCount = 0;
		$resolvedFuncCallNodeCount = 0;
		$resolvedFuncCallEdgeCounts = [];
		$resolvedNsFuncCallNodeCount = 0;
		$resolvedNsFuncCallEdgeCounts = [];
		$resolvedMethodCallNodeCount = 0;
		$resolvedMethodCallEdgeCounts = [];
		$resolvedStaticCallNodeCount = 0;
		$resolvedStaticCallEdgeCounts = [];

		$callEdgeToFuncCount = 0;
		$callEdgeToBuiltinFuncCount = 0;
		$callEdgeToUndefinedFuncCount = 0;
		$callEdgeToUndefinedNsFuncCount = 0;

		foreach ($system->sdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$op = $node->op;
				$call_edges = $system->sdg->getEdges($node, null, ['type' => 'call']);
				$call_edge_count = count($call_edges);

				if ($call_edge_count > 0) {
					$resolvedCallNodeCount++;
					if ($op instanceof FuncCall) {
						$resolvedFuncCallNodeCount++;
						if (isset($resolvedFuncCallEdgeCounts[$call_edge_count])) {
							$resolvedFuncCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedFuncCallEdgeCounts[$call_edge_count] = 1;
						}
					} else if ($op instanceof NsFuncCall) {
						$resolvedNsFuncCallNodeCount++;
						if (isset($resolvedNsFuncCallEdgeCounts[$call_edge_count])) {
							$resolvedNsFuncCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedNsFuncCallEdgeCounts[$call_edge_count] = 1;
						}
					} else if ($op instanceof MethodCall) {
						$resolvedMethodCallNodeCount++;
						if (isset($resolvedMethodCallEdgeCounts[$call_edge_count])) {
							$resolvedMethodCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedMethodCallEdgeCounts[$call_edge_count] = 1;
						}
					} else if ($op instanceof StaticCall) {
						$resolvedStaticCallNodeCount++;
						if (isset($resolvedStaticCallEdgeCounts[$call_edge_count])) {
							$resolvedStaticCallEdgeCounts[$call_edge_count]++;
						} else {
							$resolvedStaticCallEdgeCounts[$call_edge_count] = 1;
						}
					} else {
						throw new \LogicException('Unknown call op `' . $op->getType() . '`');
					}

					foreach ($call_edges as $call_edge) {
						$to_node = $call_edge->getToNode();
						if ($to_node instanceof FuncNode) {
							$callEdgeToFuncCount++;
						} else if ($to_node instanceof BuiltinFuncNode) {
							$callEdgeToBuiltinFuncCount++;
						} else if ($to_node instanceof UndefinedFuncNode) {
							$callEdgeToUndefinedFuncCount++;
						} else if ($to_node instanceof UndefinedNsFuncNode) {
							$callEdgeToUndefinedNsFuncCount++;
						}
					}
				}
			}
		}

		return array_combine($this->getSuppliedAnalysisKeys(), [
			$resolvedCallNodeCount,
			$resolvedFuncCallNodeCount,
			$resolvedFuncCallEdgeCounts,
			$resolvedNsFuncCallNodeCount,
			$resolvedNsFuncCallEdgeCounts,
			$resolvedMethodCallNodeCount,
			$resolvedMethodCallEdgeCounts,
			$resolvedStaticCallNodeCount,
			$resolvedStaticCallEdgeCounts,
			$callEdgeToFuncCount,
			$callEdgeToBuiltinFuncCount,
			$callEdgeToUndefinedFuncCount,
			$callEdgeToUndefinedNsFuncCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'resolvedCallNodeCount',
			'resolvedFuncCallNodeCount',
			'resolvedFuncCallEdgeCounts',
			'resolvedNsFuncCallNodeCount',
			'resolvedNsFuncCallEdgeCounts',
			'resolvedMethodCallNodeCount',
			'resolvedMethodCallEdgeCounts',
			'resolvedStaticCallNodeCount',
			'resolvedStaticCallEdgeCounts',
			'callEdgeToFuncCount',
			'callEdgeToBuiltinFuncCount',
			'callEdgeToUndefinedFuncCount',
			'callEdgeToUndefinedNsFuncCount',
		];
	}
}