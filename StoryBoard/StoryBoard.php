<?php

class StoryBoardPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'Story Board';
      $this->description = '...';
      $this->page = 'config_page';

      $this->version = '1.0.3';
      $this->requires = array
      (
         'MantisCore' => '1.2.0, <= 1.3.99',
      );

      $this->author = 'Stefan Schwarz, Rainer Dierck';
      $this->contact = '';
      $this->url = '';
   }

   function hooks()
   {
      $hooks = array
      (
         'EVENT_LAYOUT_PAGE_FOOTER' => 'footer',
         'EVENT_MENU_MAIN' => 'menu',
         'EVENT_REPORT_BUG_FORM' => 'bugViewFields',
         'EVENT_UPDATE_BUG_FORM' => 'bugViewFields',
         'EVENT_VIEW_BUG_DETAILS' => 'bugViewFields',
         'EVENT_REPORT_BUG' => 'bugUpdateFields',
         'EVENT_UPDATE_BUG' => 'bugUpdateFields',
         'EVENT_BUG_DELETED' => 'deleteBugReference',
      );
      return $hooks;
   }

   function init()
   {
      $t_core_path = config_get_global( 'plugin_path' )
         . plugin_get_current()
         . DIRECTORY_SEPARATOR
         . 'core'
         . DIRECTORY_SEPARATOR;
      require_once( $t_core_path . 'storyboard_constant_api.php' );
   }

   function config()
   {
      return array
      (
         'AccessLevel' => ADMINISTRATOR,
         'ShowInFooter' => ON,
         'ShowMenu' => ON,

         'status_cols' => array(
            '0' => 20,
            '1' => 30,
            '2' => 40,
            '3' => 50
         ),
      );
   }

   function schema()
   {
      return array
      (
         array
         (
            'CreateTableSQL', array( plugin_table( 'card' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            bug_id          I       NOTNULL UNSIGNED,
            p_type_id       I       UNSIGNED,

            risk            C(250)  DEFAULT '',
            story_pt        C(250)  DEFAULT '',
            story_pt_post   C(250)  DEFAULT '',
            acc_crit        C(1000) DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'type' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            type            C(250)  NOTNULL DEFAULT ''
            " )
         )
      );
   }

   /**
    * Check if user has level greater or equal then plugin access level
    *
    * @return bool - Userlevel is greater or equal then plugin access level
    */
   function getUserHasLevel()
   {
      $project_id = helper_get_current_project();
      $user_id = auth_get_current_user_id();

      return user_get_access_level( $user_id, $project_id ) >= plugin_config_get( 'AccessLevel', PLUGINS_STORYBOARD_THRESHOLD_LEVEL_DEFAULT );
   }

   /**
    * Show plugin info in mantis footer
    *
    * @return null|string
    */
   function footer()
   {
      if ( plugin_config_get( 'ShowInFooter' ) && $this->getUserHasLevel() )
      {
         return '<address>' . $this->name . ' ' . $this->version . ' Copyright &copy; 2016 by ' . $this->author . '</address>';
      }
      return null;
   }

   /**
    * If the whiteboard menu plugin isnt installed, show the storyboard menu instead
    *
    * @return null|string
    */
   function menu()
   {
      if ( !plugin_is_installed( 'WhiteboardMenu' ) && plugin_config_get( 'ShowMenu' ) && $this->getUserHasLevel() )
      {
         return '<a href="' . plugin_page( 'storyboard_index' ) . '">' . plugin_lang_get( 'menu_title' ) . '</a>';
      }
      return null;
   }

   /**
    * Add custom plugin fields to bug-specific sites (bug_report, bug_update, bug_view)
    *
    * @param $event
    * @return null
    */
   function bugViewFields( $event )
   {
      require_once( STORYBOARD_CORE_URI . 'storyboard_db_api.php' );
      require_once( STORYBOARD_CORE_URI . 'storyboard_print_api.php' );
      $storyboard_db_api = new storyboard_db_api();
      $storyboard_print_api = new storyboard_print_api();
      $bug_id = null;

      switch ( $event )
      {
         case 'EVENT_UPDATE_BUG_FORM':
            $bug_id = gpc_get_int( 'bug_id' );
            break;
         case 'EVENT_VIEW_BUG_DETAILS':
            $bug_id = gpc_get_int( 'id' );
            break;
      }

      $card_type = null;
      $card_risk = null;
      $card_story_pt = null;
      $card_story_pt_post = null;
      $card_acc_crit = null;

      if ( $bug_id != null )
      {
         $card = $storyboard_db_api->select_story_card( $bug_id );
         if ( !is_null( $card[2] ) )
         {
            $card_type = $storyboard_db_api->select_type_by_typeid( $card[2] );
         }
         $card_risk = $card[3];
         $card_story_pt = $card[4];
         $card_story_pt_post = $card[5];
         $card_acc_crit = $card[6];
      }

      switch ( $event )
      {
         case 'EVENT_VIEW_BUG_DETAILS':
            $storyboard_print_api->printBugViewFields( $card_type, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit );
            break;
         case 'EVENT_REPORT_BUG_FORM':
            $storyboard_print_api->printBugReportFields();
            break;
         case 'EVENT_UPDATE_BUG_FORM':
            $storyboard_print_api->printBugUpdateFields( $card_type, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit );
            break;
      }
      return null;
   }

   /**
    * Update custom plugin fields
    *
    * @param $event
    * @param BugData $bug
    */
   function bugUpdateFields( $event, BugData $bug )
   {
      require_once( STORYBOARD_CORE_URI . 'storyboard_db_api.php' );
      $storyboard_db_api = new storyboard_db_api();

      $bug_id = $bug->id;
      $card_type = gpc_get_string( 'card_type', '' );
      $card_type_id = null;
      if ( !is_null( $card_type ) )
      {
         $card_type_id = $storyboard_db_api->select_typeid_by_typestring( $card_type );
      }
      $card_risk = gpc_get_string( 'card_risk', '' );
      $card_story_pt = gpc_get_string( 'card_story_pt', '' );
      $card_story_pt_post = gpc_get_string( 'card_story_pt_post', '' );
      $card_acc_crit = gpc_get_string( 'card_acc_crit', '' );

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $storyboard_db_api->insert_story_card( $bug_id, $card_type_id, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit );
            break;
         case 'EVENT_UPDATE_BUG':
            $storyboard_db_api->update_story_card( $bug_id, $card_type_id, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit );
            break;
      }
   }
}

/**
 * Trigger the removal of plugin data if a bug was removed
 *
 * @param $event
 * @param $bug_id
 */
function deleteBugReference( $event, $bug_id )
{
   require_once( STORYBOARD_CORE_URI . 'storyboard_db_api.php' );
   $storyboard_db_api = new storyboard_db_api();

   $storyboard_db_api->delete_story_card( $bug_id );
}
