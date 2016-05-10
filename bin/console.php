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
use PhpPdgAnalysis\Table\Overview;
use PhpPdgAnalysis\Table\FuncProblematicDataDependences;
use PhpPdgAnalysis\Table\FuncAssignRef;
use PhpPdgAnalysis\Command\AnalysisClearCommand;
use PhpPdgAnalysis\Command\AnalysisRunCommand;
use PhpPdgAnalysis\Command\AnalysisListCommand;
use PhpPdgAnalysis\Command\TablePrintCommand;
use PhpPdgAnalysis\Command\TableListCommand;

$libraryRoot = 'C:\Users\mwijngaard\Documents\Projects\_verification';
$cacheFile = __DIR__ . '/cache.json';
$directoryAnalyses = [
	"libraryInfo" => new LibraryInfo(),
];
$analysingVisitors = [
	"func-count" => new FuncCountingVisitor(),
	"func-assign-ref-count" => new FuncAssignRefCountingVisitor(),
	"func-eval-count" => new FuncEvalCountingVisitor(),
	"func-preg-eval-count" => new FuncPregEvalCountingVisitor(),
	"func-global-count" => new FuncGlobalCountingVisitor(),
	"func-include-count" => new FuncIncludeCountingVisitor(),
	"func-var-var-count" => new FuncVarVarCountingVisitor(),
];
$tables = [
	"overview" => new Overview(),
	"func-problematic-data-deps" => new FuncProblematicDataDependences(),
	"func-assign-ref" => new FuncAssignRef(),
];

$application = new Application();
$application->add(new AnalysisClearCommand($cacheFile));
$application->add(new AnalysisRunCommand($libraryRoot, $cacheFile, $directoryAnalyses, $analysingVisitors));
$application->add(new AnalysisListCommand($directoryAnalyses, $analysingVisitors));
$application->add(new TablePrintCommand($cacheFile, $tables));
$application->add(new TableListCommand($tables));
$application->run();