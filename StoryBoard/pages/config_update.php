<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'AccessLevel' ) );
form_security_validate( 'plugin_StoryBoard_config_update' );

require_once STORYBOARD_CORE_URI . 'constant_api.php';
require_once STORYBOARD_CORE_URI . 'config_api.php';
require_once STORYBOARD_CORE_URI . 'db_api.php';
$config_api = new config_api();
$db_api = new db_api();

$option_change = gpc_get_bool( 'change', false );
$option_addtype = gpc_get_bool( 'addtype', false );
$option_deltype = gpc_get_bool( 'deletetype', false );
$option_changetype = gpc_get_bool( 'changetype', false );

/**
 * Submit configuration changes
 */
if ( $option_change )
{
   $config_api->updateValue( 'AccessLevel', ADMINISTRATOR );
   $config_api->updateButton( 'ShowInFooter' );
}

/**
 * Add a document type
 */
if ( $option_addtype )
{
   if ( isset( $_POST['type'] ) )
   {
      $db_api->insertTypeRow( $_POST['type'] );
   }
}

/**
 * Delete a document type
 */
if ( $option_deltype )
{
   if ( isset( $_POST['types'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $db_api->getTypeId( $type_string );

      $db_api->deleteTypeRow( $type_string );
   }
}

/**
 * Change a document type
 */
if ( $option_changetype )
{
   if ( isset( $_POST['types'] ) && isset( $_POST['newtype'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $db_api->getTypeId( $type_string );
      $new_type_string = $_POST['newtype'];

      $db_api->updateTypeRow( $type_id, $new_type_string );
   }
}

form_security_purge( 'plugin_StoryBoard_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );