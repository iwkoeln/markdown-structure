# iwm/markdown-structure

[![Version](https://img.shields.io/badge/Version-0.0.x-blue.svg)](https://bitbucket.org/iwm/markdown-structure/)

## Description

The iwm/markdown-structure is a PHP library that scans a specific folder for documentation files in Markdown format (MD) and provides their structure. It also includes functionality to parse the Markdown files and validate references (links to other files/images) contained within them. The generated documentation can be published on Bitbucket.

## Features

- Finder class that collects all project files and adds them to the ProjectFiles using the `setFiles` method.
  - Set files by path: `$factory->loadFilesByPath('/your/root/project/path');`
- Additional support for external repositories (dependencies like the Developer Guide) can be specified in the constructor. The root of the external repository will be used as the documentation root instead of the project root.
- Updated constructor:
  - BasePath - Must be set.
  - ProjectPath - Initially implemented as a setter. If not set, the BasePath will be used.
    - Defines the starting point of the Markdown project.
    - NOTE: The ProjectPath must be set AFTER the BasePath, NOT before it.
- Error classes:
  - Images should be placed in the "img" folder.
- Validator classes:
  - Validators should be made extensible.
  - Images should be placed in the "img" folder.
- MarkdownMediaFile:
  - Only the target should be specified, without the "image" parameter.

## Usage

To use the `markdown-structure` library in your PHP project, follow these steps:

1. Install the library using Composer by running the following command:
    ```bash
    composer require iwm/markdown-structure
    ```
2. Import the necessary classes and namespaces:
   ```php
    use Iwm\MarkdownStructure\MarkdownProjectFactory;
    ```
3. Create a new instance of the `MarkdownProjectFactory` class:
    ```php
    $rootPathOfYourProject = '/var/www/html';
    $pathToYourDocumentation = '/var/www/html/tests/Data';
    $pathToYouDocIndexFile = '/var/www/html/tests/Data/index.md';
    $fallbackUrl = 'https://bitbucket.org/iwm/markdown-structure/src/master/';
    
    $factory = new MarkdownProjectFactory(
        $rootPathOfYourProject, 
        $fallbackUrl, 
        $pathToYourDocumentation, 
        $pathToYouDocIndexFile
    );
    ```
4. Customize validators and file-parser:
    ```php
    $factory->enableValidation = true;
    $factory->addValidators([
        new ImageValidator(),
        new LinkValidator(),
        new MarkdownValidator(),
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
    ```
5. Create your markdown-structure project:
    ```php
    $project = $factory->create();
    ```

### Additional Options

You can disable the nested structure of the project by setting the `enableNestedStructure` property to `false`:
```php
$factory->enableNestedStructure = false;
```

## Contributing

Contributions are welcome! If you would like to contribute to the iwm/markdown-structure project, please follow these steps:

1. Fork the repository and clone it to your local machine.
2. Create a new branch for your feature or bug fix.
3. Make your changes and test them thoroughly.
4. Please also update the Tests and Readme files if necessary.
5. Commit your changes and push them to your forked repository.
6. Submit a pull request describing your changes and why they should be merged.

Thank you for your contributions!
