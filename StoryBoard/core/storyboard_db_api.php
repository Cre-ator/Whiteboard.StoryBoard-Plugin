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

   // ---------- BEGIN type entity ---------------------------------------------------------------------------------- //

   /**
    * @return null|string
    */
   private function initialize_type_table()
   {
      $plugin_type_table = null;
      if ( $this->get_mantis_version() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      return $plugin_type_table;
   }

   /**
    * Get all types
    *
    * @return array
    */
   public function select_all_types()
   {
      $plugin_type_table = $this->initialize_type_table();
      $query = "SELECT * FROM $plugin_type_table ORDER BY type ASC";

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
    * @param $type_string
    * @return mixed
    */
   public function select_typeid_by_typestring( $type_string )
   {
      $plugin_type_table = $this->initialize_type_table();
      $query = "SELECT id FROM $plugin_type_table WHERE type = '" . $type_string . "'";

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
    * @param $type_id
    * @return array|null
    */
   public function select_type_by_typeid( $type_id )
   {
      $plugin_type_table = $this->initialize_type_table();
      $query = "SELECT type FROM $plugin_type_table WHERE id = " . $type_id;

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
    * @param $type_string
    */
   public function insert_type( $type_string )
   {
      $plugin_type_table = $this->initialize_type_table();
      $query = "INSERT INTO $plugin_type_table ( id, type ) SELECT null,'"
         . $type_string . "' FROM DUAL WHERE NOT EXISTS ( SELECT 1 FROM $plugin_type_table WHERE type = '"
         . $type_string . "')";
      $this->mysqli->query( $query );
   }

   /**
    * Update an existing type string
    *
    * @param $type_id
    * @param $new_type_string
    */
   public function update_type( $type_id, $new_type_string )
   {
      $plugin_type_table = $this->initialize_type_table();
      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_type_table SET type = '" . $new_type_string . "' WHERE id = " . $type_id;
      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }

   /**
    * Delete a specific type
    *
    * @param $type_string
    */
   public function delete_type( $type_string )
   {
      $plugin_type_table = $this->initialize_type_table();
      $primary_key = $this->select_typeid_by_typestring( $type_string );
      $query = "DELETE FROM $plugin_type_table WHERE id = " . $primary_key;
      $this->mysqli->query( $query );
   }

   // ---------- END type entity ------------------------------------------------------------------------------------ //

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
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_acc_crit
    */
   public function insert_story_card( $bug_id, $card_type_id, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit )
   {
      $plugin_card_table = $this->initialize_card_table();
      $query = "INSERT INTO $plugin_card_table ( id, bug_id, p_type_id, risk, story_pt, story_pt_post, acc_crit )
         SELECT null,"
         . $bug_id . ","
         . $card_type_id . ",'"
         . $card_risk . "','"
         . $card_story_pt . "','"
         . $card_story_pt_post . "','"
         . $card_acc_crit . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_card_table
         WHERE bug_id = " . $bug_id . ")";

      $this->mysqli->query( $query );
   }

   /**
    * @param $bug_id
    * @param $card_type_id
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_acc_crit
    */
   public function update_story_card( $bug_id, $card_type_id, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit )
   {
      if ( $this->select_story_card( $bug_id ) == null )
      {
         $this->insert_story_card( $bug_id, $card_type_id, $card_risk, $card_story_pt, $card_story_pt_post, $card_acc_crit );
      }
      else
      {
         $plugin_card_table = $this->initialize_card_table();
         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_card_table
            SET p_type_id = " . $card_type_id . ",
            risk = '" . $card_risk . "',
            story_pt = '" . $card_story_pt . "',
            story_pt_post = '" . $card_story_pt_post . "',
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