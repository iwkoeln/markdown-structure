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

        $basePath = '/var/www/html';
        $mdProjectPath = '/var/www/html/tests/Data';
        $indexPath = '/var/www/html/tests/Data/index.md';
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $factory = new MarkdownProjectFactory($basePath, $url, $mdProjectPath, $indexPath);

        $project = $factory->create();

        $errors = $project->getFileByPath('/var/www/html/tests/Data/validator/cause-error.md')->errors;
        $expectedErrors = [
            0 => new LinkTargetNotFoundError('/var/www/html/tests/Data/validator/cause-error.md', 'Link target not found'),
        ];
        $expectedErrors[0]->setLinkText('This link lies not in root');
        $expectedErrors[0]->setUnfoundFilePath('/README.md');

        $this->assertEquals($expectedErrors, $errors);
    }
}