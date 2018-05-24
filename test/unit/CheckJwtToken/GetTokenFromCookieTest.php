<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PTS\CheckJwtToken\CheckJwtToken;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::getTokenFromCookie()
 */
class GetTokenFromCookieTest extends TestCase
{
    /**
     * @param null|string $expected
     * @param null|string $ip
     * @param array $cookies
     *
     * @throws ReflectionException
     *
     * @dataProvider dataProvider
     */
    public function testMethod(?string $expected, ?string $ip, array $cookies): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->setMethods(['getAttribute', 'getCookieParams'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects(self::once())->method('getAttribute')->with('client-ip')->willReturn($ip);
        $request->expects(self::once())->method('getCookieParams')->willReturn($cookies);

        $middleware = $this->createMock(CheckJwtToken::class);

        $method = new ReflectionMethod(CheckJwtToken::class, 'getTokenFromCookie');
        $method->setAccessible(true);
        $actual = $method->invoke($middleware, $request);

        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            [null, null, []],
            [null, '127.0.0.1', []],
            ['aaa', null, ['auth_token_' => 'aaa']],
            ['bbb', '127.0.0.1', ['auth_token_127.0.0.1' => 'bbb']],
            [null, '127.0.0.1', ['auth_token_127.1.1.1' => 'ccc']],
        ];
    }
}
