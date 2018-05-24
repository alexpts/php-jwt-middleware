<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::getCookieName()
 */
class GetCookieNameTest extends TestCase
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
     * @param string $expected
     * @param string $cookieName
     * @param bool $withIp
     *
     * @throws ReflectionException
     *
     * @dataProvider dataProvider
     */
    public function testMethod(string $expected, string $cookieName, bool $withIp): void
    {
        $ip = '127.0.0.1';

        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects(self::exactly((int)$withIp))->method('getAttribute')->with('client-ip')->willReturn($ip);

        $this->middleware
            ->setCookieName($cookieName)
            ->setCookieNameWithIp($withIp);

        $method = new ReflectionMethod(CheckJwtToken::class, 'getCookieName');
        $method->setAccessible(true);
        $actual = $method->invoke($this->middleware, $request);

        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            ['auth', 'auth', false],
            ['auth_127.0.0.1', 'auth', true],
            ['token_127.0.0.1', 'token', true],
        ];
    }
}
