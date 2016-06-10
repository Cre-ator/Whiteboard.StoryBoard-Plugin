<?php

class storyboard_config_api
{
   /**
    * Get suffix of mantis version
    *
    * @return string
    */
   public function getMantisVersion ()
   {
      return substr ( MANTIS_VERSION, 0, 4 );
   }

   /**
    * Updates a value in the plugin configuration
    *
    * @param $value
    * @param $constant
    */
   public function updateValue ( $value, $constant )
   {
      $act_value = null;

      if ( is_int ( $value ) )
      {
         $act_value = gpc_get_int ( $value, $constant );
      }

      if ( is_string ( $value ) )
      {
         $act_value = gpc_get_string ( $value, $constant );
      }

      if ( plugin_config_get ( $value ) != $act_value )
      {
         plugin_config_set ( $value, $act_value );
      }
   }

   /**
    * Updates a button in the plugin configuration
    *
    * @param $config
    */
   public function updateButton ( $config )
   {
      $button = gpc_get_int ( $config );

      if ( plugin_config_get ( $config ) != $button )
      {
         plugin_config_set ( $config, $button );
      }
   }

   /**
    *
    */
   public function printTableHead ()
   {
      if ( $this->getMantisVersion () == '1.2.' )
      {
         echo '<table align="center" class="width75" cellspacing="1">';
      }
      else
      {
         echo '<div class="form-container">';
         echo '<table>';
      }
   }

   /**
    *
    */
   public function printTableFoot ()
   {
      if ( $this->getMantisVersion () == '1.2.' )
      {
         echo '</table>';
      }
      else
      {
         echo '</table>';
         echo '</div>';
      }
   }

   /**
    *
    */
   public function printTableRowHead ()
   {
      if ( $this->getMantisVersion () == '1.2.' )
      {
         echo '<tr ' . helper_alternate_class () . '>';
      }
      else
      {
         echo '<tr>';
      }
   }

   /**
    * @param $colspan
    * @param $lang_string
    */
   public function printFormTitle ( $colspan, $lang_string )
   {
      echo '<td class="form-title" colspan="' . $colspan . '">';
      echo plugin_lang_get ( $lang_string );
      echo '</td>';
   }

   /**
    * @param $colspan
    * @param $lang_string
    * @param $required_flag
    */
   public function printFormCategory ( $colspan, $lang_string, $required_flag )
   {
      echo '<td class="category" width="30%" colspan="' . $colspan . '">';
      if ( $required_flag )
      {
         echo '<span class="required">*</span>';
      }
      echo plugin_lang_get ( $lang_string );
      echo '</td>';
   }

   /**
    * @param $config
    */
   public function printButton ( $config )
   {
      echo '<td width="100px">';
      echo '<label>';
      echo '<input type="radio" name="' . $config . '" value="1"';
      echo ( ON == plugin_config_get ( $config ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get ( 'yes' );
      echo '</label>';
      echo '<label>';
      echo '<input type="radio" name="' . $config . '" value="0"';
      echo ( OFF == plugin_config_get ( $config ) ) ? 'checked="checked"' : '';
      echo '/>' . lang_get ( 'no' );
      echo '</label>';
      echo '</td>';
   }
}