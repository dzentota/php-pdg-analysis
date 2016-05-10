<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpPdgAnalysis\Analysis\DirectoryAnalysisInterface;
use PhpPdgAnalysis\Analysis\Visitor\AnalysisVisitorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalysisRunCommand extends Command {
	/** @var string  */
	private $libraryRoot;
	/** @var  string */
	private $cacheFile;
	/** @var DirectoryAnalysisInterface[] */
	private $directoryAnalyses;
	/** @var AnalysisVisitorInterface[] */
	private $analysisVisitors;
	/** @var Parser  */
	private $parser;

	/**
	 * AnalysisRunCommand constructor.
	 * @param string $libraryRoot
	 * @param string $cacheFile
	 * @param array $directoryAnalyses
	 * @param array $analysisVisitors
	 */
	public function __construct($libraryRoot, $cacheFile, array $directoryAnalyses = [], array $analysisVisitors = []) {
		$this->libraryRoot = $libraryRoot;
		$this->cacheFile = $cacheFile;
		$this->directoryAnalyses = $directoryAnalyses;
		$this->analysisVisitors = $analysisVisitors;
		$this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		parent::__construct("analysis:run");
	}

	protected function configure() {
		$this
			->setDescription("Update analysis cache")
			->addOption(
				"analysis",
				"a",
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				"Which analysers should be run (default all)",
				array_merge(array_keys($this->directoryAnalyses), array_keys($this->analysisVisitors))
			)
			->addOption(
				"force",
				"f",
				InputOption::VALUE_NONE,
				"Whether or not already cached results should be overwritten"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$analysisNames = $input->getOption("analysis");
		foreach ($analysisNames as $analysisName) {
			if (!isset($this->directoryAnalyses[$analysisName]) && !isset($this->analysisVisitors[$analysisName])) {
				throw new \RuntimeException("No such analysis `$analysisName`");
			}
		}
		try {
			$cache = json_decode(file_get_contents($this->cacheFile), true);
		} catch (\Exception $e) {
			$cache = [];
		}

		foreach (new \DirectoryIterator($this->libraryRoot) as $libraryRootFileInfo) {
			if ($libraryRootFileInfo->isDir() && !$libraryRootFileInfo->isDot()) {
				$filename = $libraryRootFileInfo->getFilename();
				echo "analysing $filename\n";
				$fullPath = (string) $libraryRootFileInfo;
				$pathCache = isset($cache[$fullPath]) ? $cache[$fullPath] : [];

				$force = $input->getOption('force');
				$directoryAnalysesToRun = [];
				$analysisVisitorsToRun = [];
				foreach ($analysisNames as $analysisName) {
					if (isset($this->directoryAnalyses[$analysisName])) {
						$directoryAnalysis = $this->directoryAnalyses[$analysisName];
						if ($force || !$this->areAllKeysSet($directoryAnalysis->getSuppliedAnalysisKeys(), $pathCache)) {
							$status = 'running';
							$directoryAnalysesToRun[$analysisName] = $directoryAnalysis;
						} else {
							$status = 'in cache';
						}
						echo "director analysis `$analysisName`: $status\n";
					}
					if (isset($this->analysisVisitors[$analysisName])) {
						$analysisVisitor = $this->analysisVisitors[$analysisName];
						if ($force || !$this->areAllKeysSet($analysisVisitor->getSuppliedAnalysisKeys(), $pathCache)) {
							$status = 'running';
							$analysisVisitorsToRun[$analysisName] = $analysisVisitor;
						} else {
							$status = 'in cache';
						}
						echo "analysis visitor `$analysisName`: $status\n";
					}
				}

				if (count($directoryAnalysesToRun) > 0) {
					echo "running directory analyses\n";
					foreach ($directoryAnalysesToRun as $directoryAnalysis) {
						$analysisResults = $directoryAnalysis->analyse($libraryRootFileInfo);
						foreach ($analysisResults as $key => $value) {
							$pathCache[$key] = $value;
						}
					}
					echo "directory analyses done\n";
				} else {
					echo "no directory analyses to run\n";
				}
				if (count($analysisVisitorsToRun) > 0) {
					echo "running analysis visitors\n";
					$traverser = new NodeTraverser();
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$traverser->addVisitor($analysisVisitor);
					}
					echo "analysing files ";
					$cumulativeVisitorAnalysisResults = [];
					foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($libraryRootFileInfo->getRealPath())), "/.*\.php$/i") as $libraryFileInfo) {
						try {
							$nodes = $this->parser->parse(file_get_contents((string) $libraryFileInfo));
							$traverser->traverse($nodes);
							foreach ($analysisVisitorsToRun as $i => $analysisVisitor) {
								foreach ($analysisVisitor->getAnalysisResults() as $key => $value) {
									$cumulativeVisitorAnalysisResults[$i][$key] = isset($cumulativeVisitorAnalysisResults[$i][$key]) ? $cumulativeVisitorAnalysisResults[$i][$key] + $value : $value;
								}
							}
							echo ".";
						} catch (\Exception $e) {
							echo "E";
						}
					}
					foreach ($cumulativeVisitorAnalysisResults as $analysisName => $cumulativeVisitorAnalysisResult) {
						$pathCache = array_merge($pathCache, $cumulativeVisitorAnalysisResult);
					}
					echo "\n";
					echo "analysis visitors done\n";
				} else {
					echo "no analysis visitors to run\n";
				}

				$cache[$fullPath] = $pathCache;
				file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
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