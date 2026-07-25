<?php

namespace App\Tests\EventListener;

use App\Tests\BaseWebTestCase;

class SecurityHeadersListenerTest extends BaseWebTestCase
{
    public function testSecurityHeadersArePresentOnApiResponses(): void
    {
        $this->client->request('GET', '/api/lists');

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertSame("default-src 'none'", $response->headers->get('Content-Security-Policy'));
    }

    public function testNoHstsHeaderOutsideProd(): void
    {
        $this->client->request('GET', '/api/lists');

        $this->assertFalse($this->client->getResponse()->headers->has('Strict-Transport-Security'));
    }
}
