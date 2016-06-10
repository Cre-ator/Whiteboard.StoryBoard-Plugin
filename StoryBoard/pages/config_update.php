<?php
auth_reauthenticate ();
access_ensure_global_level ( config_get ( 'AccessLevel' ) );
form_security_validate ( 'plugin_StoryBoard_config_update' );

require_once STORYBOARD_CORE_URI . 'storyboard_constant_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_config_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_db_api.php';
$storyboard_config_api = new storyboard_config_api();
$storyboard_db_api = new storyboard_db_api();

$option_change = gpc_get_bool ( 'change', false );
$option_addtype = gpc_get_bool ( 'addtype', false );
$option_deltype = gpc_get_bool ( 'deletetype', false );
$option_changetype = gpc_get_bool ( 'changetype', false );

/**
 * Submit configuration changes
 */
if ( $option_change )
{
   $storyboard_config_api->updateValue ( 'access_level', ADMINISTRATOR );
   $storyboard_config_api->updateButton ( 'show_in_footer' );
   if ( !empty( $_POST[ 'status_cols' ] ) )
   {
      foreach ( $_POST[ 'status_cols' ] as $status_cols )
      {
         $status_cols = gpc_get_int_array ( 'status_cols' );
         if ( plugin_config_get ( 'status_cols' ) != $status_cols )
         {
            plugin_config_set ( 'status_cols', $status_cols );
         }
      }
   }
}

/**
 * Add a type
 */
if ( $option_addtype )
{
   if ( isset( $_POST[ 'type' ] ) )
   {
      $storyboard_db_api->insert_type ( $_POST[ 'type' ] );
   }
}

/**
 * Delete a type
 */
if ( $option_deltype )
{
   if ( isset( $_POST[ 'types' ] ) )
   {
      $type_string = $_POST[ 'types' ];
      $type_id = $storyboard_db_api->select_typeid_by_typestring ( $type_string );

      $storyboard_db_api->delete_type ( $type_string );
   }
}

/**
 * Change a type
 */
if ( $option_changetype )
{
   if ( isset( $_POST[ 'types' ] ) && isset( $_POST[ 'newtype' ] ) )
   {
      $type_string = $_POST[ 'types' ];
      $type_id = $storyboard_db_api->select_typeid_by_typestring ( $type_string );
      $new_type_string = $_POST[ 'newtype' ];

      $storyboard_db_api->update_type ( $type_id, $new_type_string );
   }
}

form_security_purge ( 'plugin_StoryBoard_config_update' );

print_successful_redirect ( plugin_page ( 'config_page', true ) );