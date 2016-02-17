<?php

class StoryBoardPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'Story Board';
      $this->description = '...';
      $this->page = 'config_page';

      $this->version = '1.0.2';
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
      require_once( $t_core_path . 'constant_api.php' );
   }

   function config()
   {
      return array
      (
         'AccessLevel' => ADMINISTRATOR,
         'ShowInFooter' => ON,
         'ShowMenu' => ON,
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

            name            C(250)  NOTNULL DEFAULT '',
            priority        C(250)  DEFAULT '',
            risk            C(250)  DEFAULT '',
            story_pt        C(250)  DEFAULT '',
            story_pt_post   C(250)  DEFAULT '',
            text            C(1000) DEFAULT '',

            acc_crit        C(1000) DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'type' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            type            C(250)  NOTNULL DEFAULT ''
            " )
         ),
         array
         (
            'CreateTableSQL', array( plugin_table( 'priority' ), "
            id              I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
            priority        C(250)  NOTNULL DEFAULT ''
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
      require_once( STORYBOARD_CORE_URI . 'db_api.php' );
      require_once( STORYBOARD_CORE_URI . 'storyboard_print_api.php' );
      $db_api = new db_api();
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

      $card_name = null;
      $card_type = null;
      $card_priority = null;
      $card_risk = null;
      $card_story_pt = null;
      $card_story_pt_post = null;
      $card_text = null;
      $card_acc_crit = null;

      if ( $bug_id != null )
      {
         $card = $db_api->selectStoryCard( $bug_id );
         $card_name = $card[3];
         if ( !is_null( $card[2] ) )
         {
            $card_type = $db_api->selectAttributeById( $card[2], 'type' );
         }
         $card_priority = $card[4];
         $card_risk = $card[5];
         $card_story_pt = $card[6];
         $card_story_pt_post = $card[7];
         $card_text = $card[8];
         $card_acc_crit = $card[9];
      }

      switch ( $event )
      {
         case 'EVENT_VIEW_BUG_DETAILS':
            $storyboard_print_api->printBugViewFields( $card_name, $card_type, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
            break;
         case 'EVENT_REPORT_BUG_FORM':
            $storyboard_print_api->printBugReportFields();
            break;
         case 'EVENT_UPDATE_BUG_FORM':
            $storyboard_print_api->printBugUpdateFields( $card_name, $card_type, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
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
      require_once( STORYBOARD_CORE_URI . 'db_api.php' );
      $db_api = new db_api();

      $bug_id = $bug->id;
      $card_name = gpc_get_string( 'card_name', '' );
      $card_type = gpc_get_string( 'card_type', '' );
      $card_type_id = $db_api->selectAttributeidByAttribute( $card_type, 'type' );
      $card_priority = gpc_get_string( 'card_priority', '' );
      $card_risk = gpc_get_string( 'card_risk', '' );
      $card_story_pt = gpc_get_string( 'card_story_pt', '' );
      $card_story_pt_post = gpc_get_string( 'card_story_pt_post', '' );
      $card_text = gpc_get_string( 'card_text', '' );
      $card_acc_crit = gpc_get_string( 'card_acc_crit', '' );

      switch ( $event )
      {
         case 'EVENT_REPORT_BUG':
            $db_api->insertStoryCard( $bug_id, $card_name, $card_type_id, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
            break;
         case 'EVENT_UPDATE_BUG':
            $db_api->updateStoryCard( $bug_id, $card_name, $card_type_id, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
            break;
      }
   }
}
