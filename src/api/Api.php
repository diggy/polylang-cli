<?php

namespace Polylang_CLI\Api;

class Api {

    protected $path = null;
    protected $exceptions = array();

    public function __construct( $path )
    {
        $this->path = $path;
        $this->exceptions = array( 'pll__', 'pll_e', 'PLL' );
    }

    public function __call( $func, $args )
    {
        if ( ! in_array( $func, $this->functions() ) )
            return \WP_CLI::error( "$func is not a Polylang API function" );

        \WP_CLI::debug(
            sprintf(
                "Calling '%s' object method '%s' with args %s",
                ( new \ReflectionClass( $this ) )->getShortName(), $func, json_encode( $args )
            ),
            __NAMESPACE__
        );

        foreach ( $this->exceptions as $f ) {
            if ( $f == $func ) {
                return call_user_func_array( $f, $args );
            }
        }

        return call_user_func_array( "pll_$func", $args );
    }

    public function functions()
    {
        $functions = array();

        foreach ( $raw = $this->functions_raw() as $i => $func ) {

            if ( in_array( $func, $this->exceptions ) )
                continue;

            $functions[$i] = str_replace( 'pll_', '', $func );
        }

        $functions = array_merge( $functions, $this->exceptions );

        \WP_CLI::debug( sprintf( "Made available %d Polylang API functions", count( $functions ) ), __NAMESPACE__ );

        return $functions;
    }

    private function functions_raw()
    {
        // $content = file_get_contents( $this->path );
        // preg_match_all( "/(function )(\S*\()/", $content, $matches );
        // return array_map( 'rtrim', $matches[2], array_fill( 0, count( $matches[2] ), '(' ) );

        return array(
            'pll_the_languages',
            'pll_current_language',
            'pll_default_language',
            'pll_get_post',
            'pll_get_term',
            'pll_home_url',
            'pll_register_string',
            'pll__',
            'pll_e',
            'pll_translate_string',
            'pll_is_translated_post_type',
            'pll_is_translated_taxonomy',
            'pll_languages_list',
            'pll_set_post_language',
            'pll_set_term_language',
            'pll_save_post_translations',
            'pll_save_term_translations',
            'pll_get_post_language',
            'pll_get_term_language',
            'pll_get_post_translations',
            'pll_get_term_translations',
            'pll_count_posts',
            'PLL',
        );
    }

}
