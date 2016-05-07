<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpPdgAnalysis\Analyser\AnalyserInterface;
use PhpPdgAnalysis\Analyser\LibraryInfo;
use PhpPdgAnalysis\Analyser\VisitingAnalyser;
use PhpPdgAnalysis\Analyser\Visitor\FuncAssignRefCountingVisitor;
use PhpPdgAnalysis\Analyser\Visitor\FuncEvalCountingVisitor;
use PhpPdgAnalysis\Analyser\Visitor\FuncGlobalCountingVisitor;
use PhpPdgAnalysis\Analyser\Visitor\FuncIncludeCountingVisitor;
use PhpPdgAnalysis\Analyser\Visitor\FunctionCountingVisitor;
use PhpPdgAnalysis\Analyser\Visitor\FuncVarVarCountingVisitor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command {
	const LIBRARY_ROOT = 'C:\Users\mwijngaard\Documents\Projects\_verification';

	private $cacheFile;
	/** @var AnalyserInterface[] */
	private $analysers;

	public function __construct($cacheFile) {
		$this->cacheFile = $cacheFile;
		$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->analysers = [
			"libraryInfo" => new LibraryInfo(),
			"functionCount" => new VisitingAnalyser($parser, new FunctionCountingVisitor()),
			"funcAssignRefCount" => new VisitingAnalyser($parser, new FuncAssignRefCountingVisitor()),
			"funcEvalCount" => new VisitingAnalyser($parser, new FuncEvalCountingVisitor()),
			"funcGlobalCount" => new VisitingAnalyser($parser, new FuncGlobalCountingVisitor()),
			"funcIncludeCount" => new VisitingAnalyser($parser, new FuncIncludeCountingVisitor()),
			"funcVarVarCount" => new VisitingAnalyser($parser, new FuncVarVarCountingVisitor()),
		];
		parent::__construct("update");
	}

	protected function configure() {
		$this
			->setDescription("Update analysis cache")
			->addOption(
				"analyser",
				"a",
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				"Which analysers should be run (default all)",
				array_keys($this->analysers)
			)
			->addOption(
				"force",
				"f",
				InputOption::VALUE_NONE,
				"Whether or not already cached results should be overwritten"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$analyserNames = $input->getOption("analyser");
		foreach ($analyserNames as $analyserName) {
			if (!isset($this->analysers[$analyserName])) {
				throw new \RuntimeException("No such analyser `$analyserName`");
			}
		}
		try {
			$cache = json_decode(file_get_contents($this->cacheFile), true);
		} catch (\Exception $e) {
			$cache = [];
		}

		foreach (new \DirectoryIterator(self::LIBRARY_ROOT) as $fileInfo) {
			if ($fileInfo->isDir() && !$fileInfo->isDot()) {
				$filename = $fileInfo->getFilename();
				echo "analysing $filename\n";
				$fullPath = (string) $fileInfo;
				$pathCache = isset($cache[$fullPath]) ? $cache[$fullPath] : [];

				foreach ($analyserNames as $analyserName) {
					echo "analyser $analyserName: ";
					$analyser = $this->analysers[$analyserName];
					$suppliedKeys = $analyser->getSuppliedAnalysisKeys();
					if ($input->getOption('force') || !$this->areAllKeysSet($suppliedKeys, $pathCache)) {
						echo "applying\n";
						$analysisResults = $analyser->analyse($fileInfo);
						foreach ($analysisResults as $key => $value) {
							$pathCache[$key] = $value;
						}
						$cache[$fullPath] = $pathCache;
						file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
						echo "analyser $analyserName done\n";
					} else {
						echo "in cache\n";
					}
				}
				echo "$filename done " . json_encode($pathCache, JSON_PRETTY_PRINT) . "\n";
			}
		}
	}

	private function areAllKeysSet($keys, $arr) {
		foreach ($keys as $key) {
			if (!isset($arr[$key])) {
				return false;
			}
		}
		return true;
	}
}