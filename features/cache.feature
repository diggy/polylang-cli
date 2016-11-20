Feature: Manage Polylang cache

  Background:
    Given a WP install

  Scenario: Clear Polylang cache

    When I run `wp pll cache clear`
    Then STDOUT should contain:
      """
      Success: Languages cache cleared.
      """
