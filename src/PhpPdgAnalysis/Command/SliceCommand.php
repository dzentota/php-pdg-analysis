<?php

namespace PhpPdgAnalysis\Command;

use PHPCfg\Parser;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpPdg\CfgBridge\Script as CfgBridgeScript;
use PhpPdg\CfgBridge\System as CfgBridgeSystem;
use PhpPdg\Graph\Edge;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\Graph\Slicing\Slicer;
use PhpPdg\Graph\Slicing\SlicerInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\System;
use PhpPdgAnalysis\Slicing\SlicingVisitor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PhpPdg\SystemDependence\Factory as SdgFactory;

class SliceCommand extends Command {
	/**
	 * AnalysisListCommand constructor.
	 */
	public function __construct() {
		parent::__construct('slice');
	}

	protected function configure() {
		$this
			->setDescription('Create a new system which is a backward slice of another system')
			->addArgument(
				'inputPath',
				InputArgument::REQUIRED,
				'Path to the file or directory to slice'
			)
			->addArgument(
				'outputPath',
				InputArgument::REQUIRED,
				'Output path for the sliced file or directory'
			)
			->addArgument(
				'sliceLineNr',
				InputArgument::REQUIRED,
				'Slicing criterion line nr'
			)
			->addArgument(
				'sliceFilePath',
				InputArgument::OPTIONAL,
				'Slicing criterion filepath'
			)
			->addOption(
				'forward',
				'f',
				InputOption::VALUE_NONE,
				'Generate a forward instead of a backward slice'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$inputPath = realpath($input->getArgument('inputPath'));
		$outputPath = $input->getArgument('outputPath');
		$sliceFilePath = realpath($input->getArgument('sliceFilePath'));
		$sliceLineNr = (int) $input->getArgument('sliceLineNr');
		$ast_parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		$cfg_parser = new Parser($ast_parser);
		$sdg_factory = SdgFactory::createDefault();

		$file_asts = [];
		if (is_dir($inputPath) === true) {
			if (file_exists($outputPath) && !is_dir($outputPath)) {
				throw new \RuntimeException("Output path exists and is not a directory");
			}

			/** @var \SplFileInfo $fileInfo */
			foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($inputPath)), "/.*\.php$/i") as $fileInfo) {
				$file_path = $fileInfo->getRealPath();
				$file_asts[$file_path] = $ast_parser->parse(file_get_contents($file_path));
			}
		} else if (is_file($inputPath) === true) {
			if (file_exists($outputPath) && !is_file($outputPath)) {
				throw new \RuntimeException("Output path exists and is not a file");
			}

			$file_asts[realpath($inputPath)] = $ast_parser->parse(file_get_contents($inputPath));
		} else {
			throw new \RuntimeException("No such file or directory `$inputPath`");
		}

		$cfg_bridge_scripts = [];
		foreach ($file_asts as $file_path => $ast) {
			$cfg_bridge_scripts[] = new CfgBridgeScript($file_path, $cfg_parser->parseAst($ast, $file_path));
		}
		$cfg_bridge_system = new CfgBridgeSystem($cfg_bridge_scripts);
		$system = $sdg_factory->create($cfg_bridge_system);

		$slicer = new Slicer();
		$sdg_slicing_criterion = [];
		$func_slicing_criterions = new \SplObjectStorage();
		$funcs_seen = new \SplObjectStorage();
		foreach ($system->getFuncs() as $func) {
			$func_slicing_criterion = [];
			if ($func->filename === $sliceFilePath) {
				foreach ($func->pdg->getNodes() as $node) {
					if ($node instanceof OpNode && $node->op->getLine() === $sliceLineNr) {
						$func_slicing_criterion[] = $node;
					}
				}
			}
			if (empty($func_slicing_criterion) === false) {
				$func_slicing_criterions[$func] = $func_slicing_criterion;
				$funcs_seen->attach($func);
				$func_worklist[] = $func;
				$sdg_slicing_criterion[] = new FuncNode($func);
			}
		}
		$sliced_sdg = new Graph();
		$slicer->slice($system->sdg, $sdg_slicing_criterion, $sliced_sdg);
		foreach ($sliced_sdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				/** @var Edge[] $contains_edges */
				$contains_edges = $sliced_sdg->getEdges(null, $node, [
					'type' => 'contains'
				]);
				assert(count($contains_edges) === 1);
				/** @var FuncNode $containing_func_node */
				$containing_func_node = $contains_edges[0]->getFromNode();
				assert($containing_func_node instanceof FuncNode);
				$containing_func = $containing_func_node->getFunc();
				$func_slicing_criterion = isset($func_slicing_criterions[$containing_func]) ? $func_slicing_criterions[$containing_func] : [];
				$func_slicing_criterion[] = $node;
				$func_slicing_criterions[$containing_func] = $func_slicing_criterion;
			}
		}
		$sliced_system = new System($sliced_sdg);
		$sliced_system->scripts = $this->sliceFuncs($slicer, $system->scripts, $func_slicing_criterions);
		$sliced_system->functions = $this->sliceFuncs($slicer, $system->functions, $func_slicing_criterions);
		$sliced_system->methods = $this->sliceFuncs($slicer, $system->methods, $func_slicing_criterions);
		$sliced_system->closures = $this->sliceFuncs($slicer, $system->closures, $func_slicing_criterions);

		$file_line_nrs = [];
		foreach ($sliced_system->getFuncs() as $func) {
			foreach ($func->pdg->getNodes() as $node) {
				if ($node instanceof OpNode) {
					$file_line_nrs[$node->op->getFile()][$node->op->getLine()] = 1;
				}
			}
		}

		$ast_printer = new Standard();
		foreach ($file_asts as $file_path => $ast) {
			$slicing_visitor = new SlicingVisitor(isset($file_line_nrs[$file_path]) ? $file_line_nrs[$file_path] : []);
			$node_traverser = new NodeTraverser();
			$node_traverser->addVisitor($slicing_visitor);
			$sliced_ast = $node_traverser->traverse($ast);
			$output_file_path = str_replace($inputPath, $outputPath, $file_path);
			file_put_contents($output_file_path, $ast_printer->prettyPrintFile($sliced_ast));
		}
	}

	/**
	 * @param SlicerInterface $slicer
	 * @param Func[] $funcList
	 * @param \SplObjectStorage|NodeInterface[] $func_slicing_criterions
	 * @return Func[]
	 */
	private function sliceFuncs(SlicerInterface $slicer, $funcList, $func_slicing_criterions) {
		$result = [];
		foreach ($funcList as $key => $func) {
			if (isset($func_slicing_criterions[$func]) === true) {
				$sliced_pdg = new Graph();
				$slicer->slice($func->pdg, $func_slicing_criterions[$func], $sliced_pdg);
				$result[$key] = new Func($func->name, $func->class_name, $func->filename, $func->entry_node, $sliced_pdg);
			}
		}
		return $result;
	}
}