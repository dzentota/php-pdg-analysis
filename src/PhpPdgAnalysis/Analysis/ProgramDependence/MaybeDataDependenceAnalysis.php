<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PHPCfg\Op\Expr\Eval_;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;

class MaybeDataDependenceAnalysis extends AbstractFuncAnalysis {
	private $evalCount;
	private $evalIncomingMaybeDataDependenceCounts;
	private $evalOutgoingMaybeDataDependenceCounts;

	private $varVarCount;

	public function enterLibrary($libraryname) {
		$this->evalCount = 0;
		$this->evalIncomingMaybeDataDependenceCounts = [];
		$this->evalOutgoingMaybeDataDependenceCounts = [];
		$this->varVarCount = 0;
	}

	public function analyse(Func $func) {
		foreach ($func->pdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$op = $node->op;
				if ($op instanceof Eval_) {
					$this->evalCount++;
					$incoming_maybe_data_dependence_count = count($func->pdg->getEdges(null, $node, ['type' => 'maybe data']));
					if (isset($this->evalIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count])) {
						$this->evalIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count]++;
					} else {
						$this->evalIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count] = 1;
					}
					$outgoing_maybe_data_dependence_count = count($func->pdg->getEdges($node, null, ['type' => 'maybe data']));
					if (isset($this->evalOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count])) {
						$this->evalOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count]++;
					} else {
						$this->evalOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count] = 1;
					}
				}
			}
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->evalCount,
			$this->evalIncomingMaybeDataDependenceCounts,
			$this->evalOutgoingMaybeDataDependenceCounts,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'evalCount',
			'evalIncomingMaybeDataDependenceCounts',
			'evalOutgoingMaybeDataDependenceCounts',
		];
	}
}