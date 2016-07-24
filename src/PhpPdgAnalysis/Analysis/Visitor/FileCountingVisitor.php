<?php

namespace PhpPdgAnalysis\Analysis\Visitor;

use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;

class FileCountingVisitor extends AbstractAnalysisVisitor {
	private $fileCount;

	public function enterLibrary($libraryname) {
		$this->fileCount = 0;
	}

	public function beforeTraverse(array $nodes) {
		$this->fileCount++;
	}

	public function getAnalysisResults() {
		return array_combine($this->getSuppliedAnalysisKeys(), [
			 $this->fileCount
		]);
	}

	public function getSuppliedAnalysisKeys() {
		return [
			'fileCount'
		];
	}
}