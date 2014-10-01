<?php
require_once('../php/Tree.php');
if ($_POST['changes'] && $_POST['family']) {
  $tree = new Tree($_POST['family']);
  $tree->setTree();
  $errors = array();
  foreach($_POST['changes'] as $change) {
    if ($change['changeType'] == 'addition') {
      $parentID = $change['big']['id'];
      if (intval($change['big']['id']) <= 0) {
        $parent = $tree->getNodeID($change['big']['name'], $change['big']['course'], $change['big']['year'], $change['big']['email']);
        if ($parent == null) {
          array_push($errors, mysqli_error($tree->getLink()));
          continue;
        } else {
          $parentID = $parent['nodeID'];
        }
      }
      $result = $tree->addLittle($change['name'], $change['course'], $change['year'], $change['email'], $parentID);
      if (!$result) {
        array_push($errors, mysqli_error($tree->getLink()));
      }
    } else if ($change['changeType'] == 'removal') {
      $nodeID = $change['id'];
      if (intval($nodeID) <= 0) {
        $node = $tree->getNodeID($change['name'], $change['course'], $change['year'], $change['email']);
        if ($node == null) {
          array_push($errors, mysqli_error($tree->getLink()));
          continue;
        } else {
          $nodeID = $node['nodeID'];
        }
      }
      $tree->removeNode($nodeID);
    } else if ($change['changeType'] == 'change') {
      $parentID = $change['big']['id'];
      if (intval($change['big']['id']) <= 0) {
        $parent = $tree->getNodeID($change['big']['name'], $change['big']['course'], $change['big']['year'], $change['big']['email']);
        if ($parent == null) {
          array_push($errors, mysqli_error($tree->getLink()));
          continue;
        } else {
          $parentID = $parent['nodeID'];
        }
      }
      $nodeID = $change['id'];
      if (intval($nodeID) <= 0) {
        $node = $tree->getNodeID($change['name'], $change['course'], $change['year'], $change['email']);
        if ($node == null) {
          array_push($errors, mysqli_error($tree->getLink()));
          continue;
        } else {
          $nodeID = $node['nodeID'];
        }
      }
      $result = $tree->updateBig($nodeID, $parentID);
      if (!$result) {
        array_push($errors, mysqli_error($tree->getLink()));
      }
    }
  }
}
echo json_encode($errors);
?>