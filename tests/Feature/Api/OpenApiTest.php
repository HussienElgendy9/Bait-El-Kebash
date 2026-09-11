<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

class OpenApiTest extends ApiTestCase
{
    public function test_every_versioned_route_and_method_has_a_contract(): void
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1/')) {
                continue;
            }
            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $path = '/'.$route->uri();
                $this->assertArrayHasKey($path, $spec['paths']);
                $this->assertArrayHasKey(strtolower($method), $spec['paths'][$path]);
            }
        }
        foreach ($spec['paths'] as $path => $operations) {
            foreach ($operations as $method => $operation) {
                if ($method === 'post' && $path === '/api/v1/admin/products/{product}') {
                    continue;
                } // Documented multipart method override.
                $found = collect(Route::getRoutes()->getRoutes())->contains(fn ($route) => '/'.$route->uri() === $path && in_array(strtoupper($method), $route->methods(), true));
                $this->assertTrue($found, $method.' '.$path.' has no route');
            }
        }
    }

    public function test_catalog_response_matches_documented_required_fields(): void
    {
        $spec = Yaml::parseFile(base_path('docs/api/openapi.yaml'));
        $product = $this->product();
        $data = $this->get('/api/v1/products/'.$product->id)->assertOk()->json('data');
        $required = $spec['components']['schemas']['Product']['required'];
        $this->assertEqualsCanonicalizing($required, array_keys($data));
        $this->assertMatchesRegularExpression('/'.$spec['components']['schemas']['Money']['pattern'].'/', $data['unit_price']);
    }
}
