<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PHPCfg\Operand;
use PHPCfg\Operand\Variable;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;

class DataDependenceCountsAnalysis extends AbstractFuncAnalysis {
	private $opNodeCount;
	private $operandCount;
	private $literalOperandCount;
	private $boundVariableOperandCount;
	private $variableOperandCount;
	private $temporaryWithOriginalOperandCount;
	private $operandWithWriteOpCount;
	private $resolvedOperandcount;
	private $dataDependenceEdgeCount;
	private $unresolvedOperandCount;

	public function enterLibrary($libraryname) {
		$this->opNodeCount = 0;
		$this->operandCount = 0;
		$this->literalOperandCount = 0;
		$this->boundVariableOperandCount = 0;
		$this->variableOperandCount = 0;
		$this->temporaryWithOriginalOperandCount = 0;
		$this->operandWithWriteOpCount = 0;
		$this->resolvedOperandcount = 0;
		$this->unresolvedOperandCount = 0;
		$this->dataDependenceEdgeCount = 0;
	}

	public function analyse(Func $func) {
		foreach ($func->pdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				$this->opNodeCount++;

				$edgeOperandPaths = [];
				$edges = $func->pdg->getEdges(null, $node, ['type' => 'data']);
				$this->dataDependenceEdgeCount += count($edges);

				foreach ($edges as $edge) {
					$attributes = $edge->getAttributes();
					$edgeOperandPath = $attributes['operand'];
					if (isset($edgeOperandPaths[$edgeOperandPath]) === false) {
						$edgeOperandPaths[$edgeOperandPath] = 1;
					}
				}

				$op = $node->op;
				foreach ($op->getVariableNames() as $variableName) {
					if ($op->isWriteVariable($variableName) === false) {
						$operand = $op->$variableName;
						if ($operand === null) {
							continue;
						}
						if (is_array($operand)) {
							foreach ($operand as $i => $arrayOperand) {
								if ($arrayOperand !== null) {
									$this->handleOperand($arrayOperand, $variableName . ':' . $i, $edgeOperandPaths);
								}
							}
						} else {
							$this->handleOperand($operand, $variableName, $edgeOperandPaths);
						}
					}
				}
			}
		}
	}

	private function handleOperand(Operand $operand, $operandPath, $edgeOperandPaths) {
		$this->operandCount++;
		if (empty($operand->ops) === false) {
			$this->operandWithWriteOpCount++;
			if (isset($edgeOperandPaths[$operandPath]) === true) {
				$this->resolvedOperandcount++;
			} else {
				$this->unresolvedOperandCount++;
			}
		} else if ($operand instanceof Operand\Literal) {
			$this->literalOperandCount++;
		} else if ($operand instanceof Operand\BoundVariable) {
			$this->boundVariableOperandCount++;
		} else {
			$i = 0;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->opNodeCount,
			$this->operandCount,
			$this->literalOperandCount,
			$this->boundVariableOperandCount,
			$this->variableOperandCount,
			$this->temporaryWithOriginalOperandCount,
			$this->operandWithWriteOpCount,
			$this->resolvedOperandcount,
			$this->unresolvedOperandCount,
			$this->dataDependenceEdgeCount,
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'opNodeCount',
			'operandCount',
			'literalOperandCount',
			'boundVariableOperandCount',
			'variableOperandCount',
			'temporaryWithOriginalOperandCount',
			'operandWithWriteOpCount',
			'resolvedOperandcount',
			'unresolvedOperandCount',
			'dataDependenceEdgeCount',
		];
	}
}