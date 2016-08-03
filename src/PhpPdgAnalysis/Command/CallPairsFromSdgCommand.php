<?php

namespace PhpPdgAnalysis\Command;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\NsFuncCall;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpParser\ParserFactory;
use PhpPdg\ProgramDependence\DataDependence\CombiningGenerator;
use PhpPdg\ProgramDependence\DataDependence\MaybeGenerator as MaybeDataDependenceGenerator;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\ProgramDependence\MemoryCachingFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdg\SystemDependence\FilesystemFactory as SdgFilesystemFactory;
use PhpPdg\AstBridge\Parser\FileCachingParser as AstFileCachingParser;
use PhpPdg\AstBridge\Parser\MemoryCachingParser as AstMemoryCachingParser;
use PhpPdg\AstBridge\Parser\WrappedParser as AstWrappedParser;
use PhpPdg\CfgBridge\Parser\FileCachingParser as CfgFileCachingParser;
use PhpPdg\CfgBridge\Parser\WrappedParser as CfgWrappedParser;
use PhpPdgAnalysis\ProgramDependence\DebugFactory as PdgDebugFactory;
use PhpPdgAnalysis\SystemDependence\DebugFactory as SdgDebugFactory;
use PhpPdgAnalysis\SystemDependence\DebugFilesystemFactory as SdgDebugFilesystemFactory;
use PhpPdg\Graph\Factory as GraphFactory;
use PhpPdg\ProgramDependence\ControlDependence\BlockFlowGraph\Generator as BlockCfgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\BlockDependenceGraph\Generator as BlockCdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\PostDominatorTree\Generator as PdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\Generator as ControlDependenceGenerator;
use PhpPdg\ProgramDependence\DataDependence\Generator as DataDependenceGenerator;
use PhpPdg\ProgramDependence\Node\OpNode;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\StaticCall;
use PHPCfg\Operand;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\Node\BuiltinFuncNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;

class CallPairsFromSdgCommand extends Command {
	private $cacheDir;

	public function __construct($cacheDir) {
		$this->cacheDir = $cacheDir;
		parent::__construct("call-pairs:from-sdg");
	}

	protected function configure() {
		$this
			->setDescription("Generate a call pairs file from an system dir using an SDG")
			->addArgument(
				'systemDir',
				InputArgument::REQUIRED,
				'Path to root dir of system to generate SDG for'
			)
			->addArgument(
				'outputFile',
				InputArgument::REQUIRED,
				'Path to output compiled call pairs to'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$starttime = microtime(true);
		$systemdir = $input->getArgument('systemDir');
		$outputFile = $input->getArgument('outputFile');

		$ast_string_parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$memory_caching_ast_parser = new AstMemoryCachingParser(new AstFileCachingParser($this->cacheDir. '/ast', new AstWrappedParser($ast_string_parser), true));

		$cfg_parser = new CfgFileCachingParser($this->cacheDir . '/cfg', new CfgWrappedParser($memory_caching_ast_parser), true);
		$graph_factory = new GraphFactory();
		$block_cfg_generator = new BlockCfgGenerator($graph_factory);
		$block_cdg_generator = new BlockCdgGenerator($graph_factory);
		$pdt_generator = new PdgGenerator($graph_factory);
		$control_dependence_generator = new ControlDependenceGenerator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new CombiningGenerator([
			new DataDependenceGenerator(),
			new MaybeDataDependenceGenerator()
		]);
		$memory_caching_pdg_factory = new MemoryCachingFactory(new PdgDebugFactory(new PdgFactory($graph_factory, $control_dependence_generator, $data_dependence_generator)));
		$sdg_factory = new SdgDebugFilesystemFactory(new SdgFilesystemFactory($cfg_parser, new SdgDebugFactory(new SdgFactory($graph_factory, $memory_caching_pdg_factory))));

		$system = $sdg_factory->create($systemdir);

		$fileLineCallCounts = [];    // used for tracking how many calls there are at one location
		$out = [];
		foreach ($system->sdg->getNodes() as $node) {
			$funcName = null;
			if ($node instanceof OpNode) {
				$op = $node->op;

				if (
					$op instanceof FuncCall
					|| $op instanceof NsFuncCall
					|| $op instanceof MethodCall
					|| $op instanceof StaticCall
				) {
					$opFilename = $op->getFile();
					$opEndLine = $op->getAttribute('endLine');

					// skip multiple calls on one line
					if (isset($fileLineCallCounts[$opFilename][$opEndLine]) === true) {
						$fileLineCallCounts[$opFilename][$opEndLine]++;
						if (isset($out['calls'][$opFilename][$opEndLine]) === true) {
							unset($out['calls'][$opFilename][$opEndLine]);
						}
						continue;
					}
					$fileLineCallCounts[$opFilename][$opEndLine] = 1;

					$callEdges = $system->sdg->getEdges($node, null, ['type' => 'call']);
					if (!empty($callEdges)) {
						foreach ($callEdges as $callEdge) {
							/** @var FuncNode $toNode */
							$toNode = $callEdge->getToNode();
							$toScopedName = null;
							if ($toNode instanceof FuncNode) {
								$toScopedName = $toNode->getFunc()->getScopedName();
							} else if ($toNode instanceof BuiltinFuncNode) {
								$toScopedName = $toNode->getId();
							}

							if ($toScopedName !== null) {
								$out['calls'][$opFilename][$opEndLine][$toScopedName] = 1;
							}
						}
					}
				}
			} else if ($node instanceof FuncNode) {
				$out['funcs'][$node->getFunc()->getScopedName()] = 1;
			} else if ($node instanceof BuiltinFuncNode) {
				$out['builtin-funcs'][$node->getId()] = 1;
			}
		}

		file_put_contents($outputFile, json_encode($out, JSON_PRETTY_PRINT));
		echo sprintf("Time: %0.2fs\n", microtime(true) - $starttime);
	}
}