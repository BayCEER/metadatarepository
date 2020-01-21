<?php
namespace OCA\MDRepo\BackgroundJob;

use OCA\metadatarepo\AppInfo\Application;
use OC\BackgroundJob\TimedJob;
use OCA\MDRepo\Backend;
use OCP\IUserManager;
use OCP\IUser;
use OC\Files\View;
use OCP\Files\NotFoundException;

class RemoveDeleted extends TimedJob
{
    /**
     * @var IUserManager
     */
    private $userManager;
    
    
    private $hits;
    
    public function __construct(IUserManager $userManager = null)
    {
        parent::setInterval(3600); // sec!
        if ($userManager === null) {
            $this->fixDIForJobs();
        } else {
            $this->userManager = $userManager;
        }
    }

    protected function fixDIForJobs() {
        $application = new Application();
        $this->userManager = \OC::$server->getUserManager();
    }
    
    public function run($arguments)
    {
        \OCP\Util::writeLog("metadatarepo", "Cronjob called", \OCP\Util::INFO);
        $this->hits = [];
        do {
            $res = Backend::search("*", [], json_decode("{}"), $offset, 100, 0);
            $offset += count($res['hits']);
            for ($i = 0; $i < count($res['hits']); $i ++) {
                $res['hits'][$i]['found']=false;
                $this->hits[] = $res['hits'][$i];
            }
        } while (count($res['hits']) > 0);
        \OCP\Util::writeLog("metadatarepo", count($this->hits)." to check", \OCP\Util::INFO);
        
        $this->userManager->callForAllUsers(function (IUser $user) {
            $uid = $user->getUID();
            \OC_Util::tearDownFS();
            \OC_Util::setupFS($uid);
            \OCP\Util::writeLog("metadatarepo", "checking user ".$uid, \OCP\Util::INFO);
            $view=new View('/'.$uid.'/files');
            $view_trash=new View('/'.$uid.'/files_trashbin');
            for ($i = 0; $i < count($this->hits); $i ++) {
                if($this->hits[$i]['found']) continue;
                \OCP\Util::writeLog("metadatarepo", "Checking ".$this->hits[$i]['path'].'--'.$this->hits[$i]['key'], \OCP\Util::DEBUG);
                try {
                    $filename = $view->getPath($this->hits[$i]['key']);
                } catch (NotFoundException $e) {
                    try {
                        $filename = $view_trash->getPath($this->hits[$i]['key']);                    
               
                    } catch (NotFoundException $e){
                        $filename=false;
                    }
                } 
                if($filename) $this->hits[$i]['found']=$filename;
                
            }      
            
        });
        for ($i = 0; $i < count($this->hits); $i ++) {
            if(! $this->hits[$i]['found']){
                \OCP\Util::writeLog("metadatarepo", $this->hits[$i]['path'].": Not found deleting...", \OCP\Util::INFO);
                Backend::deleteById($this->hits[$i]['key']);
            }
        }
        \OC_Util::tearDownFS();
            
    }
}