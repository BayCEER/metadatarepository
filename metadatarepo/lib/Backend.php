<?php
namespace OCA\MDRepo;

use OC\Files\Filesystem;
use OC\Files\View;
use OCP\Files\NotFoundException;
use OC\PreviewManager;
define('ELASTIC_SEARCH_URL', \OC::$server->getSystemConfig()->getValue('metadatarepo.elastic_search.url','http://localhost:5541/'));
define('COLLECTION' , \OC::$server->getSystemConfig()->getValue('metadatarepo.elastic_search.collection','owncloud'));

class Backend
{

    public static function get($id)
    {
        $json=file_get_contents(ELASTIC_SEARCH_URL . COLLECTION.'/index/'. $id);
        return json_decode($json, true);
    }

    public static function getThumbnail($id)
    {
        return file_get_contents(ELASTIC_SEARCH_URL . COLLECTION.'/thumbnail/' . $id);
    }

    public static function getImage($id)
    {
        return file_get_contents(ELASTIC_SEARCH_URL . COLLECTION.'/image/'. $id);
    }

    public static function write($path)
    {
        list ($uid, $owner_path, $info) = self::getFileInfo($path);
        $ownerView = new View('/' . $uid . '/files');
        if (preg_match('/\\.txt$/i', $info->getName())) {
            $content = $ownerView->file_get_contents($owner_path);
            $encoding = mb_detect_encoding($content . "a", "UTF-8, WINDOWS-1252, ISO-8859-15, ISO-8859-1, ASCII", true);
            if ($encoding == "") {
                // set default encoding if it couldn't be detected
                $encoding = 'ISO-8859-15';
            }
            $content = iconv($encoding, "UTF-8", $content);
            $content = str_replace("\r\n", "\n", $content);
            
            $json = json_encode(array(
                'key' => $info->getId(),
                'path' => $uid . '/' . $owner_path,
                'content' => $content,
                'lastModified' => $info->getMTime(),
                'user' => \OC::$server->getUserSession()->getUser()->getDisplayName()
            ));
            $req = curl_init(ELASTIC_SEARCH_URL . COLLECTION . '/index/'.$info->getId());
            curl_setopt($req, CURLOPT_POST, 1);
            curl_setopt($req, CURLOPT_POSTFIELDS, $json);
            curl_setopt($req, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            ));
            $res = curl_exec($req);
            
        } else {
    
            $endings=array('TXT','txt','Txt','TXt','txT','tXt','tXT','TxT');
            $try=0;
            $info_txt=0;
            while ($try<8){
                $path_txt=preg_replace('/(jpe?g|png|gif|svg)$/i', $endings[$try], $path);
                $info_txt = Filesystem::getFileInfo($path_txt);
                if($info_txt) break;
                $try++;
            }
            
            if (!$info_txt) {
                \OCP\Util::writeLog("metadatarepo", "No ReadmeDC.TXT for $path", \OCP\Util::ERROR);
                return;
            }
            $previewManager = new PreviewManager(\OC::$server->getConfig(), \OC::$server->getRootFolder(), \OC::$server->getUserSession());
            $preview = $previewManager->createPreview('files/' . $path, 1024,1024);
            if ($preview->valid()) {
                $data=$preview->data();
                $req = curl_init(ELASTIC_SEARCH_URL .COLLECTION. '/image/'. $info_txt->getId());
                curl_setopt($req, CURLOPT_POST, 1);
                curl_setopt($req, CURLOPT_POSTFIELDS, $data);
                curl_setopt($req, CURLOPT_HTTPHEADER, array(
                    'Content-Type: image/png',
                    'Content-Length: ' . strlen($data)
                ));
                $res = curl_exec($req);
            } else {
                \OCP\Util::writeLog("metadatarepo", "Create preview failed", \OCP\Util::ERROR);
            }
            $preview = $previewManager->createPreview('files/' . $path, 100, 100);
            if ($preview->valid()) {
                $data=$preview->data();
                $req = curl_init(ELASTIC_SEARCH_URL  .COLLECTION. '/thumbnail/'. $info_txt->getId());
                curl_setopt($req, CURLOPT_POST, 1);
                curl_setopt($req, CURLOPT_POSTFIELDS, $data);
                curl_setopt($req, CURLOPT_HTTPHEADER, array(
                    'Content-Type: image/png',
                    'Content-Length: ' . strlen($data)
                ));
                $res = curl_exec($req);
            } else {
                \OCP\Util::writeLog("metadatarepo", "Create thumbnail failed", \OCP\Util::ERROR);
            }
        }
        $message = "write hook: $uid $owner_path " . $info->getId();
        if ($res)
            \OCP\Util::writeLog("metadatarepo", $message . " SUCCESS", \OCP\Util::INFO);
        else
            \OCP\Util::writeLog("metadatarepo", $message . " ERROR", \OCP\Util::ERROR);
    }

    public static function delete($path, $oldpath = '')
    {
        list ($uid, $path, $info) = self::getFileInfo($path);
        if (preg_match('/\\.txt$/i', $info->getName())) {
            // TXT-File
            $req = curl_init();
            curl_setopt($req, CURLOPT_URL, ELASTIC_SEARCH_URL  .COLLECTION.'/index/' . $info->getId());
            curl_setopt($req, CURLOPT_CUSTOMREQUEST, "DELETE");
            $res = curl_exec($req);
        } else {
            // Image
            $endings=array('TXT','txt','Txt','TXt','txT','tXt','tXT','TxT');
            $try=0;
            $info_txt=0;
            while ($try<8){
                $path_txt=preg_replace('/(jpe?g|png|gif|svg)$/i', $endings[$try], $oldpath);
                $info_txt = Filesystem::getFileInfo($path_txt);
                if($info_txt) break;
                $try++;
            }
            
            if (!$info_txt) {
                \OCP\Util::writeLog("metadatarepo", "No ReadmeDC.TXT for $path", \OCP\Util::ERROR);
                return;
            }
            $req = curl_init();
            curl_setopt($req, CURLOPT_URL, ELASTIC_SEARCH_URL  .COLLECTION.'/image/' . $info_txt->getId());
            curl_setopt($req, CURLOPT_CUSTOMREQUEST, "DELETE");
            $res = curl_exec($req);
            $req = curl_init();
            curl_setopt($req, CURLOPT_URL, ELASTIC_SEARCH_URL  .COLLECTION.'/thumbnail/'. $info_txt->getId());
            curl_setopt($req, CURLOPT_CUSTOMREQUEST, "DELETE");
            $res = curl_exec($req);
        }
        $message = "delete hook: $uid $path " . $info->getId();
        if ($res)
            \OCP\Util::writeLog("metadatarepo", $message . " SUCCESS", \OCP\Util::INFO);
        else
            \OCP\Util::writeLog("metadatarepo", $message . " ERROR", \OCP\Util::ERROR);
    }

    public static function getFileInfo($filename)
    {
        $uid = Filesystem::getOwner($filename);
        $userManager = \OC::$server->getUserManager();
        $info = Filesystem::getFileInfo($filename);
        
        // \OCP\Util::writeLog("metadatarepo","Mimetype: ".$info->getMimetype(),2);
        // if the user with the UID doesn't exists, e.g. because the UID points
        // to a remote user with a federated cloud ID we use the current logged-in
        // user. We need a valid local user to create the versions
        if (! $userManager->userExists($uid)) {
            $uid = \OC::$server->getUserSession()
                ->getUser()
                ->getUID();
        }
        Filesystem::initMountPoints($uid);
        if ($uid != \OC::$server->getUserSession()
            ->getUser()
            ->getUID()) {
            $ownerView = new View('/' . $uid . '/files');
            try {
                $filename = $ownerView->getPath($info['fileid']);
                // make sure that the file name doesn't end with a trailing slash
                // can for example happen single files shared across servers
                $filename = \rtrim($filename, '/');
            } catch (NotFoundException $e) {
                $filename = null;
            }
        }
        return [
            $uid,
            $filename,
            $info
        ];
    }

    public static function search($search, $fields, $filter, $offset = 0, $hitsPerPage = 20, $fragmentSize=100)
    {
        if(! $filter) $filter=json_decode("{}");
        $json = file_get_contents(ELASTIC_SEARCH_URL  .COLLECTION. '/index?query=' . urlencode($search) . 
            '&start=' . $offset . '&hitsPerPage=' . $hitsPerPage . '&fragmentSize=' . $fragmentSize .
            '&fields='. urlencode(json_encode($fields)) .
            '&filter='. urlencode(json_encode($filter))
            );
        
        return json_decode($json, true);
    }
    
    public static function getFieldNames()
    {
        $json = file_get_contents(ELASTIC_SEARCH_URL  .COLLECTION. '/field/names?sysFields=true');
        return json_decode($json, true);
    }
    
    public static function terms($search, $filter, $maxHits=10){
        if(! $filter) $filter=json_decode("{}");
        $json = file_get_contents(ELASTIC_SEARCH_URL  .COLLECTION. '/terms?query=' . urlencode($search) .
            '&filter='. urlencode(json_encode($filter)). '&maxHits=' .$maxHits . '&fragmentSize=30'
            );
        
        return json_decode($json, true);
    }
    
}