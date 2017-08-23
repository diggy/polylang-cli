@pll-option
Feature: Manage Polylang settings

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: Get the default language code (slug)

    When I run `wp pll option get default_lang`
    Then STDOUT should be:
      """
      nl
      """
