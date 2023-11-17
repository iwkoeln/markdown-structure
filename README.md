# iwm/markdown-structure

[![Version](https://img.shields.io/badge/Version-v1.0.0-blue.svg)](https://bitbucket.org/iwm/markdown-structure/)

## Abstract

When working with Documentation Files, it is often necessary to create a structure of the files. 
This package provides a factory to create a structure of markdown files.
It also helps to validate and parse the files. By doing this,
you will be able to base your documentation on a structure of markdown files and
reduce the effort of creating documentations.

## Description

Markdown Structure requires you to be able to list all files in a directory you want to base your documentation on.
You can either add them manually or provide them as an array.
After you configured Markdown Structure to your liking, you will be provided with the `Markdown Project` and use it to
create beautiful documentations.

## Features

* **[`FilesFinder:`](src/Utility/FilesFinder.php)**
  For easier use of physical files, you can use our File Finder
* **[`FileTreeBuilder:`](src/Utility/FileTreeBuilder.php)**
  In addition to the File Finder, you can enable the File Tree Builder to get a tree structure of your files
* **[`Markdown Validation:`](src/Validator)**
  You can validate your markdown files with our validators or create your own
* **[`Media File Validation:`](src/Validator)**
  In addition to the validation of markdown files, you can also validate media files
* **[`Markdown Project Orphan Validation:`](src/Validator/MarkdownProject/OrphanFileValidator.php)**
  Keeping track of your files can be hard. We help you find orphan files in your project
* **[`Fallback Url For Links to Non-Documenation Files:`](src/Finisher/FallbackUrlForProjectFileLinksFinisher.php)**
  If you want to link to files that are not part of your documentation, you can use this finisher to add a fallback url
* **[`Sectionend Content Parser:`](src/Parser/HeadlinesToSectionParser.php)**
  In order to render beautiful documentations, you can use this parser collection to parse your markdown files
* **[`Error Classes:`](src/Error)**
  We provide you with a collection of error classes to help you find errors in your project

## Usage

To use the `markdown-structure` library in your PHP project, follow these steps:

1. Install the library using composer: `composer require iwm/markdown-structure`
2. Import the library in your PHP file: `use Iwm\MarkdownStructure\MarkdownProjectFactory;`
3. Create a new instance of the `MarkdownProjectFactory` class, providing the required parameters:
    ```php
    $rootPathOfYourProject = '/var/www/html';
    $pathToYourDocumentation = '/Docs';
    $pathToYouDocIndexFile = '/index.md';
    $fallbackUrl = 'https://bitbucket.org/iwm/markdown-structure/src/master/';
    
    $factory = new MarkdownProjectFactory(
        $rootPathOfYourProject, 
        $fallbackUrl, 
        $pathToYourDocumentation, 
        $pathToYouDocIndexFile
    );
    ```
4. Add your files to the factory:
    ```php
    // Add files manually
    $factory->addFile('Docs/index.md');
    $factory->addFile('Docs/1.md');
    $factory->addFile('Docs/2.md');
    $factory->addFile('Docs/3.md');
    
    // Add files using an array
    $factory->addFiles([
       'Docs/index.md',
       'Docs/1.md',
       'Docs/2.md',
       'Docs/3.md'
    ]);
    
    // Add files using the File Finder
    $factory->addFiles(\Iwm\MarkdownStructure\Utility\FilesFinder::findFilesByPath('/var/www/html/Docs')));
    ```
5. Customize validators and file parsers as needed:
    ```php
    // Register parsers that execute when the markdown file is added to the MarkdownProjectFactory
    $factory->registerParserForAfterRegistration([
        new SplitByEmptyLineParser(),
        new HeadlinesToSectionParser(),
        new RemoveDevSections(),
        new CombineTextAndImagesParser(),
        new CombineTextAndListParser(),
    ]);
    
    // Register validators that execute for each markdown and media file once the create method is called
    $factory->registerValidators([
        new ImageValidator(),
        new LinkValidator(),
        new MarkdownValidator(),
    ]);
    
    // Register finishers that execute after the files are validated and errors are collected but before the project is created
    $factory->registerFinisher([
        new MarkdownToHTMLParser(),
        new ParagraphToContainerParser(),
        new SectionsToHtmlParser()
    ]);
    
    // Register finishers that execute after the project is created
    $factory->registerProjectValidators([
        new YourMarkdownProjectValidator()
    ]);
    $factory->registerProjectFinishers([
        new YourMarkdownProjectFinisher()
    ]);
    ```
6. Create your markdown-structure project:
    ```php
    $project = $factory->create();
    ```

### Additional Options

You can disable the nested structure of the project by setting the `enableNestedStructure` property to `false`:
```php
$factory->enableNestedStructure = false;
```
