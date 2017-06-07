<?php

namespace Polylang_CLI\Traits;

if ( ! trait_exists( 'Polylang_CLI\Traits\Utils' ) ) {

trait Utils {

    /**
     * Gets term ID by slug.
     *
     * @access protected
     * @param  string $slug The language code (slug) to get the term ID for.
     * @return boolean|int Term ID on success, zero on failure.
     */
    protected function get_lang_id_by_slug( $slug ) {

        $languages = wp_list_pluck( $this->pll->model->get_languages_list(), 'term_id', 'slug' );

        return isset( $languages[$slug] ) ? (int) $languages[$slug] : 0;
    }

}

}
