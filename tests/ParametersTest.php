<?php
declare(strict_types=1);

namespace Nerbiz\UrlEditor\Tests;

use Nerbiz\UrlEditor\Exceptions\InvalidParametersException;
use Nerbiz\UrlEditor\Properties\Parameters;
use PHPUnit\Framework\TestCase;

class ParametersTest extends TestCase
{
    /**
     * Invalid parameters type should throw an exception
     * @return void
     * @throws InvalidParametersException
     */
    public function testNeedsValidParametersType(): void
    {
        $this->expectException(InvalidParametersException::class);

        new Parameters(5);
    }

    /**
     * See if a query string comes out as an expected array
     * @return void
     * @throws InvalidParametersException
     */
    public function testStringOuputsExpectedArray(): void
    {
        $queryString = '?test&test-two=2&3=foo&bar&';
        $expected = [
            'test' => null,
            'test-two' => 2,
            3 => 'foo',
            'bar' => null,
        ];
        $parameters = new Parameters($queryString);

        $this->assertEquals($expected, $parameters->toArray());
    }

    /**
     * See if an array comes out as an expected query string
     * @return void
     * @throws InvalidParametersException
     */
    public function testArrayOuputsExpectedString(): void
    {
        $queryArray = [
            'test' => null,
            'test-two' => 2,
            3 => 'foo',
            'bar' => null,
        ];
        $expected = 'test=&test-two=2&3=foo&bar=';
        $parameters = new Parameters($queryArray);

        $this->assertEquals($expected, $parameters->toString());
    }
}
