<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownParagraphToHTMLParser implements ParserInterface
{
    public function fileIsParsable(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile;
    }

    /**
     * @throws CommonMarkException
     */
    public function parse(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile
    {
        if (!$file instanceof MarkdownFile) {
            return $file;
        }

        $file->sectionedResult = $this->modifyAllSections($file->sectionedResult);

        return $file;
    }

    /**
     * @param array<Section|string> $sectionedResult
     *
     * @return array<Section|string>
     *
     * @throws CommonMarkException
     */
    private function modifyAllSections(array $sectionedResult): array
    {
        $result = [];

        foreach ($sectionedResult as $currentSection) {
            if ($currentSection instanceof Section) {
                $currentSection->content = $this->convertParagraphsToHtml($currentSection->content);
            }
            $result[] = $currentSection;
        }

        return $result;
    }

    /**
     * @param array<string> $paragraphs
     *
     * @return array<string>
     *
     * @throws CommonMarkException
     */
    private function convertParagraphsToHtml(array $paragraphs): array
    {
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());

        $htmlParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $htmlParagraphs[] = $parser->convert($paragraph);
        }

        return $htmlParagraphs;
    }
}
