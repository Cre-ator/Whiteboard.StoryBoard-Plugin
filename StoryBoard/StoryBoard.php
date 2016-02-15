<?php

class StoryBoardPlugin extends MantisPlugin
{
   function register()
   {
      $this->name = 'Story Board';
      $this->description = '...';
      $this->page = 'config_page';

      $this->version = '1.0.1';
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
}
