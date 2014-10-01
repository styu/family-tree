<?php
require_once('php/Tree.php');

if (!empty($_GET)){
  $familyName = $_GET['family'];
  $tree = new Tree($familyName);
  $tree->setTree();
} 
?>

<?php
$curIndent = -1;
$current = null;
$nodes = $tree->getTree();
$ul_id = "00000000p";
$familyTree = array();
foreach ($nodes as $val){
  $node = explode('#', $val);
  $nodeInfo = $tree->getInfo($node[1]);
  if ($nodeInfo == null) {
    $little = array('id' => $node[1], 'error' => 'not found');
  } else {
    $little = array(
      'level' => $node[0],
      'name' => $nodeInfo['name'],
      'id' => $nodeInfo['nodeID'],
      'email' => $nodeInfo['email'],
      'course' => $nodeInfo['course'],
      'year' => $nodeInfo['year']
    );
  }
  array_push($familyTree, $little);
}
echo json_encode($familyTree);
?>
