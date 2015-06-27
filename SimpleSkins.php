<?php

/**
 * Extension Gather
 *
 * @file
 * @ingroup Extensions
 * @author Jon Robson
 * @licence GNU General Public Licence 2.0 or later
 */

// Needs to be called within MediaWiki; not standalone
if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This is a MediaWiki extension and cannot run standalone.\n";
	die( -1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'SimpleSkins',
	'author' => array( 'Jon Robson' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:SimpleSkins',
	'license-name' => 'GPL-2.0+',
);

// autoload extension classes
$autoloadClasses = array (
	'SimpleSkinsHooks' => 'SimpleSkins.hooks',
	'SkinBacadabra' => 'SkinBacadabra',
	'SkinBacadabraTemplate' => 'SkinBacadabraTemplate',
);

foreach ( $autoloadClasses as $className => $classFilename ) {
	$wgAutoloadClasses[$className] = __DIR__ . "/includes/$classFilename.php";
}

$wgSFResourceBoilerplate = array(
	'targets' => array( 'desktop', 'mobile' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'SimpleSkins',
);

$wgSimpleSkinDefault = 'Dali';

$wgHooks['OutputPageParserOutput'][] = 'SimpleSkinsHooks::onOutputPageParserOutput';
$wgHooks['SetupAfterCache'][] = 'SimpleSkinsHooks::onSetupAfterCache';
$wgHooks['RequestContextCreateSkin'][] = 'SimpleSkinsHooks::onRequestContextCreateSkin';
$wgHooks['ResourceLoaderRegisterModules'][] = 'SimpleSkinsHooks::onResourceLoaderRegisterModules';
