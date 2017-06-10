@pll-flag
Feature: Manage Polylang flags

  Background:
    Given a WP install
    And I run `wp plugin install polylang`
    And I run `wp plugin activate polylang`
    And I run `wp pll lang create Dutch nl nl_NL`
    And I run `wp pll doctor translate`

  Scenario: Set flag for language

    When I run `wp pll flag set nl nl`
    Then STDOUT should contain:
      """
      Success: Language updated.
      """

    When I run `wp pll flag set nl ""`
    Then STDOUT should contain:
      """
      Success: Language updated.
      """

    When I run `wp pll flag set foo nl`
    Then STDERR should contain:
      """
      Error: Invalid language code. Run `wp pll lang list --field=locale` to get a list of valid language codes.
      """
    And the return code should be 0

    When I run `wp pll flag set nl foo`
    Then STDERR should contain:
      """
      Error: The flag does not exist
      """
    And the return code should be 0

  Scenario: List flags

    When I run `wp pll flag list`
    Then STDOUT should contain:
      """
      file	name
      ad	Andorra
      ae	United Arab Emirates
      af	Afghanistan
      ag	Antigua and Barbuda
      ai	Anguilla
      al	Albania
      am	Armenia
      an	Netherlands Antilles
      ao	Angola
      ar	Argentina
      arab	Arab league
      as	American Samoa
      at	Austria
      au	Australia
      aw	Aruba
      ax	Åland Islands
      az	Azerbaijan
      ba	Bosnia and Herzegovina
      basque	Basque Country
      bb	Barbados
      bd	Bangladesh
      be	Belgium
      bf	Burkina Faso
      bg	Bulgaria
      bh	Bahrain
      bi	Burundi
      bj	Benin
      bm	Bermuda
      bn	Brunei
      bo	Bolivia
      br	Brazil
      bs	Bahamas
      bt	Bhutan
      bw	Botswana
      by	Belarus
      bz	Belize
      ca	Canada
      catalonia	Catalonia
      cc	Cocos
      cd	Democratic Republic of the Congo
      cf	Central African Republic
      cg	Congo
      ch	Switzerland
      ci	Ivory Coast
      ck	Cook Islands
      cl	Chile
      cm	Cameroon
      cn	China
      co	Colombia
      cr	Costa Rica
      cu	Cuba
      cv	Cape Verde
      cx	Christmas Island
      cy	Cyprus
      cz	Czech Republic
      de	Germany
      dj	Djibouti
      dk	Denmark
      dm	Dominica
      do	Dominican Republic
      dz	Algeria
      ec	Ecuador
      ee	Estonia
      eg	Egypt
      eh	Western Sahara
      england	England
      er	Eritrea
      es	Spain
      esperanto	Esperanto
      et	Ethiopia
      fi	Finland
      fj	Fiji
      fk	Falkland Islands
      fm	Micronesia
      fo	Faroe Islands
      fr	France
      ga	Gabon
      galicia	Galicia
      gb	United Kingdom
      gd	Grenada
      ge	Georgia
      gh	Ghana
      gi	Gibraltar
      gl	Greenland
      gm	Gambia
      gn	Guinea
      gp	Guadeloupe
      gq	Equatorial Guinea
      gr	Greece
      gs	South Georgia and the South Sandwich Islands
      gt	Guatemala
      gu	Guam
      gw	Guinea-Bissau
      gy	Guyana
      hk	Hong Kong
      hm	Heard Island and McDonald Islands
      hn	Honduras
      hr	Croatia
      ht	Haiti
      hu	Hungary
      id	Indonesia
      ie	Republic of Ireland
      il	Israel
      in	India
      io	British Indian Ocean Territory
      iq	Iraq
      ir	Iran
      is	Iceland
      it	Italy
      jm	Jamaica
      jo	Jordan
      jp	Japan
      ke	Kenya
      kg	Kyrgyzstan
      kh	Cambodia
      ki	Kiribati
      km	Comoros
      kn	Saint Kitts and Nevis
      kp	North Korea
      kr	South Korea
      kurdistan	Kurdistan
      kw	Kuwait
      ky	Cayman Islands
      kz	Kazakhstan
      la	Laos
      lb	Lebanon
      lc	Saint Lucia
      li	Liechtenstein
      lk	Sri Lanka
      lr	Liberia
      ls	Lesotho
      lt	Lithuania
      lu	Luxembourg
      lv	Latvia
      ly	Libya
      ma	Morocco
      mc	Monaco
      md	Moldova
      me	Montenegro
      mg	Madagascar
      mh	Marshall Islands
      mk	Macedonia
      ml	Mali
      mm	Myanmar
      mn	Mongolia
      mo	Macao
      mp	Northern Mariana Islands
      mq	Martinique
      mr	Mauritania
      ms	Montserrat
      mt	Malta
      mu	Mauritius
      mv	Maldives
      mw	Malawi
      mx	Mexico
      my	Malaysia
      mz	Mozambique
      na	Namibia
      nc	New Caledonia
      ne	Niger
      nf	Norfolk Island
      ng	Nigeria
      ni	Nicaragua
      nl	Netherlands
      no	Norway
      np	Nepal
      nr	Nauru
      nu	Niue
      nz	New Zealand
      occitania	Occitania
      om	Oman
      pa	Panama
      pe	Peru
      pf	French Polynesia
      pg	Papua New Guinea
      ph	Philippines
      pk	Pakistan
      pl	Poland
      pm	Saint Pierre and Miquelon
      pn	Pitcairn
      pr	Puerto Rico
      ps	Palestinian Territory
      pt	Portugal
      pw	Belau
      py	Paraguay
      qa	Qatar
      quebec	Quebec
      ro	Romania
      rs	Serbia
      ru	Russia
      rw	Rwanda
      sa	Saudi Arabia
      sb	Solomon Islands
      sc	Seychelles
      scotland	Scotland
      sd	Sudan
      se	Sweden
      sg	Singapore
      sh	Saint Helena
      si	Slovenia
      sk	Slovakia
      sl	Sierra Leone
      sm	San Marino
      sn	Senegal
      so	Somalia
      sr	Suriname
      ss	South Sudan
      st	São Tomé and Príncipe
      sv	El Salvador
      sy	Syria
      sz	Swaziland
      tc	Turks and Caicos Islands
      td	Chad
      tf	French Southern Territories
      tg	Togo
      th	Thailand
      tibet	Tibet
      tj	Tajikistan
      tk	Tokelau
      tl	Timor-Leste
      tm	Turkmenistan
      tn	Tunisia
      to	Tonga
      tr	Turkey
      tt	Trinidad and Tobago
      tv	Tuvalu
      tw	Taiwan
      tz	Tanzania
      ua	Ukraine
      ug	Uganda
      us	United States
      uy	Uruguay
      uz	Uzbekistan
      va	Vatican
      vc	Saint Vincent and the Grenadines
      ve	Venezuela
      veneto	Veneto
      vg	British Virgin Islands
      vi	United States Virgin Islands
      vn	Vietnam
      vu	Vanuatu
      wales	Wales
      wf	Wallis and Futuna
      ws	Western Samoa
      ye	Yemen
      yt	Mayotte
      za	South Africa
      zm	Zambia
      zw	Zimbabwe
      """
