<?php
namespace OCA\MDRepo\Controller;

use OC\Files\View;
use OC\HintException;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\ForbiddenException;
use OCP\IRequest;
use OCP\Lock\LockedException;
use OCP\IConfig;

class FileController extends Controller
{

    private $config;
    /** @var View */
    private $view;
    
    public function __construct(
        $appName,
        IRequest $request,
        IConfig $config
        )
    {
        parent::__construct($appName, $request);
        $this->appName = $appName;
        $this->config = $config;
        $uid = \OC::$server->getUserSession()->getUser()->getUID();
        $this->view = new View('/'.$uid.'/files');
    }

    /**
     * Fill text file with default content
     *
     * @NoAdminRequired
     *
     * @param string $dir
     * @param string $filename
     * @return DataResponse
     */
    public function fill($dir, $filename)
    {
        $path = $dir . '/' . $filename;
        
        try {
            if ($filename !== '') {
                if ($this->view->isUpdatable($path)) {
                    $filecontents = $this->config->getAppValue('metadatarepo','default_readmedc.txt', "default_readmedc.txt is not set");
                    try {
                        $this->view->file_put_contents($path, $filecontents);
                    } catch (LockedException $e) {
                        $message = (string) 'The file is locked.';
                        return new DataResponse([
                            'message' => $message
                        ], Http::STATUS_BAD_REQUEST);
                    } catch (ForbiddenException $e) {
                        return new DataResponse([
                            'message' => $e->getMessage()
                        ], Http::STATUS_BAD_REQUEST);
                    }
                    // Clear statcache
                    clearstatcache();
                    // Get new mtime
                    $newmtime = $this->view->filemtime($path);
                    $newsize = $this->view->filesize($path);
                    return new DataResponse([
                        'mtime' => $newmtime,
                        'size' => $newsize
                    ], Http::STATUS_OK);
                } else {
                    return new DataResponse([
                        'message' => 'Insufficient permissions'
                    ], Http::STATUS_BAD_REQUEST);
                }
            } else {
                $this->logger->error('No file path supplied');
                return new DataResponse([
                    'message' => 'File path not supplied'
                ], Http::STATUS_BAD_REQUEST);
            }
        } catch (HintException $e) {
            $message = (string) $e->getHint();
            return new DataResponse([
                'message' => $message
            ], Http::STATUS_BAD_REQUEST);
        } catch (\Exception $e) {
            $message = (string) 'An internal server error occurred.';
            return new DataResponse([
                'message' => $message
            ], Http::STATUS_BAD_REQUEST);
        }
    }
}
