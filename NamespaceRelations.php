<?php

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This is the LanguageInflection extension. Please see the README file for installation instructions.\n";
    exit( 1 );
}

// Primary stuff
$wgAutoloadClasses['NamespaceRelationsHooks'] = dirname( __FILE__ ) . '/NamespaceRelations.hooks.php';
$wgAutoloadClasses['NamespaceRelations'] = dirname( __FILE__ ) . '/NamespaceRelations_body.php';

// Internationalization
$wgExtensionMessagesFiles['NamespaceRelations'] = dirname( __FILE__ ) . '/NamespaceRelations.i18n.php';

// Attaching to hooks
$wgHooks['SkinTemplateNavigation'][] = 'NamespaceRelationsHooks::onSkinTemplateNavigation';

$wgExtensionCredits['other'][] = array(
    'path'           => __FILE__,
    'name'           => 'NamespaceRelations',
    'author'         => array( 'Pavel Selitskas' ),
    'url'            => 'https://www.mediawiki.org/wiki/Extension:NamespaceRelations',
    'descriptionmsg' => 'nsrels-desc',
    'version'		 => '0.1',
);

// global variables
/**
 * Define extra namespaces and how they relate to the basic set of namespaces.
 */
$wgNamespaceRelations = array();