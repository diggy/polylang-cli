<?php

namespace Polylang_CLI\Traits;

if ( ! trait_exists( 'Polylang_CLI\Traits\Properties' ) ) {

trait Properties {

    protected $cli = null;
    protected $pll = null;
    protected $api = null;

    protected $taxonomy      = 'language';
    protected $taxonomy_term = 'term_language';

    protected $options_default = array();
    protected $options_sync    = array();
    protected $options_cpt     = array();

    protected $fields_term = array(
        'term_id',
        'term_taxonomy_id',
        'name',
        'slug',
        'description',
        'parent',
        'count',
        'term_group',
    );

    protected $fields_language = array(
        'term_id',
        'name',
        'slug',
        'term_group',
        'term_taxonomy_id',
        'taxonomy',
        'description',
        'parent',
        'count',
        'tl_term_id',
        'tl_term_taxonomy_id',
        'tl_count',
        'locale',
        'is_rtl',
        'flag_url',
        'flag',
        'home_url',
        'search_url',
        'host',
        'mo_id',
        'page_on_front',
        'page_for_posts',
        'filter',
        'flag_code',
    );

}

}
