<?php

namespace Iwm\MarkdownStructure\Unit\Validator;

use Iwm\MarkdownStructure\ErrorHandler\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\MarkdownProjectFactory;
use PHPUnit\Framework\TestCase;

class MarkdownProjektValidatorTest extends TestCase
{
    /**
     * @test
     * @testdox MarkdownProjectValidator logs error if linked markdown file not exists in rootline
     */
    public function testValidate()
    {

        $basePath = getenv('BASE_PATH') ?: '/var/www/html';
        $mdProjectPath = "$basePath/tests/Data";
        $indexPath = "$mdProjectPath/index.md";
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $factory = new MarkdownProjectFactory($basePath, $url, $mdProjectPath, $indexPath);

        $project = $factory->create();

        $errors = $project->getFileByPath("$mdProjectPath/validator/cause-error.md")->errors;
        $expectedErrors = [
            0 => new LinkTargetNotFoundError("$mdProjectPath/validator/cause-error.md", 'Link target not found'),
        ];
        $expectedErrors[0]->setLinkText('This link lies not in root');
        $expectedErrors[0]->setUnfoundFilePath('/README.md');

        $this->assertEquals($expectedErrors, $errors);
    }
}