<?php

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase {

    public function test_table_options_is_declared_property() {
        $ref = new ReflectionClass( Helpers::class );
        $this->assertTrue(
            $ref->hasProperty( 'table_options' ),
            'table_options must be explicitly declared to avoid PHP 8.2+ deprecation'
        );
    }

    public function test_table_logs_is_declared_property() {
        $ref = new ReflectionClass( Helpers::class );
        $this->assertTrue(
            $ref->hasProperty( 'table_logs' ),
            'table_logs must be explicitly declared to avoid PHP 8.2+ deprecation'
        );
    }

    public function test_options_defaults_is_declared_property() {
        $ref = new ReflectionClass( Helpers::class );
        $this->assertTrue(
            $ref->hasProperty( 'options_defaults' ),
            'options_defaults must be explicitly declared to avoid PHP 8.2+ deprecation'
        );
    }

    public function test_properties_have_correct_values_after_construction() {
        $helpers = new Helpers();
        $this->assertSame( 'custom_404_pro_options', $helpers->table_options );
        $this->assertSame( 'custom_404_pro_logs', $helpers->table_logs );
        $this->assertIsArray( $helpers->options_defaults );
        $this->assertNotEmpty( $helpers->options_defaults );
    }
}
