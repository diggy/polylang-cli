<?php
/**
 * CLI interface for the Polylang plugin.
 *
 * @author  Peter J. Herrel <peterherrel@gmail.com>
 * @package diggy/polylang-cli
 * @version 1.0.0-prealpha.1
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    require __DIR__ . '/vendor/autoload.php';

    // WP_CLI::add_command( 'pll',        Polylang_CLI\Cli::class );

    WP_CLI::add_command( 'pll cache',     Polylang_CLI\Commands\Cache::class );
    WP_CLI::add_command( 'pll doctor',    Polylang_CLI\Commands\Doctor::class );
    WP_CLI::add_command( 'pll flag',      Polylang_CLI\Commands\Flag::class );
    WP_CLI::add_command( 'pll lang',      Polylang_CLI\Commands\Lang::class );
    WP_CLI::add_command( 'pll option',    Polylang_CLI\Commands\Option::class );
    WP_CLI::add_command( 'pll post',      Polylang_CLI\Commands\Post::class );
    WP_CLI::add_command( 'pll post-type', Polylang_CLI\Commands\PostType::class );
    WP_CLI::add_command( 'pll taxonomy',  Polylang_CLI\Commands\Taxonomy::class );

}
