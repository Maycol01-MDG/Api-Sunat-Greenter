<?php

use App\Http\Controllers\Api\DespatchController;

test('dispatch controller exposes xml and pdf methods', function () {
    $controller = new DespatchController();

    expect($controller)->toBeInstanceOf(DespatchController::class)
        ->and(method_exists($controller, 'xml'))->toBeTrue()
        ->and(method_exists($controller, 'pdf'))->toBeTrue();
});
