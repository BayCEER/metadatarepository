<?php 

namespace OCA\MDRepo\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IConfig;

class Admin implements ISettings
{
    
    private $config;
    public function __construct(
        IConfig $config
        )
    {
        $this->config = $config;
    }
    

    /**
     * Print config section (ownCloud 10)
     *
     * @return TemplateResponse
     */
    public function getPanel()
    {
        return $this->getForm();
    }
    
    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     * @since 9.1
     */
    public function getForm()
    {
        
        $parameters = [
            'search_help_text' => $this->config->getAppValue('metadatarepo', 'search_help_text'),
            'default_readmedc.txt' => $this->config->getAppValue('metadatarepo', 'default_readmedc.txt'),
        ];
        return new TemplateResponse('metadatarepo', 'settings', $parameters, '');
    }
    
    /**
     * @return string the section ID, e.g. 'sharing'
     * @since 9.1
     */
    public function getSection()
    {
        return 'metadatarepo';
    }
    
    /**
     * Get section ID (ownCloud 10)
     *
     * @return string
     */
    public function getSectionID()
    {
        return 'metadatarepo';
    }
    
    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     * @since 9.1
     */
    public function getPriority()
    {
        return 10;
    }
}

?>