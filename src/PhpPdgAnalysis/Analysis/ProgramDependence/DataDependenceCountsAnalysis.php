<?php

namespace PhpPdgAnalysis\Analysis\ProgramDependence;

use PHPCfg\Op;
use PHPCfg\Operand;
use PHPCfg\Operand\Variable;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\GraphInterface;
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

	private $funcUnresolvedOperandsCount;
	private $funcsWithUnresolvedOperandsCount;
	private $unresolvedOperandCount;

	private $unresolvedDueToNotInGraphCount;
	private $predefinedVariables;
	private $undefinedVariables;
	private $dynamicFeatureCount;
	private $otherCount;

	private static $predefined = [
		'GLOBALS',
		'_SERVER',
		'_GET',
		'_POST',
		'_FILES',
		'_REQUEST',
		'_SESSION',
		'_ENV',
		'_COOKIE',
		'php_errormsg',
		'HTTP_RAW_POST_DATA',
		'http_response_header',
		'argc',
		'argv',
	];

	public function enterLibrary($libraryname) {
		$this->opNodeCount = 0;
		$this->operandCount = 0;
		$this->literalOperandCount = 0;
		$this->boundVariableOperandCount = 0;
		$this->variableOperandCount = 0;
		$this->temporaryWithOriginalOperandCount = 0;
		$this->operandWithWriteOpCount = 0;
		$this->resolvedOperandcount = 0;
		$this->dataDependenceEdgeCount = 0;

		$this->funcsWithUnresolvedOperandsCount = 0;
		$this->unresolvedOperandCount = 0;

		$this->unresolvedDueToNotInGraphCount = 0;
		$this->predefinedVariables = 0;
		$this->undefinedVariables = 0;
		$this->dynamicFeatureCount = 0;
		$this->otherCount = 0;
	}

	public function analyse(Func $func) {
		$this->funcUnresolvedOperandsCount = 0;
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
									$this->handleOperand($func->pdg, $arrayOperand, $variableName . ':' . $i, $edgeOperandPaths);
								}
							}
						} else {
							$this->handleOperand($func->pdg, $operand, $variableName, $edgeOperandPaths);
						}
					}
				}
			}
		}
		if ($this->funcUnresolvedOperandsCount > 0) {
			$this->funcsWithUnresolvedOperandsCount++;
			$this->unresolvedOperandCount += $this->funcUnresolvedOperandsCount;
		}
	}

	private function handleOperand(GraphInterface $graph, Operand $operand, $operandPath, $edgeOperandPaths) {
		$this->operandCount++;

		$write_ops = $this->resolveWriteOps($operand);
		if (empty($write_ops) === false) {
			$this->operandWithWriteOpCount++;
			if (isset($edgeOperandPaths[$operandPath]) === true) {
				$this->resolvedOperandcount++;
			} else if (empty($this->filterOpsInGraph($graph, $write_ops)) === true) {
				$this->unresolvedDueToNotInGraphCount++;
			} else {
				$this->funcUnresolvedOperandsCount++;
			}
		} else if ($operand instanceof Operand\Literal) {
			$this->literalOperandCount++;
		} else if ($operand instanceof Operand\BoundVariable) {
			$this->boundVariableOperandCount++;
		} else if ($operand instanceof Operand\Temporary && $operand->original instanceof Variable && empty($operand->original->ops)) {
			if (in_array($operand->original->name->value, self::$predefined, true) === true) {
				$this->predefinedVariables++;
			} else {
				$this->undefinedVariables++;
			}
		} else if ($operand instanceof Variable) {
			$this->dynamicFeatureCount++;
		} else {
			$this->otherCount++;
		}
	}

	private function resolveWriteOps(Operand $operand, $seen_phis = null) {
		$seen_phis = $seen_phis ?? new \SplObjectStorage();
		$result = [];
		foreach ($operand->ops as $op) {
			if ($op instanceof Op\Phi) {
				if ($seen_phis->contains($op) === false) {
					$seen_phis->attach($op);
					foreach ($op->vars as $var) {
						$result = array_merge($result, $this->resolveWriteOps($var, $seen_phis));
					}
				}
			} else {
				$result[] = $op;
			}
		}
		return $result;
	}

	/**
	 * @param GraphInterface $graph
	 * @param Op[] $ops
	 * @return Op[]
	 */
	private function filterOpsInGraph(GraphInterface $graph, $ops) {
		$result = [];
		foreach ($ops as $op) {
			assert($op instanceof Op);
			if ($graph->hasNode(new OpNode($op)) === true) {
				$result[] = $op;
			}
		}
		return $result;
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
			$this->dataDependenceEdgeCount,

			$this->funcsWithUnresolvedOperandsCount,
			$this->unresolvedOperandCount,

			$this->unresolvedDueToNotInGraphCount,
			$this->predefinedVariables,
			$this->undefinedVariables,
			$this->dynamicFeatureCount,
			$this->otherCount,
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
			'dataDependenceEdgeCount',

			'funcsWithUnresolvedOperandsCount',
			'unresolvedOperandCount',

			'unresolvedDueToNotInGraphCount',
			'predefinedVariables',
			'undefinedVariables',
			'dynamicFeatureCount',
			'otherCount',
		];
	}
}