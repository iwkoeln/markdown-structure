<?php
require_once 'vendor/autoload.php';

use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\CombineTextAndImagesParser;
use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\MarkdownToHTMLParser;
use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Parser\RemoveDevSections;
use Iwm\MarkdownStructure\Parser\SectionsToHtmlParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use League\CommonMark\Exception\CommonMarkException;
use League\Config\Exception\ConfigurationExceptionInterface;
use Symfony\Component\ErrorHandler\Debug;

Debug::enable();

$basePath = getenv('BASE_PATH') ?: '/var/www/html';
$mdProjectPath = "$basePath/tests/Fixtures/docs";
$indexPath = "$mdProjectPath/index.md";
$url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

$factory = new MarkdownProjectFactory($basePath, $url, $mdProjectPath, $indexPath);

$factory->addValidators([
    new \Iwm\MarkdownStructure\Validator\MarkdownProjectValidator(),
    new \Iwm\MarkdownStructure\Validator\MediaFileValidator(),
]);

$factory->addFileParsers([
    new SplitByEmptyLineParser(),
    new HeadlinesToSectionParser(),
    new RemoveDevSections(),
    new CombineTextAndImagesParser(),
    new CombineTextAndListParser(),
    new MarkdownToHTMLParser(),
    new ParagraphToContainerParser(),
    new SectionsToHtmlParser()
]);

$project = $factory->create();
dump($project);

//$factory->loadFilesByPath('/var/www/html/local_packages/general-editors-guide');
//$factory->addExternalSource('baum', '/var/www/html/vendor');
//$factory->enableValidation = false;
//$factory->enableNestedStructure = false;
/*$factory->setValidators([
    new ImageValidator(),
    new LinkValidator(),
    new MarkdownValidator(),
]);

$factory->setErrorHandlers([
    new ImageErrorHandler(),
    new LinkErrorHandler(),
    new MarkdownErrorHandler(),
]);*/

