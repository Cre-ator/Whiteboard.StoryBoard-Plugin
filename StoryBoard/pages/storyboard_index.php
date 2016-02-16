<?php
require_once STORYBOARD_CORE_URI . 'db_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_print_api.php';

$db_api = new db_api();
$print_api = new print_api();


$print_api->print_page_head( plugin_lang_get( 'menu_title' ) );

$project_spec_bug_ids = $db_api->get_bugarray_by_project( helper_get_current_project() );
var_dump( $project_spec_bug_ids );

html_page_bottom1();
