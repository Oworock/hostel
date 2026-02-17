<?php

test('returns a successful response', function () {
    $response = $this->get('/');

    $this->assertContains($response->getStatusCode(), [200, 302]);
});
