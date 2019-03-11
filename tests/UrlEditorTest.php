<?php
declare(strict_types=1);

namespace Nerbiz\UrlEditor\Tests;

use Nerbiz\UrlEditor\Exceptions\InvalidUrlException;
use Nerbiz\UrlEditor\UrlEditor;
use PHPUnit\Framework\TestCase;

class UrlEditorTest extends TestCase
{
    /**
     * Invalid URLs should throw an exception
     * @return void
     * @throws InvalidUrlException
     */
    public function testNeedsValidUrl(): void
    {
        $this->expectException(InvalidUrlException::class);

        new UrlEditor('invalid-url');
    }

    /**
     * See if the input URL comes out as the same URL
     * @return void
     * @throws InvalidUrlException
     */
    public function testOutputsSameUrl(): void
    {
        $url = 'https://example.com';
        $urlEditor = new UrlEditor($url);

        $this->assertEquals($url, $urlEditor->getFull());
    }

    /**
     * Completely change a URL with strings and see if it matches the expected output
     * @return void
     * @throws InvalidUrlException
     */
    public function testOutputsStringConstructedUrl(): void
    {
        $url = 'https://example.com';
        // Multiple lines for readability
        $expected = 'http://www.another-example.co.uk'
            . '/slug-1/slug-2'
            . '?param-1=value-1&empty=&another-empty=&param-2=value-2&special=%40+%25'
            . '#element-id';
        $urlEditor = new UrlEditor($url);

        $urlEditor->setIsSecure(false);
        $urlEditor->setDomainBase(' another-example ');
        $urlEditor->getSubdomains()->fromString(' .www. ');
        $urlEditor->getTld()->fromString(' .co.uk. ');
        $urlEditor->getSlugs()->fromString(' /slug-1/slug-2/ ');
        $urlEditor->getParameters()->fromString(
            '?param-1=value-1&empty=&another-empty=&param-2=value-2&special=%40+%25'
        );
        $urlEditor->getFragment()->fromString('element-id');

        $this->assertEquals($expected, $urlEditor->getFull());
    }

    /**
     * Completely change a URL with arrays and see if it matches the expected output
     * @return void
     * @throws InvalidUrlException
     */
    public function testOutputsArrayConstructedUrl(): void
    {
        $url = 'https://example.com';
        // Multiple lines for readability
        $expected = 'http://www.another-example.co.uk'
            . '/slug-1/slug-2'
            . '?param-1=value-1&empty=&another-empty=&param-2=value-2&special=%40+%25'
            . '#element-id';
        $urlEditor = new UrlEditor($url);

        $urlEditor->setIsSecure(false);
        $urlEditor->setDomainBase(' another-example ');
        $urlEditor->getSubdomains()->fromArray(['', ' .www. ', null]);
        $urlEditor->getTld()->fromArray(['', 'co', 'uk', null]);
        $urlEditor->getSlugs()->fromArray(['', 'slug-1', 'slug-2', null]);
        $urlEditor->getParameters()->fromArray([
            'param-1' => 'value-1',
            'empty' => null,
            'another-empty' => '',
            'param-2' => 'value-2',
            'special' => ' @+%',
        ]);
        $urlEditor->getFragment()->fromString('element-id');

        $this->assertEquals($expected, $urlEditor->getFull());
    }
}
