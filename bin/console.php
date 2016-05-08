<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use PhpPdgAnalysis\Analysis\LibraryInfo;
use PhpPdgAnalysis\Analysis\Visitor\FunctionCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncAssignRefCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncEvalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncGlobalCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncIncludeCountingVisitor;
use PhpPdgAnalysis\Analysis\Visitor\FuncVarVarCountingVisitor;
use PhpPdgAnalysis\Table\Overview;
use PhpPdgAnalysis\Table\ProblematicFeatures;
use PhpPdgAnalysis\Command\AnalysisClearCommand;
use PhpPdgAnalysis\Command\AnalysisRunCommand;
use PhpPdgAnalysis\Command\AnalysisListCommand;
use PhpPdgAnalysis\Command\TablePrintCommand;
use PhpPdgAnalysis\Command\TableListCommand;

$cacheFile = __DIR__ . '/cache.json';

$directoryAnalyses = [
	"libraryInfo" => new LibraryInfo(),
];
$analysingVisitors = [
	"functionCount" => new FunctionCountingVisitor(),
	"funcAssignRefCount" => new FuncAssignRefCountingVisitor(),
	"funcEvalCount" => new FuncEvalCountingVisitor(),
	"funcGlobalCount" => new FuncGlobalCountingVisitor(),
	"funcIncludeCount" => new FuncIncludeCountingVisitor(),
	"funcVarVarCount" => new FuncVarVarCountingVisitor(),
];
$tables = [
	"overview" => new Overview(),
	"problematicFeatures" => new ProblematicFeatures(),
];

$application = new Application();
$application->add(new AnalysisClearCommand($cacheFile));
$application->add(new AnalysisRunCommand($cacheFile, $directoryAnalyses, $analysingVisitors));
$application->add(new AnalysisListCommand($directoryAnalyses, $analysingVisitors));
$application->add(new TablePrintCommand($cacheFile, $tables));
$application->add(new TableListCommand($tables));
$application->run();