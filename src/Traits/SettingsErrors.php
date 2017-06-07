<?php

namespace Polylang_CLI\Traits;

if ( ! trait_exists( 'Polylang_CLI\Traits\SettingsErrors' ) ) {

trait SettingsErrors {

    /**
     * Gets WP settings errors.
     *
     * @access protected
     * @param  string $func WP_CLI method to run. Accepted values are `error` and `success`. Default: success.
     * @return array
     */
    protected function get_settings_errors( $func = 'success' ) {

        $func = ( $func == 'success' ) ? $func : 'error';

        $settings_errors = array();

        foreach ( \get_settings_errors( 'general' ) as $arr ) {

            $settings_errors[$func][] = $arr['message'];
        }

        return $settings_errors;
    }

    /**
     * Displays WP settings errors.
     *
     * @access protected
     * @param  string $func WP_CLI method to run. Accepted values are `error` and `success`. Default: success.
     * @return void
     */
    protected function settings_errors( $result = 'success' ) {

        foreach ( $this->get_settings_errors( $result ) as $func => $messages ) {

            foreach ( $messages as $message ) {

                $this->cli->$func( $message );
            }
        }
    }

    /**
     * Clears WP settings errors.
     *
     * @access protected
     * @return void
     */
    protected function clear_settings_errors() {

        $GLOBALS['wp_settings_errors'] = array();
    }

}

}
