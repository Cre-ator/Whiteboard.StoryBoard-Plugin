<?php

class db_api
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
   public function getMantisVersion()
   {
      return substr( MANTIS_VERSION, 0, 4 );
   }

   /**
    * @param $project_id
    * @return array
    */
   public function get_bugarray_by_project( $project_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
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
      if ( $this->getMantisVersion() == '1.2.' )
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
   public function selectAllAttributes( $attribute )
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
   public function selectAttributeidByAttribute( $attribute_string, $attribute )
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
   public function selectAttributeById( $attribute_id, $attribute )
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
   public function insertAttribute( $attribute_string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $query = "INSERT INTO $plugin_attribute_table ( id, " . $attribute ." ) SELECT null,'"
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
   public function updateAttribute( $attribute_id, $new_attribute_string, $attribute )
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
   public function deleteAttribute( $string, $attribute )
   {
      $plugin_attribute_table = $this->initialize_attribute_table( $attribute );
      $primary_key = $this->selectAttributeidByAttribute( $string, $attribute );
      $query = "DELETE FROM $plugin_attribute_table WHERE id = " . $primary_key;
      $this->mysqli->query( $query );
   }

   // ---------- END attribute entity ------------------------------------------------------------------------------- //

   // ---------- BEGIN card entity ---------------------------------------------------------------------------------- //

   /**
    * Get a card selected by a specific bug
    *
    * @param $bug_id
    * @return array|null
    */
   public function selectStoryCard( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_card_table = plugin_table( 'card', 'StoryBoard' );
      }
      else
      {
         $plugin_card_table = db_get_table( 'plugin_StoryBoard_card' );
      }

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
    * @param $card_name
    * @param $card_type_id
    * @param $card_priority
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_text
    * @param $card_acc_crit
    */
   public function insertStoryCard( $bug_id, $card_name, $card_type_id, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_card_table = plugin_table( 'card', 'StoryBoard' );
      }
      else
      {
         $plugin_card_table = db_get_table( 'plugin_StoryBoard_card' );
      }

      $query = "INSERT INTO $plugin_card_table ( id, bug_id, p_type_id, name, priority, risk, story_pt, story_pt_post, text, acc_crit )
         SELECT null,"
         . $bug_id . ","
         . $card_type_id . ",'"
         . $card_name . "','"
         . $card_priority . "','"
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
    * @param $card_name
    * @param $card_type_id
    * @param $card_priority
    * @param $card_risk
    * @param $card_story_pt
    * @param $card_story_pt_post
    * @param $card_text
    * @param $card_acc_crit
    */
   public function updateStoryCard( $bug_id, $card_name, $card_type_id, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit )
   {
      if ( $this->selectStoryCard( $bug_id ) == null )
      {
         $this->insertStoryCard( $bug_id, $card_name, $card_type_id, $card_priority, $card_risk, $card_story_pt, $card_story_pt_post, $card_text, $card_acc_crit );
      }
      else
      {
         if ( $this->getMantisVersion() == '1.2.' )
         {
            $plugin_card_table = plugin_table( 'card', 'StoryBoard' );
         }
         else
         {
            $plugin_card_table = db_get_table( 'plugin_StoryBoard_card' );
         }

         $query = "SET SQL_SAFE_UPDATES = 0";
         $this->mysqli->query( $query );

         $query = "UPDATE $plugin_card_table
            SET p_type_id = " . $card_type_id . ",
            name = '" . $card_name . "',
            priority = '" . $card_priority . "',
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
   public function deleteStoryCard( $bug_id )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_card_table = plugin_table( 'card', 'StoryBoard' );
      }
      else
      {
         $plugin_card_table = db_get_table( 'plugin_StoryBoard_card' );
      }

      $query = "DELETE FROM $plugin_card_table
         WHERE bug_id = " . $bug_id;

      $this->mysqli->query( $query );
   }

   // ---------- END card entity ------------------------------------------------------------------------------------ //
}