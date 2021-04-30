<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        Validator::make(['title' => 111], [
            'title' => 'required|max:255',
        ])->validate();

        $this->assertTrue(true);
    }
}
