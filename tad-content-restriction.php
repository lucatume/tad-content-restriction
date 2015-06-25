<?php
/**
 * Plugin Name: TAD Content Restriction
 * Plugin URI: http://theAverageDev.com
 * Description: A Post content restriction framework.
 * Version: 1.0
 * Author: theAverageDev
 * Author URI: http://theAverageDev.com
 * License: GPL 2.0
 */

include 'vendor/autoload_52.php';

$plugin = trc_Plugin::instance();

$plugin->file = __FILE__;
$plugin->url  = plugins_url( '/', __FILE__ );

$plugin->query_vars          = trc_QueryVars::instance()->init();
$plugin->admin_page          = trc_AdminPage::instance()->init();
$plugin->template_restrictor = trc_TemplateRedirector::instance()->init();
$plugin->query_restrictor    = trc_QueryRestrictor::instance()->init();
