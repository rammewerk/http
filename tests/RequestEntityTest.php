<?php

namespace Rammewerk\Http\Tests;

use PHPUnit\Framework\TestCase;
use Rammewerk\Http\DecodeConfig;
use Rammewerk\Http\Request;
use Rammewerk\Http\Tests\Fixture\UserEntity;

class RequestEntityTest extends TestCase {



    public function testEntity(): void {
        $request = new Request(['name' => 'Kristoffer', 'age' => '30', 'email' => 'kristoffer@example.com']);
        $user = $request->decode(UserEntity::class);
        $this->assertInstanceOf(UserEntity::class, $user);
        $this->assertEquals('Kristoffer', $user->name);
        $this->assertEquals(30, $user->age);
        $this->assertEquals('kristoffer@example.com', $user->email);
    }



    public function testEntitySettings(): void {
        $request = new Request(['name_input' => 'Kristoffer', 'age' => '30', 'email' => 'kristoffer@example.com']);
        $user = $request->decode(UserEntity::class, function (DecodeConfig $settings) {
            $settings->assign('name_input', 'name');
            $settings->require('age');
            $settings->exclude('email');
        });
        $this->assertInstanceOf(UserEntity::class, $user);
        $this->assertEquals('Kristoffer', $user->name);
        $this->assertEquals(30, $user->age);
        $this->assertEquals('', $user->email);
    }



    public function testEntityMissingRequired(): void {
        $request = new Request(['name' => 'Kristoffer']);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required property: email');
        $request->decode(UserEntity::class, function (DecodeConfig $settings) {
            $settings->require('email');
        });
    }



    public function testEntityGuard(): void {
        $request = new Request(['name' => 'Kristoffer', 'age' => '30']);
        $user_1 = $request->decode(UserEntity::class);
        $this->assertEquals(30, $user_1->age);
        $user_2 = $request->decode(UserEntity::class, function (DecodeConfig $settings) {
            $settings->exclude('age');
        });
        $this->assertNull($user_2->age);
    }



    public function testEntityMap(): void {
        $request = new Request(['name_input' => 'Kristoffer', 'age_input' => '30']);
        $user_1 = $request->decode(UserEntity::class);
        $this->assertEmpty($user_1->name);
        $this->assertNull($user_1->age);
        $user_2 = $request->decode(UserEntity::class, function (DecodeConfig $settings) {
            $settings->assign('name_input', 'name');
            $settings->assign('age_input', 'age');
        });
        $this->assertNotEmpty($user_2->name);
        $this->assertNotNull($user_2->age);
    }


}