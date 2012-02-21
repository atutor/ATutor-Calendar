<?php
/*
|-----------------
| Chip Download Class
|------------------
*/
 function curPageURL($mode=1) {
 if($mode){
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 } 
 }
 else{
 $pageURL = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'];
 }
return str_replace(substr($pageURL,strripos($pageURL,'/')+1),'',$pageURL);
}

require_once("includes/classes/chip_download.class.php");
 
/*
|-----------------
| Class Instance
|------------------
*/
$file = $_GET['file']; 
$download_path = curPageURL(0);

  
$args = array(
        'download_path'     =>   $download_path,
        'file'              =>   $file,
        'extension_check'   =>   TRUE,
        'referrer_check'    =>   FALSE,
        'referrer'          =>   NULL,
        );
        
      
$download = new chip_download( $args );
 
/*
|-----------------
| Pre Download Hook
|------------------
*/
 
$download_hook = $download->get_download_hook();

//exit;
 
/*
|-----------------
| Download
|------------------
*/

if( $download_hook['download'] == TRUE ) {
 
    /* You can write your logic before proceeding to download */
 
    /* Let's download file */
   
    $download->get_download();
 
}
 
?>
