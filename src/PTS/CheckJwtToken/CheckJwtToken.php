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
    public function checkIp(bool $checkIp): self
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
            $tokenIp = $token->getPayload()->findClaimByName('ip');
            $tokenIp = $tokenIp !== null ? $tokenIp->getValue() : null;

            if ($tokenIp && $clientIp !== $tokenIp) {
                throw new TokenException('Token not valid for this ip');
            }
        }
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
        $clientIp = $request->getAttribute($this->ipAttr);
        $cookieName = $clientIp ? $this->cookieName.'_'.$clientIp : $this->cookieName;

        return $request->getCookieParams()[$cookieName] ?? null;
    }
}
