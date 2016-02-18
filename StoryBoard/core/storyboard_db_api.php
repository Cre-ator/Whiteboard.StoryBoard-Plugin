<?php

class storyboard_db_api
{
   private $mysqli;
   private $dbPath;
   private $dbUser;
   private $dbPass;
   private $dbName;

   public function __construct()
   {
      $this->dbPath = config_get( 'hostname' );
      $this->dbUser = config_get( 'db_username' );
      $this->dbPass = config_get( 'db_password' );
      $this->dbName = config_get( 'database_name' );

      $this->mysqli = new mysqli( $this->dbPath, $this->dbUser, $this->dbPass, $this->dbName );
   }

   /**
    * Get suffix of mantis version
    *
    * @return string
    */
   public function get_mantis_version()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   /**
    * @param $project_id
    * @return array
    */
   public function get_bugarray_by_project( $project_id )
   {
      if ( $this->get_mantis_version() == '1.2.' )
      {
         $bug_table = db_get_table( 'mantis_bug_table' );
      }
      else
      {
         $bug_table = db_get_table( 'bug' );
      }

      $query = "SELECT b.id FROM $bug_table b
        WHERE b.project_id =" . $project_id;

      $bug_array = array();
      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            array_push( $bug_array, $row[0] );
         }
      }
      return $bug_array;
   }

   // ---------- BEGIN attribute entity ----------------------------------------------------------------------------- //

   /**
    * @param $attribute
    * @return null|string
    */
   private function initialize_attribute_table( $attribute )
   {
      $plugin_attribute_table = null;
      if ( $this->get_mantis_version() == '1.2.' )
      {
         if ( $attribute == 'type' )
         {
            $plugin_attribute_table = plugin_table( 'type', 'StoryBoard' );
         }
         elseif ( $attribute == 'priority' )
         {
            $plugin_attribute_table = plugin_table( 'priority', 'StoryBoard' );
         }
      }
      else
      {
         if ( $attribute == 'type' )
         {
            $plugin_attribute_table = db_get_table( 'plugin_StoryBoard_type' );
         }
         elseif ( $attribute == 'priority' )
         {
            $plugin_attribute_table = db_get_table( 'plugin_StoryBoard_priority' );
         }
      }

      return $plugin_attribute_table;
   }

   /**
    * Get all attributes
    *
    * @param $attribute
    * @return array
    */
   public function select_all_attributes( $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "SELECT * FROM $plugin_attribute_table ORDER BY " . $attribute . " ASC";

      $result = $this->mysqli->query( $query );
      $attributes = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $attributes[] = $row;
         }
      }

      return $attributes;
   }

   /**
    * Get the primary key for a specific type string
    *
    * @param $attribute_string
    * @param $attribute
    * @return mixed
    */
   public function select_attributeid_by_attribute( $attribute_string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "SELECT id FROM $plugin_attribute_table WHERE " . $attribute . " = '" . $attribute_string . "'";

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $row = mysqli_fetch_row( $result );
         $primary_key = $row[0];
         return $primary_key;
      }
      else
      {
         return null;
      }
   }

   /**
    * Get type by specified type id
    *
    * @param $attribute_id
    * @param $attribute
    * @return array|null
    */
   public function select_attribute_by_id( $attribute_id, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "SELECT " . $attribute . " FROM $plugin_attribute_table WHERE id = " . $attribute_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $type_row = mysqli_fetch_row( $result );
         $type = $type_row[0];
         return $type;
      }
      else
      {
         return null;
      }
   }

   /**
    * Add a specific type
    *
    * @param $attribute_string
    * @param $attribute
    */
   public function insert_attribute( $attribute_string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "INSERT INTO $plugin_attribute_table ( id, " . $attribute . " ) SELECT null,'"
         . $attribute_string . "' FROM DUAL WHERE NOT EXISTS ( SELECT 1 FROM $plugin_attribute_table WHERE "
         . $attribute . " = '" . $attribute_string . "')";
      $this->mysqli->query( $query );
   }

   /**
    * Update an existing type string
    *
    * @param $attribute_id
    * @param $new_attribute_string
    * @param $attribute
    */
   public function update_attribute( $attribute_id, $new_attribute_string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_attribute_table SET " . $attribute . " = '" . $new_attribute_string . "' WHERE id = " . $attribute_id;
      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Delete a specific type
    *
    * @param $string
    * @param $attribute
    */
   public function delete_attribute( $string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $primary_key = $this->select_attributeid_by_attribute( $string, $attribute );
      $query = "DELETE FROM $plugin_attribute_table WHERE id = " . $primary_key;
      $this->mysqli->query( $query );
   }

   // ---------- END attribute entity ------------------------------------------------------------------------------- //

   // ---------- BEGIN card entity ---------------------------------------------------------------------------------- //

   /**
    * @return null|string
    */
   public function initialize_card_table()
   {
      $plugin_card_table = null;
      if ( $this->get_mantis_version() == '1.2.' )
      {
         $plugin_card_table = plugin_table( 'card', 'StoryBoard' );
      }
      else
      {
         $plugin_card_table = db_get_table( 'plugin_StoryBoard_card' );
      }

      return $plugin_card_table;
   }

   /**
    * Get a card selected by a specific bug
    *
    * @param $bug_id
    * @return array|null
    */
   public function select_story_card( $bug_id )
   {
      $plugin_card_table = $this->initialize_card_table();
      $query = "SELECT * FROM $plugin_card_table
         WHERE bug_id = " . $bug_id;

      $result = $this->mysqli->query( $query );
      if ( 0 != $result->num_rows )
      {
         $card = mysqli_fetch_row( $result );
         return $card;
      }
      else
      {
         return null;
      }
   }

   /**
    * @param $bug_id
    * @param $card_type_id
    * @param $card_priority_id
    * @param $card_name
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_text
    * @param $card_acc_crit
    */
   public function insert_story_card( $bug_id, $card_type_id, $card_priority_id, $card_name, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit )
   {
      $plugin_card_table = $this->initialize_card_table();
      $query = "INSERT INTO $plugin_card_table ( id, bug_id, p_type_id, p_priority_id, name, risk, story_pt, story_pt_post, text, acc_crit )
         SELECT null,"
         . $bug_id . ","
         . $card_type_id . ","
         . $card_priority_id . ",'"
         . $card_name . "','"
         . $card_risk . "','"
         . $card_story_pt . "','"
         . $card_story_pt_post . "','"
         . $card_text . "','"
         . $card_acc_crit . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_card_table
         WHERE bug_id = " . $bug_id . ")";

      $this->mysqli->query( $query );
   }

   /**
    * @param $bug_id
    * @param $card_type_id
    * @param $card_priority_id
    * @param $card_name
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_text
    * @param $card_acc_crit
    */
   public function update_story_card( $bug_id, $card_type_id, $card_priority_id, $card_name, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit )
   {
      if ( $this->select_story_card( $bug_id ) == null )
      {
         $this->insert_story_card( $bug_id, $card_type_id, $card_priority_id, $card_name, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
      }
      else
      {
         $plugin_card_table = $this->initialize_card_table();
         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_card_table
            SET p_type_id = " . $card_type_id . ",
            p_priority_id = " . $card_priority_id . ",
            name = '" . $card_name . "',
            risk = '" . $card_risk . "',
            story_pt = '" . $card_story_pt . "',
            story_pt_post = '" . $card_story_pt_post . "',
            text = '" . $card_text . "',
            acc_crit = '" . $card_acc_crit . "'";
         $query .= " WHERE bug_id = " . $bug_id;

         $this->mysqli->query( $query );

         $query = "SET SQL_SAFE_UPDATES = 1";
         $this->mysqli->query( $query );
      }
   }

   /**
    * Delete a card specified by a bug id
    *
    * @param $bug_id
    */
   public function delete_story_card( $bug_id )
   {
      $plugin_card_table = $this->initialize_card_table();
      $query = "DELETE FROM $plugin_card_table
         WHERE bug_id = " . $bug_id;

      $this->mysqli->query( $query );
   }

   // ---------- END card entity ------------------------------------------------------------------------------------ //
}