<?php
namespace OCA\MDRepo\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Controller;
use OC\Files\View;
use OCP\Files\NotFoundException;
use OCA\MDRepo\Backend;
use OCP\IConfig;
use OCP\IURLGenerator;

class PageController extends Controller
{

    private $config;
    private $urlGenerator;
    private $view;

    public function __construct($AppName, IRequest $request, IConfig $config, IURLGenerator $urlGenerator)
    {
        parent::__construct($AppName, $request);
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $uid = \OC::$server->getUserSession()
            ->getUser()
            ->getUID();
        $this->view = new View('/' . $uid . '/files');
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index()
    {
        // Renders metadatarepo/templates/main.php
        // This will produce a js page
        return new TemplateResponse('metadatarepo', 'index', array(
            'search_help_text' => $this->config->getAppValue('metadatarepo', 'search_help_text')
        ));
    }


    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param int $id
     */
    public function thumbnail($id)
    {
        try {
            $path = $this->view->getPath($id);
        } catch (NotFoundException $e) {
            $json = Backend::get($id);
            if(preg_match('/ReadmeDC\\.private\\.txt$/i', $json['path']))
                return new NotFoundResponse();
        }
        return new DataDisplayResponse(Backend::getThumbnail($id), Http::STATUS_OK, [
            'Content-Type' => 'image/png'
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param int $id
     */
    public function image($id)
    {
        try {
            $path = $this->view->getPath($id);
        } catch (NotFoundException $e) {
            $json = Backend::get($id);
            if(preg_match('/ReadmeDC\\.private\\.txt$/i', $json['path']))
                return new NotFoundResponse();
        }
        return new DataDisplayResponse(Backend::getImage($id), Http::STATUS_OK, [
            'Content-Type' => 'image/png'
        ]);
    }
}
