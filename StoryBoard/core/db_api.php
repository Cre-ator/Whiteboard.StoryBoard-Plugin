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

   /**
    * Get all types
    *
    * @return array
    */
   public function getFullTypes()
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      $query = "SELECT * FROM $plugin_type_table ORDER BY type ASC";

      $result = $this->mysqli->query( $query );
      $types = array();
      if ( 0 != $result->num_rows )
      {
         while ( $row = $result->fetch_row() )
         {
            $types[] = $row;
         }
      }

      return $types;
   }

   /**
    * Add a specific type
    *
    * @param $string
    */
   public function insertTypeRow( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      $query = "INSERT INTO $plugin_type_table ( id, type )
         SELECT null,'" . $string . "'
         FROM DUAL WHERE NOT EXISTS (
         SELECT 1 FROM $plugin_type_table
         WHERE type = '" . $string . "')";

      $this->mysqli->query( $query );
   }

   /**
    * Get the primary key for a specific type string
    *
    * @param $string
    * @return mixed
    */
   public function getTypeId( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      $query = "SELECT t.id FROM $plugin_type_table t
         WHERE t.type = '" . $string . "'";

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
    * Delete a specific type
    *
    * @param $string
    */
   public function deleteTypeRow( $string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      $primary_key = $this->getTypeId( $string );

      $query = "DELETE FROM $plugin_type_table
         WHERE id = " . $primary_key;

      $this->mysqli->query( $query );
   }

   /**
    * Update an existing type string
    *
    * @param $type_id
    * @param $new_type_string
    */
   public function updateTypeRow( $type_id, $new_type_string )
   {
      if ( $this->getMantisVersion() == '1.2.' )
      {
         $plugin_type_table = plugin_table( 'type', 'StoryBoard' );
      }
      else
      {
         $plugin_type_table = db_get_table( 'plugin_StoryBoard_type' );
      }

      $query = "SET SQL_SAFE_UPDATES = 0";
      $this->mysqli->query( $query );

      $query = "UPDATE $plugin_type_table
         SET type = '" . $new_type_string . "'
         WHERE id = " . $type_id;

      $this->mysqli->query( $query );

      $query = "SET SQL_SAFE_UPDATES = 1";
      $this->mysqli->query( $query );
   }
}