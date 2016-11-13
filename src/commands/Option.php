<?php

namespace Polylang_CLI\Commands;

/**
 * Class Option
 *
 * @package Polylang_CLI
 */
class Option extends BaseCommand
{
    /**
     * List Polylang settings
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     wp pll option list
     *     wp pll option list --format=csv
     *
     * @subcommand list
     * @alias export
     */
    public function list_( $args, $assoc_args ) {

        $option = get_option( 'polylang' );

        if ( empty( $option ) ) {
            return \WP_CLI::error( 'The option `polylang` is empty or does not exist.' );
        }

        $formatter = new \WP_CLI\Formatter( $assoc_args, array_keys( $option ) );

        $formatter->display_items( array( (object) $option ) );
    }

    /**
     * Reset Polylang settings
     *
     * ## EXAMPLES
     *
     *     wp pll option reset
     */
    public function reset( $args, $assoc_args ) {

        $option = get_option( 'polylang' );

        if ( empty( $option ) ) {
            return \WP_CLI::error( 'The option `polylang` is empty or does not exist.' );
        }

        # get default options
        $options = \PLL_Install::get_default_options();

        # get default language
        $options['default_lang'] = isset( $option['default_lang'] )
            ? $option['default_lang']
            : wp_list_pluck( $this->pll->model->get_languages_list(), 'slug' )[0];

        # set the options
        $this->pll->model->options = $options;

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( $options['default_lang'] );

        # success!
        return \WP_CLI::success( 'Reset the `polylang` option to factory settings.' );
    }

    /**
     * Get Polylang settings.
     *
     * ## OPTIONS
     *
     * <option_name>
     * : Option name. Use the options subcommand to get a list of accepted values. Required.
     *
     * ## EXAMPLES
     *
     *     wp pll option get default_lang
     */
    public function get( $args, $assoc_args ) {

        # get Polylang options
        $option = get_option( 'polylang' );

        # check if option exists
        if ( empty( $option ) ) {
            return \WP_CLI::error( 'The option `polylang` is empty or does not exist.' );
        }

        # check if valid option name
        if ( ! in_array( $args[0], array_merge( array_keys( \PLL_Install::get_default_options() ), array( 'default_lang' ) ) ) ) {
            return \WP_CLI::error( sprintf( 'Invalid option name: %s', $args[0] ) );
        }

        return \WP_CLI::success( sprintf( 'The value of %s is %s', $args[0], maybe_serialize( $option[$args[0]] ) ) );
    }

    /**
     * Update Polylang settings.
     *
     * ## OPTIONS
     *
     * <option_name>
     * : Option name. Use the options subcommand to get a list of accepted values. Required.
     *
     * <new_value>
     * : New value for the option. Required.
     *
     * ## EXAMPLES
     *
     *     wp pll option update default_lang nl
     */
    public function update( $args, $assoc_args ) {

        # get Polylang options
        $option = get_option( 'polylang' );

        # check if option exists
        if ( empty( $option ) ) {
            return \WP_CLI::error( 'The option `polylang` is empty or does not exist.' );
        }

        # check if valid option name
        if ( ! in_array( $args[0], array_merge( array_keys( \PLL_Install::get_default_options() ), array( 'default_lang' ) ) ) ) {
            return \WP_CLI::error( sprintf( 'Invalid option name: %s', $args[0] ) );
        }

        # disallow changing PLL version
        if ( 'version' === $args[0] ) {
            return \WP_CLI::error( "You're not allowed to change the Polylang version." );
        }

        # change default language
        if ( 'default_lang' === $args[0] ) {
            unset( $args[0] );
            return $this->default_( array_values( $args ) );
        }

        # update Polylang options
        // update_option( 'polylang', array_merge( $option, array( $args[0] => $args[1] ) ) );
        $this->pll->model->options = array_merge( $option, array( $args[0] => $args[1] ) );
        $this->pll->model->update_default_lang( pll_default_language() );

        # success!
        return \WP_CLI::success( sprintf( 'The value of %s was set to %s', $args[0], maybe_serialize( $args[1] ) ) );
    }

    /**
     * Gets or sets the default language
     *
     * ## OPTIONS
     *
     * [<language-code>]
     * : Optional. The language code (slug) to set as default.
     *
     * ## EXAMPLES
     *
     *   wp polylang default
     *   wp polylang default nl
     *
     * @synopsis [<language-code>]
     *
     * @subcommand default
     */
    public function default_( $args, $assoc_args = array() )
    {
        if ( ! $languages = $this->pll->model->get_languages_list() ) {
            return \WP_CLI::warning( "No languages are currently configured." );
        }

        # get the default language
        $default = pll_default_language();

        # if no language provided, return the default language
        if ( empty( $args ) ) {
            return \WP_CLI::success( "{$default} is currently the default language" );
        }

        # sanitize user input
        $language = isset( $args[0] ) && $args[0] ? sanitize_title_with_dashes( $args[0] ) : false;

        # check if submitted language is already the default
        if ( empty( $language ) || $default === $language ) {
            return \WP_CLI::warning( "{$default} is currently the default language");
        }

        # check if submitted language is installed
        if ( ! in_array( $language, wp_list_pluck( $languages, 'slug' ) ) ) {
            return \WP_CLI::error( "The language '$language' is currently not installed" );
        }

        # set the default language
        $this->pll->model->update_default_lang( $language );

        # this can't go wrong
        return \WP_CLI::success( "Default language was set to {$language}" );
    }

    /**
     * Enable post meta syncing across languages.
     *
     * Accepted values:
     *
     * * taxonomies
     * * post_meta
     * * comment_status
     * * ping_status
     * * sticky_posts
     * * post_date
     * * post_format
     * * post_parent
     * * _wp_page_template
     * * menu_order
     * * _thumbnail_id
     *
     * ## OPTIONS
     *
     * <item>
     * : Item, or comma-separated list of items, to sync. Required.
     *
     * ## EXAMPLES
     *
     *     $ wp pll option sync taxonomies,post_meta
     *     Success: Polylang `sync` option updated.
     */
    public function sync( $args, $assoc_args ) {

        # get args as array
        $args = explode( ',', $args[0] );

        # get list of syncable items (array key = input name, array value = translated item name)
        $syncable = \PLL_Settings_Sync::list_metas_to_sync();

        # validate args
        foreach ( $args as $key ) {
            if ( ! in_array( $key, array_keys( $syncable ) ) ) {
                return \WP_CLI::error( sprintf( 'Invalid key: %s', $key ) );
            }
        }

        # get current settings
        $settings = (array) $this->pll->model->options['sync'];

        # update current settings
        $settings = array_merge( $settings, array_combine( $args, array_fill( 1, count( $args ), 1 ) ) );

        $this->pll->model->options['sync'] = $settings;

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( pll_default_language() );

        \WP_CLI::success( 'Polylang `sync` option updated.' );
    }

}
