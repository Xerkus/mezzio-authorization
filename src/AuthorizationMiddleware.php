<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authorization;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;

use const Webimpress\HttpMiddlewareCompatibility\HANDLER_METHOD;

class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ResponseInterface
     */
    private $responsePrototype;

    public function __construct(AuthorizationInterface $authorization, ResponseInterface $responsePrototype)
    {
        $this->authorization = $authorization;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * {@inheritDoc}
     * @todo Use role/identity interface from mezzio-authentication once published.
     */
    public function process(ServerRequestInterface $request, HandlerInterface $handler)
    {
        $role = $request->getAttribute(AuthorizationInterface::class, false);

        if (false === $role) {
            return $this->responsePrototype->withStatus(401);
        }

        return $this->authorization->isGranted($role, $request)
            ? $handler->{HANDLER_METHOD}($request)
            : $this->responsePrototype->withStatus(403);
    }
}
