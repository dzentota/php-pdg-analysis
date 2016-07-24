<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpPdg\ProgramDependence\DataDependence\CombiningGenerator;
use PhpPdg\ProgramDependence\DataDependence\MaybeGenerator as MaybeDataDependenceGenerator;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\ProgramDependence\MemoryCachingFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdg\SystemDependence\FilesystemFactory as SdgFilesystemFactory;
use PhpPdgAnalysis\Analysis\DirectoryAnalysisInterface;
use PhpPdgAnalysis\Analysis\ProgramDependence\FuncAnalysisInterface;
use PhpPdgAnalysis\Analysis\SystemDependence\SystemAnalysisInterface;
use PhpPdgAnalysis\Analysis\Visitor\AnalysisVisitorInterface;
use PhpPdg\AstBridge\Parser\FileCachingParser as AstFileCachingParser;
use PhpPdg\AstBridge\Parser\MemoryCachingParser as AstMemoryCachingParser;
use PhpPdg\AstBridge\Parser\WrappedParser as AstWrappedParser;
use PhpPdg\CfgBridge\Parser\FileCachingParser as CfgFileCachingParser;
use PhpPdg\CfgBridge\Parser\WrappedParser as CfgWrappedParser;
use PhpPdgAnalysis\ProgramDependence\DebugFactory as PdgDebugFactory;
use PhpPdgAnalysis\SystemDependence\DebugFactory as SdgDebugFactory;
use PhpPdgAnalysis\SystemDependence\DebugFilesystemFactory as SdgDebugFilesystemFactory;
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
	/** @var  FuncAnalysisInterface[] */
	private $funcAnalyses;
	/** @var SystemAnalysisInterface[] */
	private $systemAnalyses;
	/** @var AstMemoryCachingParser */
	private $memory_caching_ast_parser;
	/** @var CfgFileCachingParser  */
	private $cfg_parser;
	/** @var  PdgDebugFactory */
	private $memory_caching_pdg_factory;
	/** @var  SdgDebugFilesystemFactory */
	private $sdg_factory;

	/**
	 * AnalysisRunCommand constructor.
	 * @param string $libraryRoot
	 * @param string $cacheFile
	 * @param string $cacheDir
	 * @param DirectoryAnalysisInterface[] $directoryAnalyses
	 * @param AnalysisVisitorInterface[] $analysisVisitors
	 * @param FuncAnalysisInterface[] $funcAnalyses
	 * @param SystemAnalysisInterface[] $systemAnalyses
	 */
	public function __construct($libraryRoot, $cacheFile, $cacheDir, array $directoryAnalyses = [], array $analysisVisitors = [], array $funcAnalyses = [], array $systemAnalyses = []) {
		$this->libraryRoot = $libraryRoot;
		$this->cacheFile = $cacheFile;
		$this->cacheDir = $cacheDir;
		$this->directoryAnalyses = $directoryAnalyses;
		$this->nameResolvingVisitor = new NameResolver();
		$this->analysisVisitors = $analysisVisitors;
		$this->funcAnalyses = $funcAnalyses;
		$this->systemAnalyses = $systemAnalyses;

		$ast_string_parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->memory_caching_ast_parser = new AstMemoryCachingParser(new AstFileCachingParser($cacheDir . '/ast', new AstWrappedParser($ast_string_parser), true));

		$this->cfg_parser = new CfgFileCachingParser($cacheDir . '/cfg', new CfgWrappedParser($this->memory_caching_ast_parser), true);
		$graph_factory = new GraphFactory();
		$block_cfg_generator = new BlockCfgGenerator($graph_factory);
		$block_cdg_generator = new BlockCdgGenerator($graph_factory);
		$pdt_generator = new PdgGenerator($graph_factory);
		$control_dependence_generator = new ControlDependenceGenerator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new CombiningGenerator([
			new DataDependenceGenerator(),
			new MaybeDataDependenceGenerator()
		]);
		$this->memory_caching_pdg_factory = new MemoryCachingFactory(new PdgDebugFactory(new PdgFactory($graph_factory, $control_dependence_generator, $data_dependence_generator)));
		$this->sdg_factory = new SdgDebugFilesystemFactory(new SdgFilesystemFactory($this->cfg_parser, new SdgDebugFactory(new SdgFactory($graph_factory, $this->memory_caching_pdg_factory))));

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
			if (!isset($this->directoryAnalyses[$analysisName]) && !isset($this->analysisVisitors[$analysisName]) && !isset($this->funcAnalyses[$analysisName]) && !isset($this->systemAnalyses[$analysisName])) {
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
				$librarydir = $libraryRootFileInfo->getRealPath();
				echo "analysing $libraryname\n";
				$library_cache = isset($cache[$libraryname]) ? $cache[$libraryname] : [];

				$force = $input->getOption('force');
				/** @var DirectoryAnalysisInterface[] $directoryAnalysesToRun */
				$directoryAnalysesToRun = [];
				/** @var AnalysisVisitorInterface[] $analysisVisitorsToRun */
				$analysisVisitorsToRun = [];
				/** @var FuncAnalysisInterface[] $funcAnalysesToRun */
				$funcAnalysesToRun = [];
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
					if (isset($this->funcAnalyses[$analysisName])) {
						$funcAnalysis = $this->funcAnalyses[$analysisName];
						if ($force || !$this->areAllKeysSet($funcAnalysis->getSuppliedAnalysisKeys(), $library_cache)) {
							$status = 'running';
							$funcAnalysesToRun[$analysisName] = $funcAnalysis;
						} else {
							$status = 'in cache';
						}
						echo "func analysis `$analysisName`: $status\n";
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
						$analysisResults = $directoryAnalysis->analyse($librarydir);
						foreach ($analysisResults as $key => $value) {
							$library_cache[$key] = $value;
						}
					}
					echo "directory analyses done\n";
				} else {
					echo "no directory analyses to run\n";
				}
				if (count($analysisVisitorsToRun) > 0 || count($funcAnalysesToRun) > 0 || count($systemAnalysesToRun) > 0) {
					$traverser = new NodeTraverser();
					$traverser->addVisitor($this->nameResolvingVisitor);
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$traverser->addVisitor($analysisVisitor);
						$analysisVisitor->enterLibrary($libraryname);
					}
					foreach ($funcAnalysesToRun as $funcAnalysis) {
						$funcAnalysis->enterLibrary($libraryname);
					}
					echo "analysing files ";
					/** @var \SplFileInfo $libraryFileInfo */
					foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($libraryRootFileInfo->getRealPath())), "/.*\\.php$/i") as $libraryFileInfo) {
						$filename = $libraryFileInfo->getRealPath();
						try {
							if (count($analysisVisitorsToRun) > 0) {
								$nodes = $this->memory_caching_ast_parser->parse($filename);
								$traverser->traverse($nodes);
							}
							if (count($funcAnalysesToRun) > 0) {
								$script = $this->cfg_parser->parse($filename);
								foreach (array_merge([$script->main], $script->functions) as $cfg_func) {
									$pdg = $this->memory_caching_pdg_factory->create($cfg_func, $filename);
									foreach ($funcAnalysesToRun as $funcAnalysis) {
										$funcAnalysis->analyse($pdg);
									}
								}
							}
							echo ".";
						} catch (\Exception $e) {
							echo "E";
						}
					}
					foreach ($analysisVisitorsToRun as $analysisVisitor) {
						$analysisVisitor->leaveLibrary($libraryname);
						$library_cache = array_merge($library_cache, $analysisVisitor->getAnalysisResults());
					}
					foreach ($funcAnalysesToRun as $funcAnalysis) {
						$funcAnalysis->leaveLibrary($libraryname);
						$library_cache = array_merge($library_cache, $funcAnalysis->getAnalysisResults());
					}
					echo "\n";
					if (count($systemAnalysesToRun) > 0) {
						echo "creating sdg\n";
						$system = $this->sdg_factory->create($librarydir);
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
			$this->memory_caching_ast_parser->clear();
			$this->memory_caching_pdg_factory->clear();
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