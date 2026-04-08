<?php

use PHPUnit\Framework\TestCase;

class AdminClassTest extends TestCase {

    public function test_helpers_is_declared_property() {
        $ref = new ReflectionClass( AdminClass::class );
        $this->assertTrue(
            $ref->hasProperty( 'helpers' ),
            'helpers must be explicitly declared to avoid PHP 8.2+ deprecation'
        );
    }

    public function test_helpers_is_instance_of_helpers_after_construction() {
        $admin = new AdminClass();
        $ref   = new ReflectionClass( $admin );
        $prop  = $ref->getProperty( 'helpers' );
        $prop->setAccessible( true );
        $this->assertInstanceOf( Helpers::class, $prop->getValue( $admin ) );
    }
}
