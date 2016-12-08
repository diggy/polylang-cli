Feature: Manage Polylang languages

  Background:
    Given a WP install

  Scenario: Language CRUD commands

    When I run `wp pll lang create afrikaans af af`
    Then STDOUT should contain:
      """
      Success: Language added.
      Downloaden vertaling van https://downloads.wordpress.org/translation/core/{WP_VERSION}/af.zip...
      Uitpakken update...
      De laatste versie installeren...
      Vertaling succesvol bijgewerkt.
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
      Warning: Language already installed.
      """

    When I run `wp pll lang create Klingon klingon tlh`
    Then STDOUT should contain:
      """
      Success: Language added.
      Error: Language 'tlh' not found.
      """

    When I run `wp pll lang get nl --format=json`
    Then STDOUT should contain:
      """
      {"term_id":2,"name":"Dutch","slug":"nl","term_group":0,"term_taxonomy_id":2,"taxonomy":"language","description":"a:3:{s:6:\"locale\";s:5:\"nl_NL\";s:3:\"rtl\";i:0;s:9:\"flag_code\";s:0:\"\";}","parent":0,"count":2,"filter":"raw"}
      """

    When I run `wp pll lang url nl`
    Then STDOUT should contain:
      """
      http://example.com/?lang=nl
      """
