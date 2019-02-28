<?php
declare(strict_types=1);

namespace Nerbiz\UrlEditor\Tests;

use Nerbiz\UrlEditor\UrlEditor;
use PHPUnit\Framework\TestCase;

class UrlEditorTest extends TestCase
{
    /**
     * Invalid URLs should throw an exception
     * @return void
     */
    public function testNeedsValidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UrlEditor('invalid-url');
    }

    /**
     * See if the input URL comes out as the same URL
     * @return void
     */
    public function testOutputsSameUrl(): void
    {
        $url = 'https://example.com/';
        $urlEditor = new UrlEditor($url);

        $this->assertEquals($url, $urlEditor->getFull());
    }
}
