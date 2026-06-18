#
# Geteilte tt_content-Spalten aus dpx_template / dpx_container, die NICHT von
# einem Content Block verwaltet werden. Feldgleich zu den alten dpx-Extensions,
# damit die importierten Inhalte verlustfrei erhalten bleiben.
#
CREATE TABLE tt_content (
    # Header-Erweiterungen (dpx_template tt_content_header.php)
    header_kicker tinytext,
    tx_header_style tinytext,
    tx_header_inside tinyint(4) DEFAULT '0' NOT NULL,
    description tinytext,

    # dpxtemplate_dpx_download (0 aktive, aber Spalte erhalten)
    tx_dpxtemplate_file int(11) unsigned DEFAULT '0' NOT NULL,

    # Container (dpx_container) - auf b13/container portiert
    container_tab_open int(11) unsigned DEFAULT '1' NOT NULL,
    container_accordion_toggle_all int(11) unsigned DEFAULT '0' NOT NULL,
    container_accordion_toggle int(11) unsigned DEFAULT '0' NOT NULL,
    container_accordion_open int(11) unsigned DEFAULT '1' NOT NULL,
    container_headline tinytext,
    slider tinyint(4) DEFAULT '0' NOT NULL,
    slider_type tinytext,
    slider_style tinytext,
    slider_navigation tinytext,
    slider_arrows tinytext,
    slider_columns tinytext,
    grid_type tinytext,
    grid_columns tinytext,
    grid_bgimage int(11) unsigned DEFAULT '0' NOT NULL,
    grid_parallax tinyint(4) DEFAULT '0' NOT NULL,
    grid_imgbg tinyint(4) DEFAULT '0' NOT NULL,
    grid_bottom_image tinytext,
    grid_bgcolor varchar(10),
    grid_bgfullsize tinyint(4) DEFAULT '0' NOT NULL,
    grid_container tinyint(4) DEFAULT '0' NOT NULL,
    grid_light tinyint(4) DEFAULT '0' NOT NULL,
    columns_grid_col_1 tinytext,
    columns_grid_col_2 tinytext,
    columns_grid_col_3 tinytext,
    columns_grid_col_4 tinytext,
    grid_bggradient tinyint(2) DEFAULT '0' NOT NULL,
    tx_dpxcontainer_equal_heights tinyint(1) DEFAULT '0' NOT NULL
);

#
# Seiten-Zusatzfelder (dpx_template pages.php)
#
CREATE TABLE pages (
    newsletter varchar(255) DEFAULT '' NOT NULL,
    socialmedia varchar(255) DEFAULT '' NOT NULL,
    breadcrumb varchar(255) DEFAULT '' NOT NULL,
    event varchar(255) DEFAULT '0' NOT NULL,
    toggleEvent varchar(255) DEFAULT '' NOT NULL,
    event_location varchar(255) DEFAULT NULL,
    event_category varchar(255) DEFAULT NULL,
    event_startdate varchar(255) DEFAULT NULL,
    event_enddate varchar(255) DEFAULT NULL,
    event_address varchar(1024) DEFAULT NULL,
    event_signup_link varchar(255) DEFAULT NULL,
    event_signup_link_label varchar(255) DEFAULT NULL,
    event_past varchar(255) DEFAULT NULL,
    highlight tinyint(1) DEFAULT '0' NOT NULL,
    teaser_description text,
    teaser_description_overview text,
    category_title text
);
