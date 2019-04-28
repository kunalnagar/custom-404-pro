<?php
/**
 * Custom404Pro Core Functions
 *
 * General core functions available on both the front-end and admin.
 *
 * @package Custom404Pro\Functions
 * @version 3.2.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 3.2.7
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function wc_maybe_define_constant( $name, $value ) {
    if ( ! defined( $name ) ) {
        define( $name, $value );
    }
}