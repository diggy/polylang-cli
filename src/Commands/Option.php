<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\OptionCommand' ) ) {

/**
 * Inspect and manage Polylang settings.
 *
 * @package Polylang_CLI
 */
class OptionCommand extends BaseCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->options_default = \PLL_Install::get_default_options();

        # get list of syncable items (array key = input name, array value = translated item name)
        $this->options_sync    = \PLL_Settings_Sync::list_metas_to_sync();
    }

    /**
     * List Polylang settings.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Accepted values: table, csv, json, count, yaml. Default: table
     *
     * ## EXAMPLES
     *
     *     $ wp pll option list
     *     $ wp pll option list --format=csv
     *
     * @subcommand list
     */
    public function list_( $args, $assoc_args ) {

        $option = get_option( 'polylang' );

        if ( empty( $option ) ) {
            $this->cli->error( 'The option `polylang` is empty or does not exist.' );
        }

        $items = array();

        foreach ( $option as $key => $value ) {
            $obj               = new \stdClass();
            $obj->option_name  = $key;
            $obj->option_value = $value;
            $items[]           = $obj;
        }

        $formatter = $this->cli->formatter( $assoc_args, array( 'option_name', 'option_value' ) );

        $formatter->display_items( $items );
    }

    /**
     * Reset Polylang settings.
     *
     * ## EXAMPLES
     *
     *     $ wp pll option reset
     */
    public function reset( $args, $assoc_args ) {

        $option = get_option( 'polylang' );

        if ( empty( $option ) ) {
            $this->cli->error( 'The option `polylang` is empty or does not exist.' );
        }

        # get default options
        $options = $this->options_default;

        # get default language @todo review
        $options['default_lang'] = isset( $option['default_lang'] )
            ? $option['default_lang']
            : wp_list_pluck( $this->pll->model->get_languages_list(), 'slug' )[0];

        # set the options
        $this->pll->model->options = $options;

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( $options['default_lang'] );

        # success!
        $this->cli->success( 'Reset the `polylang` option to factory settings.' );
    }

    /**
     * Get Polylang settings.
     *
     * ## OPTIONS
     *
     * <option_name>
     * : Option name. Use the options subcommand to get a list of accepted values. Required.
     *
     * [--format=<format>]
     * : Get value in a particular format.
     * ---
     * default: var_export
     * options:
     *   - var_export
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *     $ wp pll option get default_lang
     */
    public function get( $args, $assoc_args ) {

        # get Polylang options
        $option = get_option( 'polylang' );

        # check if option exists
        if ( empty( $option ) ) {
            $this->cli->error( 'The option `polylang` is empty or does not exist.' );
        }

        # check if valid option name
        if ( ! in_array( $args[0], array_merge( array_keys( $this->options_default ), array( 'default_lang' ) ) ) ) {
            $this->cli->error( sprintf( 'Invalid option name: %s', $args[0] ) );
        }

        $this->cli->print_value( $option[$args[0]], $assoc_args );
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
     *     $ wp pll option update default_lang nl
     */
    public function update( $args, $assoc_args ) {

        # get Polylang options
        $option = get_option( 'polylang' );

        # check if option exists
        if ( empty( $option ) ) {
            $this->cli->error( 'The option `polylang` is empty or does not exist.' );
        }

        # check if valid option name
        if ( ! in_array( $args[0], array_merge( array_keys( $this->options_default ), array( 'default_lang' ) ) ) ) {
            $this->cli->error( sprintf( 'Invalid option name: %s', $args[0] ) );
        }

        # disallow changing PLL version
        if ( 'version' === $args[0] ) {
            $this->cli->error( "You're not allowed to change the Polylang version." );
        }

        # change default language
        if ( 'default_lang' === $args[0] ) {
            unset( $args[0] );
            return $this->default_( array_values( $args ) );
        }

        # update Polylang options
        // update_option( 'polylang', array_merge( $option, array( $args[0] => $args[1] ) ) );
        $this->pll->model->options = array_merge( $option, array( $args[0] => $args[1] ) );
        $this->pll->model->update_default_lang( $this->api->default_language() );

        # success!
        $this->cli->success( sprintf( 'The value of %s was set to %s', $args[0], maybe_serialize( $args[1] ) ) );
    }

    /**
     * Gets or sets the default language.
     *
     * ## OPTIONS
     *
     * [<language-code>]
     * : Optional. The language code (slug) to set as default.
     *
     * ## EXAMPLES
     *
     *     $ wp pll option default
     *     $ wp pll option default nl
     *
     * @synopsis [<language-code>]
     *
     * @subcommand default
     */
    public function default_( $args, $assoc_args = array() )
    {
        if ( ! $languages = $this->pll->model->get_languages_list() ) {
            return $this->cli->warning( "No languages are currently configured." );
        }

        # if no language provided, return the default language
        if ( empty( $args ) ) {
            return $this->get( array( 'default_lang' ), array() );
        }

        # get the default language
        $default = $this->api->default_language();

        # sanitize user input
        $language = isset( $args[0] ) && $args[0] ? sanitize_title_with_dashes( $args[0] ) : false;

        # check if submitted language is already the default
        if ( empty( $language ) || $default === $language ) {
            return $this->cli->warning( "{$default} is currently the default language");
        }

        # check if submitted language is installed
        if ( ! in_array( $language, wp_list_pluck( $languages, 'slug' ) ) ) {
            $this->cli->error( "The language '$language' is currently not installed" );
        }

        # set the default language
        $this->pll->model->update_default_lang( $language );

        # this can't go wrong
        $this->cli->success( "Default language was set to {$language}" );
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
     *
     * @alias manage
     */
    public function sync( $args, $assoc_args ) {

        if ( $args[0] === 'all' ) {

            $this->pll->model->options['sync'] = array_keys( $this->options_sync );

            # update options, default category and nav menu locations
            $this->pll->model->update_default_lang( $this->api->default_language() );

            return $this->cli->success( 'Polylang `sync` option updated.' );
        }

        # get args as array
        $args = explode( ',', $args[0] );

        # validate args
        foreach ( $args as $key ) {
            if ( ! in_array( $key, array_keys( $this->options_sync ) ) ) {
                $this->cli->error( sprintf( 'Invalid key: %s', $key ) );
            }
        }

        # get current settings
        $settings = (array) $this->pll->model->options['sync'];

        # update current settings
        $settings = array_merge( $settings, $args );

        $this->pll->model->options['sync'] = $settings;

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( $this->api->default_language() );

        $this->cli->success( 'Polylang `sync` option updated.' );
    }

    /**
     * Disable post meta syncing across languages.
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
     * : Item, or comma-separated list of items, to unsync. Required.
     *
     * ## EXAMPLES
     *
     *     $ wp pll option unsync post_format,_wp_page_template
     *     Success: Polylang `sync` option updated.
     *
     * @alias unmanage
     */
    public function unsync( $args, $assoc_args ) {

        if ( $args[0] === 'all' ) {

            $this->pll->model->options['sync'] = array();

            # update options, default category and nav menu locations
            $this->pll->model->update_default_lang( $this->api->default_language() );

            return $this->cli->success( 'Polylang `sync` option updated.' );
        }

        # get args as array
        $args = explode( ',', $args[0] );

        # validate args
        foreach ( $args as $i => $key ) {
            if ( ! in_array( $key, array_keys( $this->options_sync ) ) ) {
                unset( $args[$i] );
                $this->cli->warning( sprintf( 'Invalid key: %s', $key ) );
            }
        }

        # get current settings
        $settings = (array) $this->pll->model->options['sync'];

        # update current settings
        $settings = array_diff( $settings, $args );

        $this->pll->model->options['sync'] = array_values( $settings );

        # update options, default category and nav menu locations
        $this->pll->model->update_default_lang( $this->api->default_language() );

        $this->cli->success( 'Polylang `sync` option updated.' );
    }

}

}
