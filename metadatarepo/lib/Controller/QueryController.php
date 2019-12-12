<?php 
namespace OCA\MDRepo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCA\MDRepo\Backend;
use OCP\Files\NotFoundException;
use OC\Files\View;


class QueryController extends Controller {
    
    private $config;
    private $view;
    
    public function __construct($appName, IRequest $request, IConfig $config)
    {
        parent::__construct($appName, $request);
        $this->config = $config;
        $uid = \OC::$server->getUserSession()->getUser()->getUID();
        $this->view = new View('/'.$uid.'/files');
    }
    
    /**
     * @NoAdminRequired
     */
    public function index()
    {
        $res = Backend::search($this->request->{'query'}, $this->request->{'fields'},$this->request->{'filter'},
        $this->request->{'startPage'},$this->request->{'hitsPerPage'});
        
        for ($i = 0; $i < count($res['hits']); $i ++) {
            $res['hits'][$i]['readable']=false;
            $res['hits'][$i]['private']=(preg_match('/ReadmeDC\\.private\\.txt$/i', $res['hits'][$i]['path'])?true:false);
            try {
                $filename = $this->view->getPath($res['hits'][$i]['key']);
                // make sure that the file name doesn't end with a trailing slash
                // can for example happen single files shared across servers
                $res['hits'][$i]['path'] = \rtrim($filename, '/');
                $res['hits'][$i]['readable'] = true;
            } catch (NotFoundException $e) {
                $res['hits'][$i]['readable'] = false;
            }
        }
        \OCP\Util::writeLog('metadatarepo', 'fields: '.$this->request->{'fields'}, \OCP\Util::ERROR);
        
        return new JSONResponse($res);
    }
    
    /**
     * @NoAdminRequired
     */
    public function fields()
    {
        $res = Backend::getFieldNames();
        $selected=$this->config->getAppValue($this->appName, "search_fields");
        if(! $selected) $selected=$res;
        else $selected=explode(",",$selected);
        $selected=array_flip($selected);
        
        if(isset($this->request->{'selected'})){
            $ret=array();
            
            for($i=0;$i<count($res);$i++){
               if(isset($selected[$res[$i]])) $ret[]=$res[$i];
            }
        } else $ret=$res;
        
        return new JSONResponse($ret);
    }
    
    /**
     * @NoAdminRequired
     *
     * @param int $id
     */
    public function show($id)
    {
        $json = Backend::get($id);
        $json['has_image']=(Backend::getThumbnail($id)?true:false);
        try {
            $json['file_path'] = $this->view->getPath($id);
         } catch (NotFoundException $e) {
            if(preg_match('/ReadmeDC\\.private\\.txt$/i', $json['path']))
                return new JSONResponse(['content'=>'access denied']);
        }
        $json['name']=end(explode('/',$json['path']));
        
        return new JSONResponse($json);
     }
    
     /**
      * @NoAdminRequired
      */
     public function words(){
         $res = Backend::terms($this->request->{'q'}.'*', $this->request->{'filter'});
         return new JSONResponse($res);   
     }
     
    
}