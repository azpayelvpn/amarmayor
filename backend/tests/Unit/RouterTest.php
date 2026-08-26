<?php

declare(strict_types=1);

namespace AmarMayor\Tests\Unit;

use AmarMayor\Http\Request;
use AmarMayor\Http\Response;
use AmarMayor\Http\Router;
use AmarMayor\Tests\TestCase;

class RouterTest extends TestCase
{
    public function testRouteMatching(): void
    {
        $router = new Router();
        $router->get('/test-route', function (Request $req) {
            return Response::html('OK_TEST');
        });

        $req = new Request('GET', '/test-route');
        $res = $router->dispatch($req);

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('OK_TEST', $res->getContent());
    }

    public function testRouteWithParameters(): void
    {
        $router = new Router();
        $router->get('/wards/{ward_number}', function (Request $req, $ward_number) {
            return Response::json(['ward' => $ward_number]);
        });

        $req = new Request('GET', '/wards/19');
        $res = $router->dispatch($req);

        $this->assertEquals(200, $res->getStatusCode());
        $data = json_decode($res->getContent(), true);
        $this->assertEquals('19', $data['data']['ward']);
    }

    public function test404NotFound(): void
    {
        $router = new Router();
        $req = new Request('GET', '/non-existent-page');
        $res = $router->dispatch($req);

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function test405MethodNotAllowed(): void
    {
        $router = new Router();
        $router->post('/submit', function () {
            return Response::html('POST_OK');
        });

        $req = new Request('GET', '/submit');
        $res = $router->dispatch($req);

        $this->assertEquals(405, $res->getStatusCode());
    }
}
