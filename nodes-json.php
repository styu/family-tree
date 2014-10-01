<?php
require_once('php/Tree.php');

if (!empty($_GET)){
  $familyName = $_GET['family'];
  $tree = new Tree($familyName);
  $tree->setTree();
  echo json_encode($tree->getNodes());
}
?>
