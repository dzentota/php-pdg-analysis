<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpPdg\CfgBridge\Script;
use PhpPdg\CfgBridge\System;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdgAnalysis\Analysis\DirectoryAnalysisInterface;
use PhpPdgAnalysis\Analysis\SystemDependence\SystemAnalysisInterface;
use PhpPdgAnalysis\Analysis\Visitor\AnalysisVisitorInterface;
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
	/** @var DirectoryAnalysisInterface[] */
	private $directoryAnalyses;
	/** @var NameResolver  */
	private $nameResolvingVisitor;
	/** @var AnalysisVisitorInterface[] */
	private $analysisVisitors;
	/** @var SystemAnalysisInterface[] */
	private $systemAnalyses;
	/** @var Parser  */
	private $parser;
	/** @var \PHPCfg\Parser  */
	private $cfg_parser;
	/** @var  SdgFactory */
	private $sdg_factory;

	/**
	 * AnalysisRunCommand constructor.
	 * @param string $libraryRoot
	 * @param string $cacheFile
	 * @param DirectoryAnalysisInterface[] $directoryAnalyses
	 * @param AnalysisVisitorInterface[] $analysisVisitors
	 * @param SystemAnalysisInterface[] $systemAnalyses
	 */
	public function __construct($libraryRoot, $cacheFile, array $directoryAnalyses = [], array $analysisVisitors = [], array $systemAnalyses = []) {
		$this->libraryRoot = $libraryRoot;
		$this->cacheFile = $cacheFile;
		$this->directoryAnalyses = $directoryAnalyses;
		$this->nameResolvingVisitor = new NameResolver();
		$this->analysisVisitors = $analysisVisitors;
		$this->systemAnalyses = $systemAnalyses;
		$this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->cfg_parser = new \PHPCfg\Parser($this->parser);
		
		$graph_factory = new GraphFactory();
		$block_cfg_generator = new BlockCfgGenerator($graph_factory);
		$block_cdg_generator = new BlockCdgGenerator($graph_factory);
		$pdt_generator = new PdgGenerator($graph_factory);
		$control_dependence_generator = new ControlDependenceGenerator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new DataDependenceGenerator();
		$pdg_factory = new PdgFactory($graph_factory, $control_dependence_generator, $data_dependence_generator);
		$this->sdg_factory = new SdgDebugFactory(new SdgFactory($graph_factory, new PdgDebugFactory($pdg_factory)));

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
				"Which libraries should be tested (matched case insensitively, default none)",
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
		try {
			$cache = json_decode(file_get_contents($this->cacheFile), true);
		} catch (\Exception $e) {
			$cache = [];
		}

		foreach (new \DirectoryIterator($this->libraryRoot) as $libraryRootFileInfo) {
			if ($libraryRootFileInfo->isDir() && !$libraryRootFileInfo->isDot()) {
				if ($this->passesFilter(str_replace($this->libraryRoot, '', (string) $libraryRootFileInfo), $library_filters) === false) {
					continue;
				}
				$scripts = [];

				$filename = $libraryRootFileInfo->getFilename();
				echo "analysing $filename\n";
				$fullPath = (string) $libraryRootFileInfo;
				$pathCache = isset($cache[$fullPath]) ? $cache[$fullPath] : [];

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
					if (isset($this->systemAnalyses[$analysisName])) {
						$systemAnalysis = $this->systemAnalyses[$analysisName];
						if ($force || !$this->areAllKeysSet($systemAnalysis->getSuppliedAnalysisKeys(), $pathCache)) {
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
							$pathCache[$key] = $value;
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
					foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($libraryRootFileInfo->getRealPath())), "/.*\.php$/i") as $libraryFileInfo) {
						try {
							$code = file_get_contents((string) $libraryFileInfo);
							$nodes = $this->parser->parse($code);
							$scripts[(string) $libraryFileInfo] = $script = $this->cfg_parser->parseAst($nodes, (string) $libraryFileInfo);
							$traverser->traverse($nodes);
							echo ".";
						} catch (\Exception $e) {
							echo "E";
						}
					}
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$analysisVisitor->leaveLibrary();
						$pathCache = array_merge($pathCache, $analysisVisitor->getAnalysisResults());
					}
					echo "\n";
					if (count($systemAnalysesToRun) > 0) {
						echo "creating sdg\n";
						$cfg_bridge_system = new System();
						foreach ($scripts as $file_path => $script) {
							$cfg_bridge_system->addScript($file_path, $script);
						}
						$system = $this->sdg_factory->create($cfg_bridge_system);
						foreach ($systemAnalysesToRun as $systemAnalysis) {
							$analysisResults = $systemAnalysis->analyse($system);
							$pathCache = array_merge($pathCache, $analysisResults);
						}
					}
				} else {
					echo "file analysis not required\n";
				}

				$cache[$fullPath] = $pathCache;

				file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
				echo "$filename done " . json_encode($pathCache, JSON_PRETTY_PRINT) . "\n";
			}
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