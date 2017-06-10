@pll-plugin
Feature: Manage the Polylang plugin

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: Uninstall the Polylang plugin

    When I try `wp pll plugin uninstall`
    Then STDERR should be:
      """
      Error: The Polylang plugin could not be uninstalled due to the plugin's settings. Use --force to override.
      """
    And the return code should be 1

    When I run `wp pll plugin uninstall --skip-delete --force`
    Then STDOUT should contain:
      """
      Deactivating 'polylang'...
      Plugin 'polylang' deactivated.
      Ran uninstall procedure for 'polylang' plugin without deleting.
      Success: Uninstalled 1 of 1 plugins.
      """
    And the return code should be 0

    When I try `wp pll lang list`
    Then STDERR should contain:
      """
      Error: This WP-CLI command requires the Polylang plugin: wp plugin install polylang && wp plugin activate polylang
      """
    And the return code should be 1

    When I run `wp eval 'var_dump(get_option('polylang'));'`
    Then STDOUT should contain:
      """
      bool(false)
      """

    When I run `wp plugin activate polylang`
    Then STDOUT should not be empty

    When I try `wp pll lang list --format=count`
    Then STDOUT should contain:
      """
      0
      """

    When I run `wp pll plugin uninstall --force`
    Then STDOUT should contain:
      """
      Deactivating 'polylang'...
      Plugin 'polylang' deactivated.
      Uninstalled and deleted 'polylang' plugin.
      Success: Uninstalled 1 of 1 plugins.
      """
    And the return code should be 0
