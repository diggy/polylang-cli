<?php

namespace Polylang_CLI\Traits;

trait Utils {

    /**
     * Gets list of installed languages as PLL_Language objects
     *
     * @access protected
     * @return boolean|array
     */
     /*
    protected function _get_languages() {

        return $this->pll->model->get_languages_list();
    }
    */

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
