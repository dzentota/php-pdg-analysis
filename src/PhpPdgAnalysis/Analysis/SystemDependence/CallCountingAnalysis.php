<?php

namespace PhpPdgAnalysis\Analysis\SystemDependence;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\NsFuncCall;
use PHPCfg\Op\Expr\StaticCall;
use PhpPdg\SystemDependence\Node\CallNode;
use PhpPdg\SystemDependence\System;

class CallCountingAnalysis implements SystemAnalysisInterface {
	public function analyse(System $system) {
		$callNodeCount = 0;
		$resolvedCallNodeCount = 0;
		$resolvedCallCount = 0;
		$resolvedFuncCallNodeCount = 0;
		$resolvedFuncCallCount = 0;
		$resolvedNsFuncCallNodeCount = 0;
		$resolvedNsFuncCallCount = 0;
		$resolvedMethodCallNodeCount = 0;
		$resolvedMethodCallCount = 0;
		$resolvedStaticCallNodeCount = 0;
		$resolvedStaticCallCount = 0;


		foreach ($system->sdg->getNodes() as $call_node) {
			if ($call_node instanceof CallNode) {
				$callNodeCount += 1;
				$call_edges = $system->sdg->getEdges($call_node);
				$call_edge_count = count($call_edges);
				$callOp = $call_node->getCallOp();
				if ($call_edge_count > 0) {
					$resolvedCallNodeCount += 1;
					if ($callOp instanceof FuncCall) {
						$resolvedFuncCallNodeCount += 1;
					} else if ($callOp instanceof NsFuncCall) {
						$resolvedNsFuncCallNodeCount += 1;
					} else if ($callOp instanceof MethodCall) {
						$resolvedMethodCallNodeCount += 1;
					} else if ($callOp instanceof StaticCall) {
						$resolvedStaticCallNodeCount += 1;
					} else {
						throw new \LogicException('Unknown call op `' . get_class($callOp) . '`');
					}
				}
				$resolvedCallCount += $call_edge_count;
				if ($callOp instanceof FuncCall) {
					$resolvedFuncCallCount += $call_edge_count;
				} else if ($callOp instanceof NsFuncCall) {
					$resolvedNsFuncCallCount += $call_edge_count;
				} else if ($callOp instanceof MethodCall) {
					$resolvedMethodCallCount += $call_edge_count;
				} else if ($callOp instanceof StaticCall) {
					$resolvedStaticCallCount += $call_edge_count;
				} else {
					throw new \LogicException('Unknown call op `' . get_class($callOp) . '`');
				}
			}
		}

		return array_combine($this->getSuppliedAnalysisKeys(), [
			$callNodeCount,
			$resolvedCallNodeCount,
			$resolvedCallCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'callNodeCount',
			'resolvedCallNodeCount',
			'resolvedCallCount',
		];
	}
}