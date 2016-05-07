<?php

namespace PhpPdgAnalysis\Analyser\Visitor;

use PhpParser\NodeVisitor;

interface AnalysingVisitorInterface extends NodeVisitor {
	public function getAnalysisResults();
}