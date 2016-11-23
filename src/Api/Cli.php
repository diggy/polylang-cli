<?php

namespace Polylang_CLI\Api;

class Cli {

    /**
     * Runs a WP_CLI command.
     *
     * @param array $args Positional arguments including command name
     * @param array $assoc_args
     */
    public function command( $args, $assoc_args = array() ) {

        \WP_CLI::run_command( $args, $assoc_args );
    }

    /**
     * Return the flag value or, if it's not set, the $default value.
     *
     * Because flags can be negated (e.g. --no-quiet to negate --quiet), this
     * function provides a safer alternative to using
     * `isset( $assoc_args['quiet'] )` or similar.
     *
     * @access public
     *
     * @param array  $assoc_args  Arguments array.
     * @param string $flag        Flag to get the value.
     * @param mixed  $default     Default value for the flag. Default: NULL
     * @return mixed
     */
    public function flag( $assoc_args, $flag, $default = null ) {

        return \WP_CLI\Utils\get_flag_value( $assoc_args, $flag, $default );
    }

    /**
     * Create a progress bar to display percent completion of a given operation.
     *
     * Progress bar is written to STDOUT, and disabled when command is piped. Progress
     * advances with `$progress->tick()`, and completes with `$progress->finish()`.
     * Process bar also indicates elapsed time and expected total time.
     *
     * @access public
     *
     * @param string  $message  Text to display before the progress bar.
     * @param integer $count    Total number of ticks to be performed.
     * @return \WP_CLI\Utils\make_progress_bar
     */
    public function progress( $message, $count ) {

        return \WP_CLI\Utils\make_progress_bar( $message, $count );
    }

    /**
     * Gets WP_CLI formatter.
     *
     * @param array $assoc_args Output format arguments.
     * @param array $fields Fields to display of each item.
     * @param string $prefix Check if fields have a standard prefix.
     */
    public function formatter( &$assoc_args, $fields = null, $prefix = false ) {

        return new \WP_CLI\Formatter( $assoc_args, $fields, $prefix );
    }

    /**
     * Display success message prefixed with "Success: ".
     *
     * Success message is written to STDOUT.
     *
     * Typically recommended to inform user of successful script conclusion.
     *
     * @access public
     * @param string $message Message to write to STDOUT.
     * @return null
     */
    public function success( $message ) {

        \WP_CLI::success( $message );
    }

    /**
     * Display warning message prefixed with "Warning: ".
     *
     * Warning message is written to STDERR.
     *
     * Use instead of `WP_CLI::debug()` when script execution should be permitted
     * to continue.
     *
     * @access public
     * @param string $message Message to write to STDERR.
     * @return null
     */
    public function warning( $message ) {

        \WP_CLI::warning( $message );
    }

    /**
     * Display error message prefixed with "Error: " and exit script.
     *
     * Error message is written to STDERR. Defaults to halting script execution
     * with return code 1.
     *
     * Use `WP_CLI::warning()` instead when script execution should be permitted
     * to continue.
     *
     * @access public
     * @param string|WP_Error  $message Message to write to STDERR.
     * @param boolean|integer  $exit    True defaults to exit(1).
     * @return null
     */
    public function error( $message ) {

        \WP_CLI::error( $message );
    }

    /**
     * Display informational message without prefix.
     *
     * Message is written to STDOUT, or discarded when `--quiet` flag is supplied.
     *
     * ```
     * # `wp cli update` lets user know of each step in the update process.
     * WP_CLI::log( sprintf( 'Downloading from %s...', $download_url ) );
     * ```
     *
     * @access public
     * @param string $message Message to write to STDOUT.
     */
    public function log( $message ) {

        \WP_CLI::log( $message );
    }

}
