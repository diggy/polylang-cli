<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\MenuCommand' ) ) {

/**
 * Manage the WP Nav Menus.
 *
 * @package Polylang_CLI
 */
class MenuCommand extends BaseCommand {

    /**
	 * Create a new menu for each language, AND assign it to a location.
	 *
	 * ## OPTIONS
	 *
	 * <menu-name>
	 * : A descriptive name for the menu.
	 *
     * <location>
	 * : Location’s slug.
	 *
	 * [--porcelain]
	 * : Output just the new menu ids.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp pll menu create "Primary Menu" primary
	 *     Success: Assigned location to menu.
	 *     Success: Assigned location to menu.
	 *     Success: Assigned location to menu.
     *
     *     $ wp pll menu create "Secondary Menu" secondary --porcelain
	 *     21 22 23
     *
     * @when init
	 */
    public function create( $args, $assoc_args ) {

        list( $menu_name, $location ) = $args;

        $post_ids = array();

        $languages = wp_list_pluck( $this->pll->model->get_languages_list(), 'slug' );

        foreach ( $languages as $slug ) {

            ob_start();

            $menu_name_i18n = ( $slug === $this->api->default_language() ) ? $menu_name : "$menu_name ($slug)";

            $this->cli->command( array( 'menu', 'create', $menu_name_i18n ), array( 'porcelain' => true ) );

            $post_id = ob_get_clean();

            $location_i18n = ( $slug === $this->api->default_language() ) ? $location : "{$location}___{$slug}";

            $this->cli->runcommand(
                sprintf( 'menu location assign %d %s', $post_id, $location_i18n ),
                array( 'return' => $this->cli->flag( $assoc_args, 'porcelain' ), 'launch' => false, 'exit_error' => false )
            );

            $post_ids[] = $post_id;
        }

        if ( $this->cli->flag( $assoc_args, 'porcelain' ) ) {
            echo implode( ' ', array_map( 'absint', $post_ids ) );
        }
    }

}

}
