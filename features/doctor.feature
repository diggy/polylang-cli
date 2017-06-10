@pll-doctor
Feature: Troubleshoot Polylang

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

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
