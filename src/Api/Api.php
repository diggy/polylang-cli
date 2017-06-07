<?php

namespace Polylang_CLI\Api;

if ( ! class_exists( 'Polylang_CLI\Api\Api' ) ) {

class Api {

    protected static $path     = null;

    protected $functions       = array();
    protected $exceptions      = array();
    protected $debug_backtrace = null;

    public function __construct( $path )
    {
        self::$path = $path;

        $this->functions = $this->functions();

        $this->exceptions = array(
            'pll__',
            'pll_e',
            'pll_esc_html__',
            'pll_esc_attr__',
            'pll_esc_html_e',
            'pll_esc_attr_e',
            'PLL'
        );
    }



    public function __call( $func, $args )
    {
        if ( ! in_array( $func, $this->functions ) )
            return \WP_CLI::error( "$func is not a Polylang API function." );

        $this->debug_backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 );

        \WP_CLI::debug(
            sprintf(
                "Calling %s::__call('%s') from %s::%s with %d args: %s",
                $this->debug_backtrace[1]['class'],
                $this->debug_backtrace[1]['function'],
                $this->debug_backtrace[2]['class'],
                $this->debug_backtrace[2]['function'],
                count( $args ),
                json_encode( $args )
            ),
            __NAMESPACE__
        );

        // \WP_CLI::debug(  ( new \Exception() )->getTraceAsString() );

        $func = in_array( $func, $this->exceptions ) ? $func : "pll_$func";

        return is_callable( $func ) ? call_user_func_array( $func, $args ) : \WP_CLI::error( "$func is not callable." );
    }

    public function functions()
    {
        $functions = array();

        foreach ( $raw = self::functions_raw() as $i => $func ) {

            if ( in_array( $func, $this->exceptions ) )
                continue;

            $functions[$i] = str_replace( 'pll_', '', $func );
        }

        $functions = array_merge( $functions, $this->exceptions );

        \WP_CLI::debug( sprintf( "Made available %d Polylang API functions.", count( $functions ) ), __NAMESPACE__ );

        return $functions;
    }

    public static function functions_raw()
    {
        $content = file_get_contents( self::$path );

        preg_match_all( "/(function )(\S*\()/", $content, $matches ); // @todo fixme

        return array_map( 'rtrim', $matches[2], array_fill( 0, count( $matches[2] ), '(' ) );
    }

    public static function functions_xref()
    {
        return array(
             'pll_the_languages'
            ,'pll_current_language'
            ,'pll_default_language'
            ,'pll_get_post'
            ,'pll_get_term'
            ,'pll_home_url'
            ,'pll_register_string'
            ,'pll__'
            ,'pll_esc_html__'
            ,'pll_esc_attr__'
            ,'pll_e'
            ,'pll_esc_html_e'
            ,'pll_esc_attr_e'
            ,'pll_translate_string'
            ,'pll_is_translated_post_type'
            ,'pll_is_translated_taxonomy'
            ,'pll_languages_list'
            ,'pll_set_post_language'
            ,'pll_set_term_language'
            ,'pll_save_post_translations'
            ,'pll_save_term_translations'
            ,'pll_get_post_language'
            ,'pll_get_term_language'
            ,'pll_get_post_translations'
            ,'pll_get_term_translations'
            ,'pll_count_posts'
            ,'PLL'
        );
    }

}

}
