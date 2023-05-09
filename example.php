<?php


use Armin\MarkdownStructure\MarkdownProjectFactory;

$factory = new MarkdownProjectFactory('/var/www/html/docs', 'https://bitbucket.org/iwm/...');
$factory->setFiles(['docs/index.md', 'docs/feature/test.md', 'docs/feature/img/bild.png']);

$factory->enableValidation = false;
$factory->enableNestedStructure = false;

// Get file contents from Git
//$factory->setReadFileFunction(function(string $filePath) use($project, $branch) {
//    return $this->getContent($project, $branch, substr($filePath, strlen($this->getFolderPrefix($project))));
//});

$project = $factory->create();
//dump($factory);
//dd($project);
