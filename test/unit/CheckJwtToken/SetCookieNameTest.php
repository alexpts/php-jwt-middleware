<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::setCookieName()
 */
class SetCookieNameTest extends TestCase
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
     * @param string $name
     * @param string $expected
     *
     * @dataProvider dataProvider
     *
     * @throws ReflectionException
     */
    public function testMethod(string $name, string $expected): void
    {
        $return = $this->middleware->setCookieName($name);
        self::assertInstanceOf(CheckJwtToken::class, $return);

        $prop = new ReflectionProperty(CheckJwtToken::class, 'cookieName');
        $prop->setAccessible(true);
        $actual = $prop->getValue($this->middleware);
        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            ['auth', 'auth'],
            ['auth_back', 'auth_back'],
        ];
    }

}
