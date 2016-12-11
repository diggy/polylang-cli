Feature: Troubleshoot Polylang

  Background:
    Given a WP install

  Scenario: Use doctor functionality

    When I run `wp pll doctor check`
    Then STDOUT should contain:
      """
      Success: All translatable post and term objects are assigned to a language.
      """

    When I run `wp pll doctor api`
    Then STDOUT should contain:
      """
      Success: There are no Polylang API changes.
      """
