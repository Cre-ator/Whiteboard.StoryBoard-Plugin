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
$option_addpriority_level = gpc_get_bool( 'addpriority_level', false );
$option_delpriority_level = gpc_get_bool( 'deletepriority_level', false );
$option_changepriority_level = gpc_get_bool( 'changepriority_level', false );

/**
 * Submit configuration changes
 */
if ( $option_change )
{
   $config_api->updateValue( 'AccessLevel', ADMINISTRATOR );
   $config_api->updateButton( 'ShowInFooter' );
}

/**
 * Add a type
 */
if ( $option_addtype )
{
   if ( isset( $_POST['type'] ) )
   {
      $db_api->insertAttribute( $_POST['type'], 'type' );
   }
}

/**
 * Delete a type
 */
if ( $option_deltype )
{
   if ( isset( $_POST['types'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $db_api->selectAttributeidByAttribute( $type_string, 'type' );

      $db_api->deleteAttribute( $type_string, 'type' );
   }
}

/**
 * Change a type
 */
if ( $option_changetype )
{
   if ( isset( $_POST['types'] ) && isset( $_POST['newtype'] ) )
   {
      $type_string = $_POST['types'];
      $type_id = $db_api->selectAttributeidByAttribute( $type_string, 'type' );
      $new_type_string = $_POST['newtype'];

      $db_api->updateAttribute( $type_id, $new_type_string, 'type' );
   }
}

/**
 * Add a priority_level
 */
if ( $option_addpriority_level )
{
   if ( isset( $_POST['priority_level'] ) )
   {
      $db_api->insertAttribute( $_POST['priority_level'], 'priority' );
   }
}

/**
 * Delete a priority_level
 */
if ( $option_delpriority_level )
{
   if ( isset( $_POST['priority_levels'] ) )
   {
      $priority_level_string = $_POST['priority_levels'];
      $priority_level_id = $db_api->selectAttributeidByAttribute( $priority_level_string, 'priority' );

      $db_api->deleteAttribute( $priority_level_string, 'priority' );
   }
}

/**
 * Change a priority_level
 */
if ( $option_changepriority_level )
{
   if ( isset( $_POST['priority_levels'] ) && isset( $_POST['newpriority_level'] ) )
   {
      $priority_level_string = $_POST['priority_levels'];
      $priority_level_id = $db_api->selectAttributeidByAttribute( $priority_level_string, 'priority' );
      $new_priority_level_string = $_POST['newpriority_level'];

      $db_api->updateAttribute( $priority_level_id, $new_priority_level_string, 'priority' );
   }
}

form_security_purge( 'plugin_StoryBoard_config_update' );

print_successful_redirect( plugin_page( 'config_page', true ) );