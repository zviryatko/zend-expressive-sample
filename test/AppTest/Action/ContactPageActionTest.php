<?php
/**
 * @file
 *
 */

namespace AppTest\Action;

use AppTest\WebTestCase;

class ContactPageActionTest extends WebTestCase
{
    public function testContactPageAccess()
    {
        $response = $this->handleRequest('GET', '/contact');
        $this->assertResponseHasStatus($response, 200);
    }
}
