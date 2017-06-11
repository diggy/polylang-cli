<?php

namespace Polylang_CLI\Commands;

if ( ! class_exists( 'Polylang_CLI\Commands\WidgetCommand' ) ) {

/**
 * Manage localized sidebar widgets.
 *
 * @package Polylang_CLI
 */
class WidgetCommand extends BaseCommand {

	/**
	 * List localized widgets associated with a sidebar.
	 *
	 * ## OPTIONS
	 *
	 * <language-code>
	 * : The language code (slug) to get widgets for. Required.
	 *
	 * <sidebar-id>
	 * : ID for the corresponding sidebar. Required.
	 *
	 * [--fields=<fields>]
	 * : Limit the output to specific object fields.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - ids
	 *   - json
	 *   - count
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each widget:
	 *
	 * * name
	 * * id
	 * * position
	 * * options
	 *
	 * There are no optionally available fields.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp pll widget list nl sidebar-1
	 *     +------+--------+----------+-------------------------------------------------------------------+
	 *     | name | id     | position | options                                                           |
	 *     +------+--------+----------+-------------------------------------------------------------------+
	 *     | text | text-2 | 2        | {"title":"test","text":"test","filter":"content","pll_lang":"nl"} |
	 *     +------+--------+----------+-------------------------------------------------------------------+
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {

		list( $language_slug, $sidebar_id ) = $args;

		# get widget fields
		$properties = new \ReflectionClass( new \Widget_Command() );
		$fields     = $properties->getDefaultProperties()['fields'];

		# validate sidebar
		$validate = new \ReflectionMethod( 'Widget_Command', 'validate_sidebar' );
		$validate->setAccessible( true );

		$validate->invokeArgs( new \Widget_Command(), array( $sidebar_id ) );

		# get sidebar widgets
		$validate = new \ReflectionMethod( 'Widget_Command', 'get_sidebar_widgets' );
		$validate->setAccessible( true );

		$output_widgets = $validate->invokeArgs( new \Widget_Command(), array( $sidebar_id ) );

		# filter widgets by language
		$output_widgets_filtered = array();

		foreach ( $output_widgets as $obj ) {
			if ( isset( $obj->options['pll_lang'] ) && $obj->options['pll_lang'] === $language_slug ) {
				$output_widgets_filtered[] = $obj;
			}
		}

		if ( ! empty( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			$output_widgets = wp_list_pluck( $output_widgets_filtered, 'id' );
		}

		$formatter = $this->cli->formatter( $assoc_args, $fields );
		$formatter->display_items( $output_widgets_filtered );
	}

}

}
