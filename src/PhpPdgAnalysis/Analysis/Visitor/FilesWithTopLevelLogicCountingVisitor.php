<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;

class FilesWithTopLevelLogicCountingVisitor extends AbstractAnalysisVisitor {
	private $filesWithTopLevelLogicCount;

	public function enterLibrary($libraryname) {
		$this->filesWithTopLevelLogicCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		foreach ($nodes as $node) {
			if (
				$node instanceof Class_
				|| $node instanceof Trait_
				|| $node instanceof Interface_
				|| $node instanceof Function_
				|| $node instanceof Use_
				|| $node instanceof Namespace_
				|| $node instanceof Include_
			) {
				continue;
			}
			$this->filesWithTopLevelLogicCount++;
			break;
		}
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			 $this->filesWithTopLevelLogicCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'filesWithTopLevelLogicCount'
		];
	}
}