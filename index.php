<?php
require_once 'vendor/autoload.php';

use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\CombineTextAndImagesParser;
use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Parser\FallbackUrlForProjectFileLinksParser;
use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\MarkdownToHTMLParser;
use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Parser\RemoveDevSectionsParser;
use Iwm\MarkdownStructure\Parser\SectionsToHtmlParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Validator\OrphanValidator;
use League\CommonMark\Exception\CommonMarkException;
use League\Config\Exception\ConfigurationExceptionInterface;
use Symfony\Component\ErrorHandler\Debug;

Debug::enable();

$basePath = getenv('BASE_PATH') ?: '/var/www/html';
$mdProjectPath = "/tests/Fixtures/docs";
$mdProjectPath = "/tests/Fixtures/docs-with-errors";
$mdProjectPath = "/tests/Fixtures/general-editors-guide.git";
$indexPath = "/index.md";
$url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

$factory = new MarkdownProjectFactory($basePath, $mdProjectPath, $indexPath, $url);

//$factory->addFiles(
//    [
//        $basePath . '/tests/Fixtures/some-code-file.yml',
//    ]
//);

$factory->registerValidators([
//    new MediaFileValidator(),
]);

$factory->registerParserForAfterRegistration([
    new SplitByEmptyLineParser(),
    new HeadlinesToSectionParser(),
//    new RemoveDevSectionsParser(),
    new CombineTextAndImagesParser(),
    new CombineTextAndListParser(),
]);

$factory->registerParserForBeforeCreation([
//    new MarkdownToHTMLParser(),
//    new ParagraphToContainerParser(),
//    new SectionsToHtmlParser()
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

