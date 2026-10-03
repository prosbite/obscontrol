<?php

use App\Models\User;

it('redirects guests from the root to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

it('redirects guests from control to login', function () {
    $response = $this->get('/control');

    $response->assertRedirect(route('login'));
});

it('renders the public display page for guests', function () {
    $response = $this->get('/display/main');

    $response->assertStatus(200);
});

it('redirects authenticated users from login to control', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/login');

    $response->assertRedirect('/control');
});

it('redirects authenticated users from the root to control', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/control');
});
