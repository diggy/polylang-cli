<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\CacheCommand' ) ) {

/**
 * Inspect and manage Polylang languages cache.
 *
 * @package Polylang_CLI
 */
class CacheCommand extends BaseCommand {

    /**
     * Gets the Polylang languages cache.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     $ wp pll cache get --format=json
     *     Success: There are 1 items in the languages cache:
     *     [{"term_id":2,"name":"Nederlands","slug":"nl","term_group":0,"term_taxonomy_id":2,"taxonomy":"language","description":"nl_NL","parent":0,"count":6259,"tl_term_id":3,"tl_term_taxonomy_id":3,"tl_count":42,"locale":"nl_NL","is_rtl":0,"flag_url":"","flag":"","home_url":"http:\/\/example.dev\/nl\/","search_url":"http:\/\/example.dev\/nl\/","host":null,"mo_id":"3","page_on_front":false,"page_for_posts":false,"filter":"raw","flag_code":""}]
     *
     *     $ wp pll cache get --format=csv --quiet
     *     term_id,name,slug,term_group,term_taxonomy_id,taxonomy,description,parent,count,tl_term_id,tl_term_taxonomy_id,tl_count,locale,is_rtl,flag_url,flag,home_url,search_url,host,mo_id,page_on_front,page_for_posts,filter,flag_code
     *     2,Nederlands,nl,0,2,language,nl_NL,0,10,3,3,42,nl_NL,0,,,http://example.dev/nl/,http://example.dev/nl/,,3,,,raw,
     */
    public function get( $args, $assoc_args ) {

        $transient = get_transient( 'pll_languages_list' );

        $this->cli->success( sprintf( 'There are %d items in the languages cache:', count( (array) $transient ) ) );

        $formatter = $this->cli->formatter( $assoc_args, array_keys( $transient[0] ) );

        $formatter->display_items( $transient );
    }

    /**
     * Clears the Polylang languages cache.
     *
     * ## EXAMPLES
     *
     *     $ wp pll cache clear
     *     Success: Languages cache cleared.
     *
     *     $ wp pll cache clear --quiet
     *
     * @alias clean
     */
    public function clear() {

        $this->pll->model->clean_languages_cache();

        $this->cli->success( 'Languages cache cleared.' );
    }
}

}
