<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node;

class FilesWithTopLevelVariablesCountingVisitor extends AbstractAnalysisVisitor {
	private $variablesFound;
	private $scopeCount;
	private $filesWithTopLevelVariablesCount;

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
		$this->variablesFound = $this->filesWithTopLevelVariablesCount = 0;
	}

	public function beforeTraverse(array $nodes){
		$this->variablesFound = false;
		$this->scopeCount = 0;
	}

	public function afterTraverse(array $nodes) {
		if ($this->variablesFound === true) {
			$this->filesWithTopLevelVariablesCount++;
		}
	}

	public function enterNode(Node $node) {
		if ($node instanceof Node\FunctionLike || $node instanceof Node\Stmt\ClassLike) {
			$this->scopeCount++;
		} else if (
			$node instanceof Node\Expr\Variable
			&& $this->scopeCount === 0
		) {
			assert(is_string($node->name) === true || $node->name instanceof Node\Expr);
			if (is_string($node->name) && in_array($node->name, self::$predefined, true) === false) {
				$this->variablesFound = true;
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($node instanceof Node\FunctionLike || $node instanceof Node\Stmt\ClassLike) {
			$this->scopeCount--;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			$this->filesWithTopLevelVariablesCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'filesWithTopLevelVariablesCount',
		];
	}
}