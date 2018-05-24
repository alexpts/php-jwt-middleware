<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::setCheckIp()
 */
class SetCheckIpTest extends TestCase
{
    /** @var CheckJwtToken */
    protected $middleware;

    /**
     * @throws ReflectionException
     */
    public function setUp()
    {
        parent::setUp();

        $service = $this->createMock(JwtService::class);
        $this->middleware =  new CheckJwtToken($service);
    }

    /**
     * @param bool $checkIP
     * @param bool $expected
     *
     * @dataProvider dataProvider
     *
     * @throws ReflectionException
     */
    public function testMethod(bool $checkIP, bool $expected): void
    {
        $return = $this->middleware->setCheckIp($checkIP);
        self::assertInstanceOf(CheckJwtToken::class, $return);

        $prop = new ReflectionProperty(CheckJwtToken::class, 'checkIp');
        $prop->setAccessible(true);
        $actual = $prop->getValue($this->middleware);
        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

}
