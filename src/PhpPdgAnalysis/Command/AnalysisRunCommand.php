<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpPdg\CfgBridge\System;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdgAnalysis\Analysis\DirectoryAnalysisInterface;
use PhpPdgAnalysis\Analysis\SystemDependence\SystemAnalysisInterface;
use PhpPdgAnalysis\Analysis\Visitor\AnalysisVisitorInterface;
use PhpPdg\AstBridge\Parser\FileCachingParser as AstFileCachingParser;
use PhpPdg\AstBridge\Parser\MemoryCachingParser as AstMemoryCachingParser;
use PhpPdg\AstBridge\Parser\WrappedParser as AstWrappedParser;
use PhpPdg\CfgBridge\Parser\FileCachingParser as CfgFileCachingParser;
use PhpPdg\CfgBridge\Parser\WrappedParser as CfgWrappedParser;
use PhpPdgAnalysis\ProgramDependence\DebugFactory as PdgDebugFactory;
use PhpPdgAnalysis\SystemDependence\DebugFactory as SdgDebugFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhpPdg\Graph\Factory as GraphFactory;
use PhpPdg\ProgramDependence\ControlDependence\BlockFlowGraph\Generator as BlockCfgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\BlockDependenceGraph\Generator as BlockCdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\PostDominatorTree\Generator as PdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\Generator as ControlDependenceGenerator;
use PhpPdg\ProgramDependence\DataDependence\Generator as DataDependenceGenerator;

class AnalysisRunCommand extends Command {
	/** @var string  */
	private $libraryRoot;
	/** @var  string */
	private $cacheFile;
	/** @var  string */
	private $cacheDir;
	/** @var DirectoryAnalysisInterface[] */
	private $directoryAnalyses;
	/** @var NameResolver  */
	private $nameResolvingVisitor;
	/** @var AnalysisVisitorInterface[] */
	private $analysisVisitors;
	/** @var SystemAnalysisInterface[] */
	private $systemAnalyses;
	/** @var AstMemoryCachingParser */
	private $ast_memory_caching_parser;
	/** @var  SdgFactory */
	private $sdg_factory;

