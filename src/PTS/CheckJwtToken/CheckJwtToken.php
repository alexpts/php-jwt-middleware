<?php
declare(strict_types=1);

namespace PTS\CheckJwtToken;

use Emarref\Jwt\Token;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PTS\JwtService\JwtService;

class CheckJwtToken implements MiddlewareInterface
{

    /** @var string */
    protected $tokenAttr = 'token';
    /** @var string */
    protected $ipAttr = 'client-ip';
    /** @var string */
    protected $cookieName = 'auth_token';
    /** @var JwtService */
    protected $jwtService;
    /** @var bool */
    protected $checkIp = true;
    /** @var bool */
    protected $cookieNameWithIp = true;

    /**
     * @param JwtService $jwtService
     */
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @param bool $checkIp
     *
     * @return $this
     */
    public function setCheckIp(bool $checkIp): self
    {
        $this->checkIp = $checkIp;
        return $this;
    }

    /**
     * @param string $cookieName
     *
     * @return $this
     */
    public function setCookieName(string $cookieName): self
    {
        $this->cookieName = $cookieName;
        return $this;
    }

    /**
     * @param bool $cookieNameWithIp
     *
     * @return $this
     */
    public function setCookieNameWithIp(bool $cookieNameWithIp): self
    {
        $this->cookieNameWithIp = $cookieNameWithIp;
        return $this;
    }

    /**
     * @param string $attr
     *
     * @return $this
     */
    public function setIpAttr(string $attr): self
    {
        $this->ipAttr = $attr;
        return $this;
    }

    /**
     * @param string $attr
     *
     * @return $this
     */
    public function setTokenAttr(string $attr): self
    {
        $this->tokenAttr = $attr;
        return $this;
    }

    /**
     * @inheritdoc
     * @throws TokenException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $jwtToken = $this->getTokenFromRequest($request);

        if (null === $jwtToken) {
            return $next->handle($request);
        }

        try {
            $token = $this->jwtService->decode($jwtToken);
            $this->jwtService->verify($token);

            $clientIp = $request->getAttribute($this->ipAttr);
            $this->checkTokenIp($token, $clientIp);

            $request = $request->withAttribute($this->tokenAttr, $token);
        } catch (\Exception $e) {
            throw new TokenException('Bad token', 401, $e);
        }

        return $next->handle($request);
    }

    /**
     * @param Token $token
     * @param string|null $clientIp
     *
     * @throws TokenException
     */
    protected function checkTokenIp(Token $token, string $clientIp = null): void
    {
        if ($this->checkIp) {
            $tokenIp = $this->getIpFromToken($token);

            if ($tokenIp && $clientIp !== $tokenIp) {
                throw new TokenException('Token not valid for this ip');
            }
        }
    }

    protected function getIpFromToken(Token $token): ?string
    {
        $tokenIp = $token->getPayload()->findClaimByName('ip');
        return $tokenIp !== null ? $tokenIp->getValue() : null;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return null|string
     */
    protected function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        return $request->hasHeader('Authorization')
            ? $this->getTokenFromBearerHeader($request)
            : $this->getTokenFromCookie($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return null|string
     */
    protected function getTokenFromBearerHeader(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeader('Authorization');
        list($type, $value) = explode(' ', $header[0]);

        return $type === 'Bearer' ? $value : null;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return null|string
     */
    protected function getTokenFromCookie(ServerRequestInterface $request): ?string
    {
        $cookiesData = $request->getCookieParams();
        $cookieName = $this->getCookieName($request);

        return $cookiesData[$cookieName] ??  null;
    }

    protected function getCookieName(ServerRequestInterface $request): string
    {
        if (!$this->cookieNameWithIp) {
            return $this->cookieName;
        }

        return $this->cookieName.'_'.$request->getAttribute($this->ipAttr);
    }
}
