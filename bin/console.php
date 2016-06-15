<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PhpPdgAnalysis\Analysis\LibraryInfo;
use PhpPdgAnalysis\Analysis\Visitor\FuncCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncAssignRefCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncEvalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncPregEvalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncGlobalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncIncludeCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncVarVarCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncExceptionCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\DuplicateNameCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\MagicMethodCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\ClassCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\TraitCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\YieldCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\ClosureCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FilesWithTopLevelLogicCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FileCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\CreateFunctionCountingVisitor;
use PhpPdgAnalysis\Analysis\SystemDependence\CallCountingAnalysis;
use PhpPdgAnalysis\Table\Overview;
use PhpPdgAnalysis\Table\FuncIncludes;
use PhpPdgAnalysis\Table\FuncRefs;
use PhpPdgAnalysis\Table\FuncEval;
use PhpPdgAnalysis\Command\AnalysisClearCommand;
use PhpPdgAnalysis\Command\AnalysisRunCommand;
use PhpPdgAnalysis\Command\AnalysisListCommand;
use PhpPdgAnalysis\Command\TablePrintCommand;
use PhpPdgAnalysis\Command\TableListCommand;
use PhpPdgAnalysis\Command\SliceCommand;

assert_options(ASSERT_BAIL, 1);

$libraryRoot = 'C:\Users\mwijngaard\Documents\Projects\_verification';
$cacheFile = __DIR__ . '/cache.json';
$directoryAnalyses = [
	"libraryInfo" => new LibraryInfo(),
];
ksort($directoryAnalyses);
$analysingVisitors = [
	"func-count" => new FuncCountingVisitor(),
	"func-assign-ref-count" => new FuncAssignRefCountingVisitor(),
	"func-eval-count" => new FuncEvalCountingVisitor(),
	"func-preg-eval-count" => new FuncPregEvalCountingVisitor(),
	"func-global-count" => new FuncGlobalCountingVisitor(),
	"func-include-count" => new FuncIncludeCountingVisitor(),
	"func-var-var-count" => new FuncVarVarCountingVisitor(),
	'func-exception-count' => new FuncExceptionCountingVisitor(),
	'duplicate-name-count' => new DuplicateNameCountingVisitor(),
	'magic-method-count' => new MagicMethodCountingVisitor(),
	'class-count' => new ClassCountingVisitor(),
	'trait-count' => new TraitCountingVisitor(),
	'yield-count' => new YieldCountingVisitor(),
	'closure-count' => new ClosureCountingVisitor(),
	'files-with-top-level-logic-count' => new FilesWithTopLevelLogicCountingVisitor(),
	'file-count' => new FileCountingVisitor(),
	'create-function-count' => new CreateFunctionCountingVisitor(),
];
ksort($analysingVisitors);
$systemAnalyses = [
	'call-count' => new CallCountingAnalysis(),
];
ksort($systemAnalyses);
$tables = [
	"overview" => new Overview(),
	"func-problematic-data-deps" => new FuncIncludes(),
	"func-refs" => new FuncRefs(),
	"func-eval" => new FuncEval(),
	"func-includes" => new FuncIncludes(),
];
ksort($tables);

$application = new Application();
$application->add(new AnalysisClearCommand($cacheFile));
$application->add(new AnalysisRunCommand($libraryRoot, $cacheFile, $directoryAnalyses, $analysingVisitors, $systemAnalyses));
$application->add(new AnalysisListCommand($directoryAnalyses, $analysingVisitors));
$application->add(new TablePrintCommand($cacheFile, $tables));
$application->add(new TableListCommand($tables));
$application->add(new SliceCommand());
$application->run();