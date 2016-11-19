<?php

namespace Polylang_CLI\Api;

class Cli {

    public function run_command( $args, $assoc_args = array() )
    {
        \WP_CLI::run_command( $args, $assoc_args );
    }

    /**
     * Gets WP_CLI flag value
     *
     * @param
     * @return
     */
    public function get_flag_value( $assoc_args, $flag, $default = null ) {

        return \WP_CLI\Utils\get_flag_value( $assoc_args, $flag, $default );
    }

    /**
     * Make WP_CLI progress bar
     *
     * @param
     * @return
     */
    public function progress_bar( $message, $count ) {
        return \WP_CLI\Utils\make_progress_bar( $message, $count );
    }

    /**
     * Gets WP_CLI formatter
     *
     * @param
     * @return
     */
    public function get_formatter( &$assoc_args, $fields = null, $prefix = false ) {

        return new \WP_CLI\Formatter( $assoc_args, $fields, $prefix );
    }

    public function success( $message )
    {
        \WP_CLI::success( $message );
    }

    public function warning( $message )
    {
        \WP_CLI::warning( $message );
    }

    public function error( $message )
    {
        \WP_CLI::error( $message );
    }

}
