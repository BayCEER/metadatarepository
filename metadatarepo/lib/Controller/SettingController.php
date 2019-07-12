<?php 
namespace OCA\MDRepo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IConfig;

class SettingController extends Controller {
    
    private $config;
    
    public function __construct(
        $appName,
        IRequest $request,
        IConfig $config        )
    {
        parent::__construct($appName, $request);
        $this->appName = $appName;
        $this->config = $config;
    }
    
    /**
     * @param $type
     * @param $value
     * @return JSONResponse
     */
    public function admin($type, $value)
    {
        //\OCP\Util::writeLog('metadatarepo', 'settings save: '.$type.$value, \OCP\Util::DEBUG);
        $this->config->setAppValue($this->appName, $type, $value);
        return new JSONResponse(array('success' => 'true'));
    }
}