<?php

use App\Models\User;

test('example page redirects guests to login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('authenticated users can view the dashboard', function () {
    $user = User::factory()->create();

    actingAsApiUser($user)
        ->get(route('dashboard'))
        ->assertOk();
});
