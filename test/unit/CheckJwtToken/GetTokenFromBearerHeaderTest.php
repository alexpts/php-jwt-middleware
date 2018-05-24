<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PTS\CheckJwtToken\CheckJwtToken;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::getTokenFromBearerHeader()
 */
class GetTokenFromBearerHeaderTest extends TestCase
{
    /**
     * @param null|string $expected
     * @param array $header
     *
     * @throws ReflectionException
     *
     * @dataProvider dataProvider
     */
    public function testMethod(?string $expected, array $header): void
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->setMethods(['getHeader'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request->expects(self::once())->method('getHeader')->with('Authorization')->willReturn($header);

        $middleware = $this->createMock(CheckJwtToken::class);

        $method = new ReflectionMethod(CheckJwtToken::class, 'getTokenFromBearerHeader');
        $method->setAccessible(true);
        $actual = $method->invoke($middleware, $request);

        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            [null, [' ']],
            ['aaa', ['Bearer aaa']],
            [null, ['NotBearer bbb']],
        ];
    }
}
