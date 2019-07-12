<?php 
namespace OCA\MDRepo\Settings;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\ISection;

class AdminSectionOC implements ISection
{
    /** @var IURLGenerator */
    private $urlGenerator;
    /** @var IL10N */
    private $l;

    public function __construct(IURLGenerator $urlGenerator, IL10N $l)
    {
//        \OCP\Util::writeLog('metadatarepo', 'AdminSectionOC called', \OCP\Util::DEBUG);
        $this->urlGenerator = $urlGenerator;
        $this->l = $l;
    }

    /**
     * Icon name for ownCloud
     *
     * @returns string
     */
    public function getIconName()
    {
        return 'metadatarepo-dark';
    }

    /**
     * returns the ID of the section. It is supposed to be a lower case string,
     *
     * @returns string
     */
    public function getID()
    {
        return 'metadatarepo';
    }

    /**
     * returns the translated name as it should be displayed
     *
     * @return string
     */
    public function getName()
    {
        return 'Metadatarepository';
        return $this->l->t('Metadatarepository');
    }

    /**
     * returns priority for positioning
     *
     * @return int
     */
    public function getPriority()
    {
        return 10;
    }
}