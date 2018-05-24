<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::setTokenAttr()
 */
class SetTokenAttrTest extends TestCase
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
     * @param string $attr
     * @param string $expected
     *
     * @dataProvider dataProvider
     *
     * @throws ReflectionException
     */
    public function testMethod(string $attr, string $expected): void
    {
        $return = $this->middleware->setTokenAttr($attr);
        self::assertInstanceOf(CheckJwtToken::class, $return);

        $prop = new ReflectionProperty(CheckJwtToken::class, 'tokenAttr');
        $prop->setAccessible(true);
        $actual = $prop->getValue($this->middleware);
        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            ['token', 'token'],
            ['client-token', 'client-token'],
        ];
    }

}
