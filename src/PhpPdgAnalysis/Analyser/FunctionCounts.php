<?php

namespace PhpPdgAnalysis\Analyser;

use PhpPdgAnalysis\Analyser\Visitor\FunctionCountingVisitor;

class FunctionCounts extends AbstractVisitingAnalyser {
	protected function getVisitor() {
		return new FunctionCountingVisitor();
	}
}