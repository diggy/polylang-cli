@pll-lang
Feature: Manage Polylang languages

  Background:
    Given a WP install
    And an empty cache
    And I run `wp core version`
    And save STDOUT as {WP_VERSION}
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  @core-language
  Scenario: Language CRUD commands

    When I run `wp pll lang create afrikaans af af`
    Then STDOUT should contain:
      """
      Success: Language added.
      Downloading translation from https://downloads.wordpress.org/translation/core/{WP_VERSION}/af.zip...
      Unpacking the update...
      Installing the latest version...
      Translation updated successfully.
      Success: Language installed.
      """

    When I try `wp pll lang create Nederlands nl nl_NL`
    Then STDERR should be:
      """
      Error: The language code must be unique
      """

    When I try `wp pll lang create "Nederlands (BE)" nl-be nl_NL`
    Then STDOUT should contain:
      """
      Success: Language added.
      """
    And STDERR should be:
      """
      Warning: Language 'nl_NL' already installed.
      """
    And the return code should be 0

    When I try `wp pll lang create Klingon klingon tlh`
    Then STDOUT should contain:
      """
      Success: Language added.
      """
    And STDERR should be:
      """
      Error: Language 'tlh' not found.
      """
    And the return code should be 0

    When I run `wp pll lang get nl --format=json`
    Then STDOUT should contain:
      """
      {"term_id":2,"name":"Dutch","slug":"nl","term_group":0,"term_taxonomy_id":2,"taxonomy":"language","description":"a:3:{s:6:\"locale\";s:5:\"nl_NL\";s:3:\"rtl\";i:0;s:9:\"flag_code\";s:0:\"\";}","parent":0,"count":2,"filter":"raw"}
      """

    When I run `wp pll lang url nl-be`
    Then STDOUT should contain:
      """
      http://example.com/?lang=nl-be
      """

    When I run `wp pll lang url nl`
    Then STDOUT should contain:
      """
      http://example.com/
      """

    When I run `wp pll option update hide_default 0`
    And I run `wp pll lang url nl`
    Then STDOUT should contain:
      """
      http://example.com/?lang=nl
      """

    When I run `wp pll lang delete klingon`
    Then STDOUT should contain:
      """
      Success: Language deleted. klingon (tlh)
      Success: 1 of 1 languages deleted
      """
    And STDERR should be:
      """
      Error: Language not installed.
      """

    When I run `wp pll lang delete --all --keep_default`
    Then STDOUT should contain:
      """
      Notice: Keeping default language nl (nl_NL).
      Success: Language deleted. af (af)
      Success: Language uninstalled.
      Success: Language deleted. nl-be (nl_NL)
      """
    And STDERR should be:
      """
      Warning: 2 of 3 languages deleted
      """
    And the return code should be 0

    When I run `wp pll lang delete --all`
    Then STDOUT should contain:
      """
      Success: Language deleted. nl (nl_NL)
      Success: Language uninstalled.
      Success: 1 of 1 languages deleted
      """
