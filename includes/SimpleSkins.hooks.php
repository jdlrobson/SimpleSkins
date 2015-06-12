<?php
/**
 * SkinFrameworkHooks.php
 */

/**
 * Hook handlers for MobileFrontend extension
 *
 * Hook handler method names should be in the form of:
 *	on<HookName>()
 * For intance, the hook handler for the 'RequestContextCreateSkin' would be called:
 *	onRequestContextCreateSkin()
 */
class SimpleSkinsHooks {
	/**
	 * ResourceLoaderRegisterModules hook handler
	 *
	 * Registers the mobile.loggingSchemas module without a dependency on the
	 * ext.EventLogging module so that calls to the various log functions are
	 * effectively NOPs.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ResourceLoaderRegisterModules
	 *
	 * @param ResourceLoader &$resourceLoader The ResourceLoader object
	 * @return bool Always true
	 */
	public static function onResourceLoaderRegisterModules( ResourceLoader &$resourceLoader ) {
		global $wgSFResourceBoilerplate;
		$dir = new DirectoryIterator( dirname( __FILE__ ) . '/../skins/' );
		foreach ( $dir as $fileinfo ) {
			if ( !$fileinfo->isDot() && $fileinfo->isDir() ) {
				$name = $fileinfo->getFilename();
				$rlModule = array(
					"skins.bacadabra.$name.styles" => $wgSFResourceBoilerplate + array(
						'position' => 'top',
						'styles' => array(
							"skins/$name/styles.css",
						),
					),
					"skins.bacadabra.$name.scripts" => $wgSFResourceBoilerplate + array(
						'position' => 'top',
						'scripts' => array(
							"skins/$name/init.js",
						),
					),
				);
				$resourceLoader->register( $rlModule );
			}
		}
		return true;
	}

	/**
	 * RequestContextCreateSkin hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/RequestContextCreateSkin
	 *
	 * @param IContextSource $context
	 * @param Skin $skin
	 * @return bool
	 */
	public static function onRequestContextCreateSkin( $context, &$skin ) {
		$skinName = 'SkinBacadabra';
		$skin = new $skinName( $context );
		return false;
	}
}
