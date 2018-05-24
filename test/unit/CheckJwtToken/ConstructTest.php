<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::__construct()
 */
class ConstructTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMethod(): void
    {
        $service = $this->createMock(JwtService::class);
        $middleware = new CheckJwtToken($service);
        $prop = new ReflectionProperty(CheckJwtToken::class, 'jwtService');
        $prop->setAccessible(true);
        $actual = $prop->getValue($middleware);
        self::assertInstanceOf(JwtService::class, $actual);
    }

}
