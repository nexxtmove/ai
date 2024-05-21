<?php

use Illuminate\Support\Facades\Http;
use Nexxtmove\AI;

beforeEach(function () {
    Http::fake();
});

test('Facade works', function () {
    $response = AI::ask('How are you?');

    expect($response)->toBeNull();
});
