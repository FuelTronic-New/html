	<?php 
	
	
 $failure= scandir("failure");
 $transaction= scandir("transaction");
$fail_dir = "../transaction/failure/";
$trans_dir = "../transaction/";
$res=array_diff($failure,$transaction);
// Cycle through all source files
foreach ($res as $file) {
  if (in_array($file, array(".",".."))) continue;
  // If we copied this successfully, mark it for deletion
  if (copy($fail_dir.$file, $trans_dir.$file)) {
    $delete[] = $fail_dir.$file;
  }
}
// Delete all successfully-copied files
if(isset($delete)){
foreach ($delete as $file) {
  unlink($file);
}}

$files = scandir("failure");
   $files2 = scandir("transaction");
   
  $result = array_intersect($files, $files2);
	  foreach($result as $fname) {
  if($fname!= '.' && $fname!= '..') {
    	 unlink("../transaction/failure/".$fname);
	}
  }


	?>