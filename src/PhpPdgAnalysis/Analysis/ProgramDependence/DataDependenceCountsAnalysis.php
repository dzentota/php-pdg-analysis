<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PHPCfg\Operand;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;

class DataDependenceCountsAnalysis extends AbstractFuncAnalysis {
	private $op_count;
	private $operand_count;
	private $data_dependence_count;
	private $maybe_data_dependence_count;
	private $operand_data_dependence_pairs = [];

	public function enterLibrary($libraryname) {
		$this->op_count = 0;
		$this->operand_count = 0;
		$this->data_dependence_count = 0;
		$this->maybe_data_dependence_count = 0;
	}

	public function analyse(Func $func) {
		foreach ($func->pdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$this->op_count++;
				$op = $node->op;
				$unique_read_operands = new \SplObjectStorage();
				foreach ($op->getVariableNames() as $var) {
					if ($op->isWriteVariable($var) === false) {
						$operands = $op->$var;
						if ($operands === null) {
							continue;
						}
						if (!is_array($operands)) {
							$operands = [$operands];
						}
						foreach ($operands as $operand) {
							if ($operand instanceof Operand) {
								$unique_read_operands->attach($operand);
							}
						}
					}
				}
				$op_operand_count = count($unique_read_operands);
				$op_data_dependences = $func->pdg->getEdges(null, $node, ['type' => 'data']);
				$op_data_dependence_count = count($op_data_dependences);
				if ($op_operand_count == 0 && $op_data_dependence_count !== 0) {
					echo $node->toString() . "\n";
					foreach ($op_data_dependences as $edge) {
						echo '  -> ' . $edge->getFromNode()->toString() . "\n";
					}
					exit();
				}
				if (isset($this->operand_data_dependence_pairs[$op_operand_count][$op_data_dependence_count])) {
					$this->operand_data_dependence_pairs[$op_operand_count][$op_data_dependence_count]++;
				} else {
					$this->operand_data_dependence_pairs[$op_operand_count][$op_data_dependence_count] = 1;
				}
			}
		}
		$this->data_dependence_count += count($func->pdg->getEdges(null, null, ['type' => 'data']));
		$this->maybe_data_dependence_count += count($func->pdg->getEdges(null, null, ['type' => 'maybe data']));
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->op_count,
			$this->operand_count,
			$this->data_dependence_count,
			$this->maybe_data_dependence_count,
			$this->operand_data_dependence_pairs
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'opCount',
			'operandCount',
			'dataDependenceCount',
			'maybeDataDependenceCount',
			'operandDataDependencePairs',
		];
	}
}