<?php

namespace Polylang_CLI\Commands;

/**
 * Inspect and manage Polylang string translations.
 *
 * @package Polylang_CLI
 */
class StringCommand extends BaseCommand {

    /**
     * List string translations.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     $ wp pll string list
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $strings = \PLL_Admin_Strings::get_strings();

        $keys = $items = array();

        foreach ( $strings as $key => $data ) {
            $keys     = array_merge( $keys, array_keys( $data ) );
            $obj      = (object) $data;
            $obj->key = $key;
            $items[]  = $obj;
        }

        $formatter = $this->cli->formatter( $assoc_args, array_merge( array( 'key' ), array_unique( $keys ) ) );

        $formatter->display_items( $items );
    }

}
