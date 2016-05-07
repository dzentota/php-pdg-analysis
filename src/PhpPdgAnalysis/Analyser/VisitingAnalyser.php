<?php

namespace PhpPdgAnalysis\Analyser;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpPdgAnalysis\Analyser\Visitor\AnalysingVisitorInterface;

class VisitingAnalyser implements AnalyserInterface {
	private $parser;
	private $visitor;
	private $traverser;

	public function __construct(Parser $parser, AnalysingVisitorInterface $visitor) {
		$this->parser = $parser;
		$this->visitor = $visitor;
		$this->traverser = new NodeTraverser();
		$this->traverser->addVisitor($this->visitor);
	}

	public function analyse(\SplFileInfo $libraryRootFileInfo) {
		$results = [];
		echo "analysing files ";
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($libraryRootFileInfo->getRealPath())), "/.*\.php$/i") as $fileInfo) {
			try {
				$nodes = $this->parser->parse(file_get_contents((string) $fileInfo));
				$this->traverser->traverse($nodes);
				foreach ($this->visitor->getAnalysisResults() as $key => $value) {
					$results[$key] = isset($results[$key]) ? $results[$key] + $value : $value;
				}
				echo ".";
			} catch (\Exception $e) {
				echo "E";
			}
		}
		echo "\n";
		return $results;
	}
	
	public function getSuppliedAnalysisKeys() {
		return $this->visitor->getSuppliedAnalysisKeys();
	}
}