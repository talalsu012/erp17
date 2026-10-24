<?php

it('answers unauthenticated api calls with the unified error format', function () {
    $this->getJson('/api/APITIQ/1')
        ->assertUnauthorized()
        ->assertExactJson(['status' => 'error', 'message' => 'غير مصرّح، يرجى تسجيل الدخول.']);
});

it('answers unknown api routes with the unified not found format', function () {
    $this->getJson('/api/does-not-exist')
        ->assertNotFound()
        ->assertExactJson(['status' => 'error', 'message' => 'غير موجود']);
});

it('keeps the standard validation error structure for api requests', function () {
    $this->postJson('/api/login', ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
});
