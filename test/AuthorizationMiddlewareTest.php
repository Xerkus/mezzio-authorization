<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authorization;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\AuthorizationMiddleware;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationMiddlewareTest extends TestCase
{
    protected function setUp()
    {
        $this->authorization = $this->prophesize(AuthorizationInterface::class);
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->response = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $middleware = new AuthorizationMiddleware($this->authorization->reveal(), $this->response->reveal());
        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware);
    }

    public function testProcessWithoutRoleAttribute()
    {
        $this->request->getAttribute(AuthorizationInterface::class, false)->willReturn(false);
        $this->response->withStatus(401)->will([$this->response, 'reveal']);

        $middleware = new AuthorizationMiddleware($this->authorization->reveal(), $this->response->reveal());

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testProcessRoleNotGranted()
    {
        $this->request->getAttribute(AuthorizationInterface::class, false)->willReturn('foo');
        $this->response->withStatus(403)->will([$this->response, 'reveal']);
        $this->authorization->isGranted('foo', $this->request->reveal())->willReturn(false);

        $middleware = new AuthorizationMiddleware($this->authorization->reveal(), $this->response->reveal());

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }

    public function testProcessRoleGranted()
    {
        $this->request->getAttribute(AuthorizationInterface::class, false)->willReturn('foo');
        $this->authorization->isGranted('foo', $this->request->reveal())->willReturn(true);

        $middleware = new AuthorizationMiddleware($this->authorization->reveal(), $this->response->reveal());
        $this->delegate->process(Argument::any())->willReturn($this->response->reveal());

        $response = $middleware->process(
            $this->request->reveal(),
            $this->delegate->reveal()
        );

        $this->assertSame($this->response->reveal(), $response);
    }
}
