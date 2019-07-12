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
        // isset ($this->request->{'s'})
        // Search-Anfrage an backend
        // result: json
        // -> array([[ fid,pfad,score,preview], ... ] //20 entries
        // check read for each fid and store in array
        // -> handle over to template
        $res = '';
        $o = 0;
        if ($this->request->{'s'}) {
            
            if ($this->request->{'o'})
                $o = $this->request->{'o'};
            $start_offset = $o;
            $end_offset = $o;
            $fetched=0;
            $hits = array();
            while (count($hits) < 20) {
                $res = Backend::search($this->request->{'s'}, $o);
                for ($i = 0; $i < count($res['hits']); $i ++) {
                    $fetched++;
                    try {
                        $filename = $this->view->getPath($res['hits'][$i]['id']);
                        // make sure that the file name doesn't end with a trailing slash
                        // can for example happen single files shared across servers
                        $res['hits'][$i]['path'] = \rtrim($filename, '/');
                        $res['hits'][$i]['readable'] = true;
                        $hits[] = $res['hits'][$i];
                    } catch (NotFoundException $e) {
                        $res['hits'][$i]['readable'] = false;
                        if (! preg_match('/ReadmeDC\\.private\\.txt$/i', $res['hits'][$i]['path']))
                            $hits[] = $res['hits'][$i];
                    }
                    $end_offset++;
                    if(count($hits)>=20) break;
                }
                if($end_offset>=$res['totalHits']) break;
                $o+=20;
            }
            if($end_offset<=20) $estimated_hits=count($hits);
            else $estimated_hits=round($res['totalHits']*count($hits)/$fetched);
            
            
        }
        return new TemplateResponse('metadatarepo', 'main', array(
            's' => $this->request->{'s'},
            'url'=>$this->urlGenerator->linkToRoute('metadatarepo.page.index'),
            'start_offset' => $start_offset,'end_offset'=>$end_offset,
            'hits' => $hits,'estimated_hits'=>$estimated_hits,
            'search_help_text' => $this->config->getAppValue('metadatarepo', 'search_help_text')
        ));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param int $id
     */
    public function show($id)
    {
        try {
            $path = $this->view->getPath($id);
            $content = $this->view->file_get_contents($path);
            $encoding = mb_detect_encoding($content . "a", "UTF-8, WINDOWS-1252, ISO-8859-15, ISO-8859-1, ASCII", true);
            if ($encoding == "") {
                // set default encoding if it couldn't be detected
                $encoding = 'ISO-8859-15';
            }
            $content = iconv($encoding, "UTF-8", $content);
            $content = str_replace("\r\n", "\n", $content);
        } catch (NotFoundException $e) {
            $json = Backend::get($id);
            if(preg_match('/ReadmeDC\\.private\\.txt$/i', $json['path']))
                return new NotFoundResponse();
            $content=$json['content'];
        }
        if (Backend::getThumbnail($id))
            $has_image = 1;
        else
            $has_image = 0;
        return new TemplateResponse('metadatarepo', 'page', array(
            'readmedc' => $content,
            'has_image' => $has_image,
            'url'=>$this->urlGenerator->linkToRoute('metadatarepo.page.index'),
            's' => $this->request->{'s'},
            'id' => $id
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
