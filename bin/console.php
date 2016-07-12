<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PhpPdgAnalysis\Analysis\LibraryInfo;
use PhpPdgAnalysis\Analysis\Visitor\FuncCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncEvalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncIncludeCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncVarFeatureCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\DuplicateNameCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\MagicMethodCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\ClassCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\ClosureCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FilesWithTopLevelLogicCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FileCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\CreateFunctionCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\CallUserFuncCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\CallCountingVisitor;
use PhpPdgAnalysis\Analysis\SystemDependence\ResolvedCallCountingAnalysis;
use PhpPdgAnalysis\Table\Overview;
use PhpPdgAnalysis\Table\FuncIncludes;
use PhpPdgAnalysis\Table\FuncEval;
use PhpPdgAnalysis\Table\FuncVarVar;
use PhpPdgAnalysis\Table\CallOverloading;
use PhpPdgAnalysis\Table\DuplicateNames;
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
	"library-info" => new LibraryInfo(),
];
ksort($directoryAnalyses);
$analysingVisitors = [
	"func-count" => new FuncCountingVisitor(),
	"func-eval-count" => new FuncEvalCountingVisitor(),
	"func-include-count" => new FuncIncludeCountingVisitor(),
	"func-var-feature-count" => new FuncVarFeatureCountingVisitor(),
	'duplicate-name-count' => new DuplicateNameCountingVisitor(),
	'magic-method-count' => new MagicMethodCountingVisitor(),
	'class-count' => new ClassCountingVisitor(),
	'closure-count' => new ClosureCountingVisitor(),
	'files-with-top-level-logic-count' => new FilesWithTopLevelLogicCountingVisitor(),
	'file-count' => new FileCountingVisitor(),
	'create-function-count' => new CreateFunctionCountingVisitor(),
	'call-user-func-count' => new CallUserFuncCountingVisitor(),
	'call-count' => new CallCountingVisitor(),
];
ksort($analysingVisitors);
$systemAnalyses = [
	'resolved-call-count' => new ResolvedCallCountingAnalysis(),
];
ksort($systemAnalyses);
$tables = [
	"overview" => new Overview(),
	"func-eval" => new FuncEval(),
	"func-includes" => new FuncIncludes(),
	"func-var-var" => new FuncVarVar(),
	"call-overloading" => new CallOverloading(),
	'duplicate-names' => new DuplicateNames(),
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