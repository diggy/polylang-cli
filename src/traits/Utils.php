<?php

namespace Polylang_CLI\Traits;

trait Utils {

    /**
     * Gets term ID by slug
     *
     * @access protected
     * @param  string        $slug The language code (slug) to get the term ID for.
     * @return boolean|array       Term ID on success, boolean false on failure.
     */
    protected function _get_term_id_by_slug( $slug ) {

        $languages = wp_list_pluck( $this->pll->model->get_languages_list(), 'term_id', 'slug' );

        return isset( $languages[$slug] ) ? $languages[$slug] : 0;
    }

    /**
     * Gets WP_CLI flag value
     *
     * @access private
     * @param
     * @return
     */
    protected function get_flag_value( $assoc_args, $flag, $default = null ) {

        return \WP_CLI\Utils\get_flag_value( $assoc_args, $flag, $default );
    }

    /**
     * Gets WP_CLI formatter
     *
     * @access private
     * @param
     * @return
     */
    protected function get_formatter( &$assoc_args ) {

        return new \WP_CLI\Formatter( $assoc_args, $this->fields_term, 'term' );
    }

}
