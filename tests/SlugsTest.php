<?php
declare(strict_types=1);

namespace Nerbiz\UrlEditor\Tests;

use Nerbiz\UrlEditor\Exceptions\InvalidSlugsException;
use Nerbiz\UrlEditor\Properties\Slugs;
use PHPUnit\Framework\TestCase;

class SlugsTest extends TestCase
{
    /**
     * Invalid parameters type should throw an exception
     * @return void
     * @throws InvalidSlugsException
     */
    public function testNeedsValidParametersType(): void
    {
        $this->expectException(InvalidSlugsException::class);

        new Slugs(5);
    }

    /**
     * See if a slugs string comes out as an expected array
     * @return void
     * @throws InvalidSlugsException
     */
    public function testStringOuputsExpectedArray(): void
    {
        $slugsString = '/test/test-two/3/foo/bar/';
        $expected = ['test', 'test-two', 3, 'foo', 'bar'];
        $slugs = new Slugs($slugsString);

        $this->assertEquals($expected, $slugs->toArray());
    }

    /**
     * See if an array comes out as an expected slugs string
     * @return void
     * @throws InvalidSlugsException
     */
    public function testArrayOuputsExpectedString(): void
    {
        $slugsArray = ['test', 'test-two', 3, 'foo', 'bar'];
        $expected = 'test/test-two/3/foo/bar';
        $slugs = new Slugs($slugsArray);

        $this->assertEquals($expected, $slugs->toString());
    }
}
