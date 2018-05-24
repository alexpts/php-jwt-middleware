<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PTS\CheckJwtToken\CheckJwtToken;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::getTokenFromRequest()
 */
class GetTokenFromRequestTest extends TestCase
{
    /**
     * @param bool $hasHeader
     *
     * @throws ReflectionException
     *
     * @dataProvider dataProvider
     */
    public function testMethod(bool $hasHeader): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->setMethods(['hasHeader'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects(self::once())->method('hasHeader')->with('Authorization')->willReturn($hasHeader);

        $middleware = $this->getMockBuilder(CheckJwtToken::class)
            ->setMethods(['getTokenFromBearerHeader', 'getTokenFromCookie'])
            ->disableOriginalConstructor()
            ->getMock();
        $middleware->expects(self::exactly($hasHeader ? 1 : 0))->method('getTokenFromBearerHeader')->with($request);
        $middleware->expects(self::exactly($hasHeader ? 0 : 1))->method('getTokenFromCookie')->with($request);

        $method = new ReflectionMethod(CheckJwtToken::class, 'getTokenFromRequest');
        $method->setAccessible(true);
        $method->invoke($middleware, $request);
    }

    public function dataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
