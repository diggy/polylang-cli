Feature: Manage Polylang languages

  Background:
    Given a WP install

  Scenario: Get a language

    When I run `wp pll lang get nl --format=json`
    Then STDOUT should contain:
      """
      {"term_id":2,"name":"Dutch","slug":"nl","term_group":0,"term_taxonomy_id":2,"taxonomy":"language","description":"a:3:{s:6:\"locale\";s:5:\"nl_NL\";s:3:\"rtl\";i:0;s:9:\"flag_code\";s:0:\"\";}","parent":0,"count":2,"filter":"raw"}
      """

      When I run `wp pll lang url nl`
      Then STDOUT should contain:
        """
        http://polylang-cli.dev/nl/
        """
