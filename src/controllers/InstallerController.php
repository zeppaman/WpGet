<?php

namespace WpGet\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \WpGet\Models\User as User;
use \Monolog\Logger as Logger;
use \WpGet\Utils\PackageManager;

 class InstallerController
{
    protected $logger;
    protected $container;
    protected $pm;
    protected $dm;

    public $writablePaths;
    
       public function __construct( $container)
       {
          
           $this->container=$container;
           $this->logger = $container["logger"];
           $this->pm= new PackageManager($container);
           $this->writablePaths= array(
               realpath("../../")."/web/ui/assets" =>" during installation, can be changed after that.",
               $this->pm->tempDir =>"temporary forlder have to be writable",
               $this->pm->storageDir =>"temporary forlder have to be writable",
           );

          $this->dm= $this->container['dm'];
       }

   
    public function __invoke($request, $response, $args)  
    {
        $output="";
        try
        {
         $fileErrors=false;
              foreach( $this->writablePaths as $path => $error)
              {
              $output.=("<br> CHECKING : ".$path);
                try
                {
                    $this->pm->ensureDirForPath($path);
                    if(!is_writable(str_replace("//","/", $path."/")))
                    {
                         $output.=("<span style='color:red'>&nbsp;&nbsp;&nbsp; : Folder not writable</span>");
                        $fileErrors=true;
                    }
                }
                catch(\Exception $err)
                {
                    $output.=("<span style='color:red'>&nbsp;&nbsp;&nbsp; : Unable to create folder</span>");
                    $fileErrors=true;
                }
                

              }
              if( $fileErrors)
              {
                $output.=("<h2> style='color:red'>Missing file permission. Installation cannot continue</h2>");
                $fileErrors=true;
                return $response->getBody()->write($output);
              }
              

              if(true)
              {
                // TODO: check for file or upgrade table
                $output.=("<br>Checking for table data (TODO)");
                $configPath=$this->dm->resolvePath('config/installed.lock');
               $output.=("<br>Start DB Upgrade");
                $this->dm->upgradeDB( $this->container['settings']['db']);
               $output.=("<br>Generating JSON settings");      
               try
               {
                file_put_contents($configPath, getdate());
            }
            catch ( \Exception $er1)
            {}
                $output.=("<br>Installation completed");
              }
              else
              {
                $output.=( "<br>INSTALLATION ALREADY DONE. NOTHING TODO.");
              }
        
              //Write JSON config
              $jsonSettings=$this->dm->resolvePath('web/ui/assets/settings.json');
              $uisettings= $this->container['settings']['ui'];
              try
              {
              file_put_contents($jsonSettings, json_encode($uisettings));
              }
              catch ( \Exception $er12) {}
            }
            catch(\Exception $err)
            {
                $output.=$err->getMessage();
            }
              return $response->getBody()->write($output);
     }
}