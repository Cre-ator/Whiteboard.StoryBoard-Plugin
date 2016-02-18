<?php
require_once STORYBOARD_CORE_URI . 'storyboard_db_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_print_api.php';

$storyboard_print_api = new storyboard_print_api();
$status_cols = plugin_config_get( 'status_cols' );

$storyboard_print_api->print_page_head( plugin_lang_get( 'menu_title' ) );
echo '<table class="width75">';
print_thead( $status_cols );
print_tbody( $status_cols );
echo '</table>';
html_page_bottom1();

/**
 * Print table head
 * @param $status_cols
 */
function print_thead( $status_cols )
{
   echo '<thead>';
   echo '<tr>';
   echo '<th></th>';
   foreach ( $status_cols as $status_col )
   {
      echo '<th bgcolor="' . get_status_color( $status_col, null, null ) . '" class="center">';
      $assocArray = MantisEnum::getAssocArrayIndexedByValues( lang_get( 'status_enum_string' ) );
      echo $assocArray[$status_col];
      echo '</th>';
   }
   echo '</tr>';
   echo '</thead>';
}

/**
 * Print table body
 * @param $status_cols
 */
function print_tbody( $status_cols )
{
   $storyboard_db_api = new storyboard_db_api();
   $project_spec_bug_ids = $storyboard_db_api->get_bugarray_by_project( helper_get_current_project() );
   $types = $storyboard_db_api->select_all_attributes( 'type' );
   echo '<tbody>';
   foreach ( $types as $type )
   {
      echo '<tr>';
      echo '<td>' . $type[1] . '</td>';
      foreach ( $status_cols as $status_col )
      {
         echo '<td>';
         foreach ( $project_spec_bug_ids as $project_spec_bug_id )
         {
            $card = $storyboard_db_api->select_story_card( $project_spec_bug_id );
            if ( $card[2] == $type[0] )
            {
               $bug_status = bug_get_field( $project_spec_bug_id, 'status' );
               if ( $bug_status == $status_col )
               {
                  /**
                   * TODO Print function story card values
                   */
                  echo bug_format_id( $project_spec_bug_id );
               }
            }
         }
         echo '</td>';
      }
      echo '</tr>';
   }
   echo '</tbody>';
}
