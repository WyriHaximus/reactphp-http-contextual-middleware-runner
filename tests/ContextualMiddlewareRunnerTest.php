<?php declare(strict_types=1);

namespace WyriHaximus\React\Tests\Http\Middleware;

use ApiClients\Tools\TestUtilities\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use function React\Promise\resolve;
use WyriHaximus\React\Http\Middleware\ContextualMiddlewareRunner;

/**
 * @internal
 */
final class ContextualMiddlewareRunnerTest extends TestCase
{
    public function testRejectWithResponse(): void
    {
        $cmr = new ContextualMiddlewareRunner(function (ServerRequestInterface $request) {
            return false;
        }, [function (): void {
            $this->fail('The first middleware should never be reached');
        }]);

        /** @var ResponseInterface $response */
        $response = $this->await($cmr($this->prophesize(ServerRequestInterface::class)->reveal(), function () {
            return new Response(321);
        }));

        self::assertSame(321, $response->getStatusCode());
    }

    public function testRejectWithResponseInPromise(): void
    {
        $cmr = new ContextualMiddlewareRunner(function (ServerRequestInterface $request) {
            return false;
        }, [function (): void {
            $this->fail('The first middleware should never be reached');
        }]);

        /** @var ResponseInterface $response */
        $response = $this->await($cmr($this->prophesize(ServerRequestInterface::class)->reveal(), function () {
            return resolve(new Response(321));
        }));

        self::assertSame(321, $response->getStatusCode());
    }

    public function testFulfill(): void
    {
        $cmr = new ContextualMiddlewareRunner(function (ServerRequestInterface $request) {
            return true;
        }, [function (ServerRequestInterface $request, $next) {
            /** @var ResponseInterface $response */
            $response = $next($request);

            return $response->withStatus(123);
        }]);

        /** @var ResponseInterface $response */
        $response = $this->await($cmr($this->prophesize(ServerRequestInterface::class)->reveal(), function () {
            return new Response(321);
        }));

        self::assertSame(123, $response->getStatusCode());
    }
}
