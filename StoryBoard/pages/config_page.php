<?php
require_once STORYBOARD_CORE_URI . 'storyboard_constant_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_config_api.php';
require_once STORYBOARD_CORE_URI . 'storyboard_db_api.php';
$storyboard_config_api = new storyboard_config_api();
$storyboard_db_api = new storyboard_db_api();

auth_reauthenticate();
access_ensure_global_level( plugin_config_get( 'AccessLevel' ) );

html_page_top1( plugin_lang_get( 'config_title' ) );
html_page_top2();
print_manage_menu();

echo '<br/>';
echo '<form action="' . plugin_page( 'config_update' ) . '" method="post">';
echo form_security_field( 'plugin_StoryBoard_config_update' );

$storyboard_config_api->printTableHead();
$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormTitle( 2, 'config_caption' );
echo '</tr>';

$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_accesslevel', true );
echo '<td width="200px" colspan="1">';
echo '<select name="AccessLevel">';
print_enum_string_option_list( 'access_levels', plugin_config_get( 'AccessLevel', PLUGINS_STORYBOARD_THRESHOLD_LEVEL_DEFAULT ) );
echo '</select>';
echo '</td>';
echo '</tr>';

$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_footer', false );
$storyboard_config_api->printButton( 'ShowInFooter' );
echo '</tr>';


$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_typeadd', false );
$type = gpc_get_string( 'type', '' );
echo '<td colspan="1">';
echo '<input type="text" id="type" name="type" size="15" maxlength="128" value="', $type, '">&nbsp';
echo '<input type="submit" name="addtype" class="button" value="' . plugin_lang_get( 'config_add' ) . '">';
echo '</td>';
echo '</tr>';

$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_types', false );
echo '<td colspan="1">';

$type_rows = $storyboard_db_api->select_all_attributes( 'type' );
foreach ( $type_rows as $type_row )
{
   $types[] = $type_row[1];
}

echo '<span class="select">';
echo '<select ' . helper_get_tab_index() . ' id="types" name="types">';
if ( !is_null( $types ) )
{
   foreach ( $types as $type )
   {
      echo '<option value="' . $type . '">' . $type . '</option>';
   }
}
echo '</select>&nbsp';
$new_type = gpc_get_string( 'newtype', '' );
echo '<input type="submit" name="deletetype" class="button" value="' . plugin_lang_get( 'config_del' ) . '">&nbsp';
echo '<input type="text" id="newtype" name="newtype" size="15" maxlength="128" value="', $new_type, '">&nbsp';
echo '<input type="submit" name="changetype" class="button" value="' . plugin_lang_get( 'config_change' ) . '">';

echo '</td>';
echo '</tr>';


$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_priorityadd', false );
$priority_level = gpc_get_string( 'priority_level', '' );
echo '<td colspan="1">';
echo '<input type="text" id="priority_level" name="priority_level" size="15" maxlength="128" value="', $priority_level, '">&nbsp';
echo '<input type="submit" name="addpriority_level" class="button" value="' . plugin_lang_get( 'config_add' ) . '">';
echo '</td>';
echo '</tr>';

$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_prioritys', false );
echo '<td colspan="1">';

$priority_level_rows = $storyboard_db_api->select_all_attributes( 'priority' );
foreach ( $priority_level_rows as $priority_level_row )
{
   $priority_levels[] = $priority_level_row[1];
}

echo '<span class="select">';
echo '<select ' . helper_get_tab_index() . ' id="priority_levels" name="priority_levels">';
if ( !is_null( $priority_levels ) )
{
   foreach ( $priority_levels as $priority_level )
   {
      echo '<option value="' . $priority_level . '">' . $priority_level . '</option>';
   }
}
echo '</select>&nbsp';
$new_priority_level = gpc_get_string( 'newpriority_level', '' );
echo '<input type="submit" name="deletepriority_level" class="button" value="' . plugin_lang_get( 'config_del' ) . '">&nbsp';
echo '<input type="text" id="newpriority_level" name="newpriority_level" size="15" maxlength="128" value="', $new_priority_level, '">&nbsp';
echo '<input type="submit" name="changepriority_level" class="button" value="' . plugin_lang_get( 'config_change' ) . '">';

echo '</td>';
echo '</tr>';

$storyboard_config_api->printTableRowHead();
$storyboard_config_api->printFormCategory( 1, 'config_status_cols', false );
echo '<td valign="top" width="100px">';
echo '<select name="status_cols[]" multiple="multiple">';
print_enum_string_option_list( 'status', plugin_config_get( 'status_cols', 50 ) );
echo '</select>';
echo '</td>';
echo '</tr>';



echo '<tr>';
echo '<td class="center" colspan="2">';
echo '<input type="submit" name="change" class="button" value="' . lang_get( 'update_prefs_button' ) . '"/>';
echo '</td>';
echo '</tr>';

$storyboard_config_api->printTableFoot();
echo '</form>';

html_page_bottom1();