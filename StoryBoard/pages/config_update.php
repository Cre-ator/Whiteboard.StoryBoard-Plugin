<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );
form_security_validate( 'plugin_StoryBoard_config_update' );

require_once STORYBOARD_CORE_URI . 'constant_api.php';
require_once STORYBOARD_CORE_URI . 'config_api.php';
$config_api = new config_api();

$config_api->updateValue( 'AccessLevel', ADMINISTRATOR );
$config_api->updateButton( 'ShowInFooter' );

form_security_purge( 'plugin_StoryBoard_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );