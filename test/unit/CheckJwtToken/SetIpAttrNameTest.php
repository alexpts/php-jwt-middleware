<?php

use PHPUnit\Framework\TestCase;
use PTS\CheckJwtToken\CheckJwtToken;
use PTS\JwtService\JwtService;

/**
 * @covers \PTS\CheckJwtToken\CheckJwtToken::setIpAttr()
 */
class SetIpAttrNameTest extends TestCase
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
        $return = $this->middleware->setIpAttr($attr);
        self::assertInstanceOf(CheckJwtToken::class, $return);

        $prop = new ReflectionProperty(CheckJwtToken::class, 'ipAttr');
        $prop->setAccessible(true);
        $actual = $prop->getValue($this->middleware);
        self::assertSame($expected, $actual);
    }

    public function dataProvider(): array
    {
        return [
            ['ip', 'ip'],
            ['client-ip', 'client-ip'],
        ];
    }

}
