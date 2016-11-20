Feature: Manage Polylang settings

  Background:
    Given a WP install

  Scenario: Get the default language code (slug)

    When I run `wp pll option get default_lang`
    Then STDOUT should contain:
      """
      Success: The value of default_lang is nl
      """
