<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::setCookieNameWithIp()
 */
class SetCookieNameWithIpTest extends TestCase
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
     * @param bool $withIp
     * @param bool $expected
     *
     * @dataProvider dataProvider
     *
     * @throws ReflectionException
     */
    public function testMethod(bool $withIp, bool $expected): void
    {
        $return = $this->middleware->setCookieNameWithIp($withIp);
        self::assertInstanceOf(CheckJwtToken::class, $return);

        $prop = new ReflectionProperty(CheckJwtToken::class, 'cookieNameWithIp');
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
