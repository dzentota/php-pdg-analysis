<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PHPCfg\Op;
use PHPCfg\Op\Expr\Eval_;
use PHPCfg\Operand\Literal;
use PHPCfg\Operand\Variable;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;

class MaybeDataDependenceAnalysis extends AbstractFuncAnalysis {
	private $evalCount;
	private $evalIncomingMaybeDataDependenceCounts;
	private $evalOutgoingMaybeDataDependenceCounts;

	private $varVarOpCount;
	private $varVarOpIncomingMaybeDataDependenceCounts;
	private $varVarOpOutgoingMaybeDataDependenceCounts;

	public function enterLibrary($libraryname) {
		$this->evalCount = 0;
		$this->evalIncomingMaybeDataDependenceCounts = [];
		$this->evalOutgoingMaybeDataDependenceCounts = [];
		$this->varVarOpCount = 0;
		$this->varVarOpIncomingMaybeDataDependenceCounts = [];
		$this->varVarOpOutgoingMaybeDataDependenceCounts = [];
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
				} else if ($this->isVarVarOp($op)) {
					$this->varVarOpCount++;
					$incoming_maybe_data_dependence_count = count($func->pdg->getEdges(null, $node, ['type' => 'maybe data']));
					if (isset($this->evalIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count])) {
						$this->varVarOpIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count]++;
					} else {
						$this->varVarOpIncomingMaybeDataDependenceCounts[$incoming_maybe_data_dependence_count] = 1;
					}
					$outgoing_maybe_data_dependence_count = count($func->pdg->getEdges($node, null, ['type' => 'maybe data']));
					if (isset($this->evalOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count])) {
						$this->varVarOpOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count]++;
					} else {
						$this->varVarOpOutgoingMaybeDataDependenceCounts[$outgoing_maybe_data_dependence_count] = 1;
					}
				}
			}
		}
	}

	private function isVarVarOp(Op $op) {
		foreach ($op->getVariableNames() as $variableName) {
			$operands = is_array($op->$variableName) === true ? $op->$variableName : [$op->$variableName];
			foreach ($operands as $operand) {
				if ($operand instanceof Variable && $operand->name instanceof Literal === false) {
					return true;
				}
			}
		}
		return false;
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->evalCount,
			$this->evalIncomingMaybeDataDependenceCounts,
			$this->evalOutgoingMaybeDataDependenceCounts,
			$this->varVarOpCount,
			$this->varVarOpIncomingMaybeDataDependenceCounts,
			$this->varVarOpOutgoingMaybeDataDependenceCounts,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'evalCount',
			'evalIncomingMaybeDataDependenceCounts',
			'evalOutgoingMaybeDataDependenceCounts',
			'varVarOpCount',
			'varVarOpIncomingMaybeDataDependenceCounts',
			'varVarOpOutgoingMaybeDataDependenceCounts',
		];
	}
}