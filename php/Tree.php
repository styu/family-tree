<?php
require_once('db_setup.php');
/**
 * Creates the family tree, with correct hierarchy
 *
 **/

class Tree {

  protected static $family_table = 'families';
  protected static $node_table = 'nodes';
  protected static $db_name = "mitats+families";
  
  protected $tree = array();
  protected $familyID;
  protected $familyName;
  /** Maintains a link to the database. */
  protected $link;

  protected $familyNameMap = array(
    'atsgangstas' => '000001',
    'jbapb' => '000002',
    'greaterthanu' => '000003',
    'bigfatfuzzylove' => '000004',
    'funkybobasaurs' => '000005',
    'bestatsfamily' => '000006',
    'allstars' => '000007'
  );

  protected $familyIDMap = array(
    '000001' =>'atsgangstas',
    '000002' => 'jbapb',
    '000003' => 'greaterthanu',
    '000004' => 'bigfatfuzzylove',
    '000005' => 'funkybobasaurs',
    '000006' => 'bestatsfamily',
    '000007' => 'allstars'
  );

  /**
   * Constructor, sets the family for the tree and database connections
   *
   **/
   function __construct($familyName) {
    $this->link = db_default_connection();
    //Filling the tables (only needs to be done once
    /*db_setup_connections_table(self::$db_name, $this->link);
    db_insert_families();
    db_insert_nodes();*/
    $this->familyID = $this->familyNameMap[$familyName];
    $this->setName();
   }
  function __destruct(){
    mysqli_close($this->link);
  }
   /**
    * Sets name of the family
    **/
   function setName(){
    mysqli_select_db($this->link, self::$db_name);
    $id = $this->familyID;
    $table_name = self::$family_table;
    
    $sql = mysqli_query($this->link, "SELECT name AS familyName FROM $table_name WHERE id = $id");
    $arr = mysqli_fetch_array($sql);
    $this->familyName = $arr['familyName'];
   }
   /**
    * @return name of the family
    **/
   function getName(){
    return $this->familyName;
   }

   function getLink() {
    return $this->link;
   }

   /**
    * Sets up the tree
    **/
   function setTree(){
    mysqli_select_db($this->link, self::$db_name);
    $id = $this->familyID;
    $table_name = self::$node_table;
    
    $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE parent='00000000' && family = $id");
    $parent = mysqli_fetch_array($sql);
    $this->setNodes($parent['nodeID'], $parent['name']);
   }
  /**
   * Helper method for setNodes, inserts an element into desired position of array
   * 
   * @param $array, array in which elements are inserted
   * @param $pos, position to be inserted at
   * @param $val, value to be inserted
   * 
   * @return array, with the value inserted in
   **/
  function array_insert($array, $pos, $val){
    $copy = $array;
    $end = array_slice($copy, $pos);
    array_splice($copy, $pos);  
    $copy[$pos] = $val;
    array_splice($copy, count($copy), 0, $end);
    return $copy;
  }
  /**
   * Sets up the family tree
   * 
   * @param $root, the parent of the tree (ID)
   * @param $rootname, name of the parent
   *
   **/
  function setNodes($root, $rootname){
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $id = $this->familyID;
    
    $agenda = array();
    $nodes = array();
    array_push($nodes, "0#" . $root);
    array_push($agenda, array($root, $root, $rootname));
    while (!empty($agenda)){
      $parent = array_shift($agenda);
      $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE parent = '$parent[1]' && family = $id");
      $len = explode(' ', $parent[0]);
      $parentNode = (count($len)-1) . "#" . $parent[1];
      $index = array_search($parentNode, $nodes)+1;
      
      while ($child = mysqli_fetch_array($sql)){
        $path = $parent[0] . ' ' . $child['nodeID'];
        $pathlen = explode(' ', $path);
        $level = count($pathlen)-1;
        $childNode = $level . "#" . $child['nodeID'];
        $nodes = $this->array_insert($nodes, $index, $childNode);
        array_push($agenda, array($path, $child['nodeID'], $child['name']));
        $index++;
      }
    }
    $this->tree = $nodes;
  }
  /**
   * Returns the tree, an array
   *
   * @return array representing the family tree
   **/
  function getTree(){
    return $this->tree;
  }
  /**
   * returns info for a particular person
   * @param $nodeID, the id of the person in the table
   **/
  function getInfo($nodeID){
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE nodeID = $nodeID");
    if (!$sql) {
      return null;
    }
    return mysqli_fetch_array($sql);
  }
  /**
     * inserts new node
   *
    */
  function addLittle($name, $course, $year, $email, $parent){
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $fam = $this->familyID;
    return mysqli_query($this->link, "INSERT INTO $table_name (name, parent, family, year, course, email) VALUES
          ('$name', '$parent', '$fam', '$year', '$course', '$email')");
  }

  function updateBig($nodeID, $parentID) {
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $fam = $this->familyID;
    return mysqli_query($this->link, "UPDATE $table_name SET parent='$parentID' WHERE nodeID='$nodeID'");
  }

  function removeNode($nodeID) {
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $id = $this->familyID;
    $agenda = array();
    array_push($agenda, $nodeID);
    while (!empty($agenda)) {
      $parent = array_shift($agenda);
      mysqli_query($this->link, "DELETE FROM $table_name WHERE nodeID='$parent' AND family = '$id'");
      $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE parent = '$parent' AND family = '$id'");
      
      while ($sql && ($child = mysqli_fetch_array($sql))){
        array_push($agenda, $child['nodeID']);
      }
    }
  }

  function getNodeID($name, $course, $year, $email) {
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $fam = $this->familyID;
    $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE name = '$name' AND course = '$course' AND year = '$year' AND email = '$email' LIMIT 1");
    if (!$sql) {
      return null;
    }
    return mysqli_fetch_array($sql);
  }

  /**
   * Sets info for node
   *
   **/
  function setNodeInfo($name, $course, $year, $email, $nodeID){
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $fam = $this->familyID;
    $sql = mysqli_query($this->link, "UPDATE $table_name 
                SET name = '$name',
                course = '$course',
                year = '$year',
                email = '$email' WHERE nodeID = '$nodeID'");
    echo mysqli_error($this->link);
  }
  /**
     * Deletes leaf from tree
   **/
  function deleteNode($nodeID){
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $sql = mysqli_query($this->link, "SELECT * FROM $table_name WHERE parent = $nodeID");
    $empty = mysqli_fetch_array($sql);
    if (!$empty)
      $sql = mysqli_query($this->link, "DELETE FROM $table_name WHERE nodeID = $nodeID");
    else
      echo "This person has littles, can't delete since webmaster too lazy to reassign littles";
  }

  function getNodes() {
    mysqli_select_db($this->link, self::$db_name);
    
    $table_name = self::$node_table;
    $fam = $this->familyID;
    $sql = mysqli_query($this->link, "SELECT * FROM $table_name");
    $nodes = array();
    while($node = mysqli_fetch_array($sql)) {
      $node['family'] = $this->familyIDMap[$node['family']];
      array_push($nodes, $node);
    }
    return $nodes;
  }
}

?>