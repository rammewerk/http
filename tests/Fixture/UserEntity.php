<?php

namespace Rammewerk\Http\Tests\Fixture;

class UserEntity {

    public function __construct(
        public readonly string $name = '',
        public ?int $age = null,
        public string $email = '',
    ) {}


}