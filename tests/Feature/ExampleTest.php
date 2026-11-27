<?php

test('guest diarahkan ke halaman login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
