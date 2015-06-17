<?php
   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/   
    set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . "/../../."));    
    
    require_once "OffAmazonPayments/.autoloader.php";
?>