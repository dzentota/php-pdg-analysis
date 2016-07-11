<?php

namespace PhpPdgAnalysis\Command;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpPdg\AstBridge\Slicing\Slicer;
use PhpPdg\AstBridge\System as AstSystem;
use PhpPdg\CfgBridge\SystemFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpPdg\SystemDependence\Factory as PdgSystemFactory;
use PhpPdg\SystemDependence\Slicing\BackwardSlicer as PdgBackwardSystemSlicer;

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
				'sliceFilePath',
				InputArgument::REQUIRED,
				'Slicing criterion filepath'
			)
			->addArgument(
				'sliceLineNr',
				InputArgument::REQUIRED,
				'Slicing criterion line nr'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$input_path = realpath($input->getArgument('inputPath'));
		$output_path = $input->getArgument('outputPath');
		$slice_file_path = realpath($input->getArgument('sliceFilePath'));
		$slice_line_nr = (int) $input->getArgument('sliceLineNr');
		$ast_parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

		$file_paths = [];
		if (is_dir($input_path) === true) {
			if (file_exists($output_path) && !is_dir($output_path)) {
				throw new \RuntimeException("Output path exists and is not a directory");
			}
			/** @var \SplFileInfo $fileInfo */
			foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($input_path)), "/.*\.php$/i") as $fileInfo) {
				$file_path = $fileInfo->getRealPath();
				$file_paths[] = $file_path;
			}
		} else if (is_file($input_path) === true) {
			if (file_exists($output_path) && !is_file($output_path)) {
				throw new \RuntimeException("Output path exists and is not a file");
			}
			$file_paths[] = realpath($input_path);
		} else {
			throw new \RuntimeException("No such file or directory `$input_path`");
		}

		$ast_system = new AstSystem();
		foreach ($file_paths as $file_path) {
			$ast_system->addAst($file_path, $ast_parser->parse(file_get_contents($file_path)));
		}

		$slicer = new Slicer(new SystemFactory(), PdgSystemFactory::createDefault(), new PdgBackwardSystemSlicer());
		$sliced_ast_system = $slicer->slice($ast_system, $slice_file_path, $slice_line_nr);
		$ast_printer = new Standard();
		foreach ($sliced_ast_system->getFilePaths() as $file_path) {
			$output_file_path = str_replace($input_path, $output_path, $file_path);
			file_put_contents($output_file_path, $ast_printer->prettyPrintFile($sliced_ast_system->getAst($file_path)));
		}
	}
}