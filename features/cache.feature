@pll-cache
Feature: Manage Polylang cache

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: Clear Polylang cache

    When I run `wp pll cache clear`
    Then STDOUT should contain:
      """
      Success: Languages cache cleared.
      """
