<?php
require_once 'vendor/autoload.php';

use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\CombineTextAndImagesParser;
use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Symfony\Component\ErrorHandler\Debug;

Debug::enable();

$projectRootPath = getenv('BASE_PATH') ?: '/var/www/html';
$mdProjectPath = "/tests/Fixtures/docs";
//$mdProjectPath = "/tests/Fixtures/docs-with-errors";
//$mdProjectPath = "/tests/Fixtures/general-editors-guide.git";
$indexPath = "/index.md";
$url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

$factory = new MarkdownProjectFactory($projectRootPath, $mdProjectPath, $indexPath, $url);

//$factory->addFile('docs/index.md');
//$factory->addFile('docs/feature/baum/index.md');
//$factory->addFile('/var/www/html/docs/feature/baum/kuchen.md');

//$factory->addFiles(
//    [
//        $basePath . '/tests/Fixtures/some-code-file.yml',
//    ]
//);

$factory->registerParser([
    new SplitByEmptyLineParser(),
    new HeadlinesToSectionParser(),
//    new RemoveDevSectionsParser(),
    new CombineTextAndImagesParser(),
    new CombineTextAndListParser(),
]);

$factory->registerValidators([
//    new MediaFileValidator(),
]);

$factory->registerFinisher([
//    new ParagraphToContainerParser(),
//    new SectionsToHtmlParser()
]);

$factory->addFiles(FilesFinder::findFilesByPath($factory->absoluteDocumentationPath));

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

