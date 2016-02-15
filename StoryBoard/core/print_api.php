<?php

class print_api
{
   /**
    * Prints head elements of a page
    * @param $string
    */
   public function print_page_head( $string )
   {
      echo '<link rel="stylesheet" href="' . STORYBOARD_FILES_URI . 'storyboard.css">';
      html_page_top1( $string );
      html_page_top2();
      if ( plugin_is_installed( 'WhiteboardMenu' ) )
      {
         require_once WHITEBOARDMENU_CORE_URI . 'whiteboard_print_api.php';
         $whiteboard_print_api = new whiteboard_print_api();
         $whiteboard_print_api->printWhiteboardMenu();
      }
      $this->print_plugin_menu();
   }

   /**
    * Prints the plugin specific menu
    */
   public function print_plugin_menu()
   {
      echo '<table align="center">';
      echo '<tr><td colspan="4" class="center" ><font color="#8b0000" size="5">*** Plugin befindet sich in Entwicklungsphase ***</font></td></tr>';
      echo '</table>';
   }
}