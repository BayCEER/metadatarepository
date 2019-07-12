<?php
namespace OCA\metadatarepo;

\OC::$server->getNavigationManager()->add(function () {
    $urlGenerator = \OC::$server->getURLGenerator();
    return [
        // The string under which your app will be referenced in owncloud
        'id' => 'metadatarepo',

        // The sorting weight for the navigation.
        // The higher the number, the higher will it be listed in the navigation
        'order' => 10,

        // The route that will be shown on startup
        'href' => $urlGenerator->linkToRoute('metadatarepo.page.index'),

        // The icon that will be shown in the navigation, located in img/
        'icon' => $urlGenerator->imagePath('metadatarepo', 'metadatarepo.svg'),

        // The application's title, used in the navigation & the settings page of your app
        'name' => \OC::$server->getL10N('metadatarepo')->t('Metadata Repository'),
    ];
});


\OCA\MDRepo\Hooks::connectHooks();
$app = new \OCP\AppFramework\App('metadatarepo');

if (\OCP\User::isLoggedIn()) {
    $eventDispatcher = \OC::$server->getEventDispatcher();
    $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {
        \OCP\Util::addscript('metadatarepo', 'metadatarepo');
    });
}
//$app->getContainer()->query('Hooks')->register();