	/**
	 * AnalysisRunCommand constructor.
	 * @param string $libraryRoot
	 * @param string $cacheFile
	 * @param string $cacheDir
	 * @param DirectoryAnalysisInterface[] $directoryAnalyses
	 * @param AnalysisVisitorInterface[] $analysisVisitors
	 * @param SystemAnalysisInterface[] $systemAnalyses
	 */
	public function __construct($libraryRoot, $cacheFile, $cacheDir, array $directoryAnalyses = [], array $analysisVisitors = [], array $systemAnalyses = []) {
		$this->libraryRoot = $libraryRoot;
		$this->cacheFile = $cacheFile;
		$this->cacheDir = $cacheDir;
		$this->directoryAnalyses = $directoryAnalyses;
		$this->nameResolvingVisitor = new NameResolver();
		$this->analysisVisitors = $analysisVisitors;
		$this->systemAnalyses = $systemAnalyses;

		$ast_string_parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->ast_memory_caching_parser = new AstMemoryCachingParser(new AstFileCachingParser($cacheDir . '/ast', new AstWrappedParser($ast_string_parser), true));

		$graph_factory = new GraphFactory();
		$cfg_file_caching_parser = new CfgFileCachingParser($cacheDir . '/cfg', new CfgWrappedParser($this->ast_memory_caching_parser), true);
		$block_cfg_generator = new BlockCfgGenerator($graph_factory);
		$block_cdg_generator = new BlockCdgGenerator($graph_factory);
		$pdt_generator = new PdgGenerator($graph_factory);
		$control_dependence_generator = new ControlDependenceGenerator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new DataDependenceGenerator();
		$pdg_factory = new PdgFactory($graph_factory, $control_dependence_generator, $data_dependence_generator);
		$this->sdg_factory = new SdgDebugFactory(new SdgFactory($graph_factory, $cfg_file_caching_parser, new PdgDebugFactory($pdg_factory)));

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
				array_merge(array_keys($this->directoryAnalyses), array_keys($this->analysisVisitors), array_keys($this->systemAnalyses))
			)
			->addOption(
				"library-filter",
				"l",
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				"Filter which libraries should be analysed (matched case insensitively)",
				array()
			)
			->addOption(
				"force",
				"f",
				InputOption::VALUE_NONE,
				"Whether or not already cached results should be overwritten"
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$time_start = microtime(true);
		$analysisNames = $input->getOption("analysis");
		$library_filters = $input->getOption('library-filter');
		foreach ($analysisNames as $analysisName) {
			if (!isset($this->directoryAnalyses[$analysisName]) && !isset($this->analysisVisitors[$analysisName]) && !isset($this->systemAnalyses[$analysisName])) {
				throw new \RuntimeException("No such analysis `$analysisName`");
			}
		}

		if (file_exists($this->cacheFile) === true) {
			$cache = json_decode(file_get_contents($this->cacheFile), true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$cache = [];
			}
		}

		foreach (new \DirectoryIterator($this->libraryRoot) as $libraryRootFileInfo) {
			if ($libraryRootFileInfo->isDir() && !$libraryRootFileInfo->isDot()) {
				if ($this->passesFilter(str_replace($this->libraryRoot, '', (string) $libraryRootFileInfo), $library_filters) === false) {
					continue;
				}

				$libraryname = $libraryRootFileInfo->getFilename();
				echo "analysing $libraryname\n";
				$library_cache = isset($cache[$libraryname]) ? $cache[$libraryname] : [];

				$force = $input->getOption('force');
				/** @var DirectoryAnalysisInterface[] $directoryAnalysesToRun */
				$directoryAnalysesToRun = [];
				/** @var AnalysisVisitorInterface[] $analysisVisitorsToRun */
				$analysisVisitorsToRun = [];
				/** @var SystemAnalysisInterface[] $systemAnalysesToRun */
				$systemAnalysesToRun = [];
				foreach ($analysisNames as $analysisName) {
					if (isset($this->directoryAnalyses[$analysisName])) {
						$directoryAnalysis = $this->directoryAnalyses[$analysisName];
						if ($force || !$this->areAllKeysSet($directoryAnalysis->getSuppliedAnalysisKeys(), $library_cache)) {
							$status = 'running';
							$directoryAnalysesToRun[$analysisName] = $directoryAnalysis;
						} else {
							$status = 'in cache';
						}
						echo "director analysis `$analysisName`: $status\n";
					}
					if (isset($this->analysisVisitors[$analysisName])) {
						$analysisVisitor = $this->analysisVisitors[$analysisName];
						if ($force || !$this->areAllKeysSet($analysisVisitor->getSuppliedAnalysisKeys(), $library_cache)) {
							$status = 'running';
							$analysisVisitorsToRun[$analysisName] = $analysisVisitor;
						} else {
							$status = 'in cache';
						}
						echo "analysis visitor `$analysisName`: $status\n";
					}
					if (isset($this->systemAnalyses[$analysisName])) {
						$systemAnalysis = $this->systemAnalyses[$analysisName];
						if ($force || !$this->areAllKeysSet($systemAnalysis->getSuppliedAnalysisKeys(), $library_cache)) {
							$status = 'running';
							$systemAnalysesToRun[$analysisName] = $systemAnalysis;
						} else {
							$status = 'in cache';
						}
						echo "system analysis `$analysisName`: $status\n";
					}
				}

				if (count($directoryAnalysesToRun) > 0) {
					echo "running directory analyses\n";
					foreach ($directoryAnalysesToRun as $directoryAnalysis) {
						$analysisResults = $directoryAnalysis->analyse($libraryRootFileInfo);
						foreach ($analysisResults as $key => $value) {
							$library_cache[$key] = $value;
						}
					}
					echo "directory analyses done\n";
				} else {
					echo "no directory analyses to run\n";
				}
				if (count($analysisVisitorsToRun) > 0 || count($systemAnalysesToRun) > 0) {
					$traverser = new NodeTraverser();
					$traverser->addVisitor($this->nameResolvingVisitor);
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$traverser->addVisitor($analysisVisitor);
						$analysisVisitor->enterLibrary();
					}
					echo "analysing files ";
					$filenames = [];
					foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($libraryRootFileInfo->getRealPath())), "/.*\.php$/i") as $libraryFileInfo) {
						$filenames[] = $filename = (string) $libraryFileInfo;
						try {
							if (count($analysisVisitorsToRun) > 0) {
								$nodes = $this->ast_memory_caching_parser->parse($filename);
								$traverser->traverse($nodes);
							}
							echo ".";
						} catch (\Exception $e) {
							echo "E";
						}
					}
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$analysisVisitor->leaveLibrary();
						$library_cache = array_merge($library_cache, $analysisVisitor->getAnalysisResults());
					}
					echo "\n";
					if (count($systemAnalysesToRun) > 0) {
						echo "creating sdg\n";
						$system = $this->sdg_factory->create($filenames);
						foreach ($systemAnalysesToRun as $systemAnalysis) {
							$analysisResults = $systemAnalysis->analyse($system);
							$library_cache = array_merge($library_cache, $analysisResults);
						}
					}
				} else {
					echo "file analysis not required\n";
				}

				$cache[$libraryname] = $library_cache;

				file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
				echo "$libraryname done " . json_encode($library_cache, JSON_PRETTY_PRINT) . "\n";
			}
			$this->ast_memory_caching_parser->clear();
		}
		echo "all done\n";
		echo 'runtime: ' . (microtime(true) - $time_start) . "s\n";
		echo 'peak memory usage: ' . memory_get_peak_usage(true) / 1024 / 1024 . "M\n";
	}

	private function passesFilter($library_path, $library_filters) {
		if (empty($library_filters) === true) {
			return true;
		}
		foreach ($library_filters as $library_filter) {
			if (stripos($library_path, $library_filter) !== false) {
				return true;
			}
		}
		return false;
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