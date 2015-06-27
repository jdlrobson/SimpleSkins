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
	public static function onOutputPageParserOutput( OutputPage &$out, ParserOutput $po ) {
		$skin = $out->getSkin();
		if ( method_exists( $skin, 'getSimpleConfig' ) ) {
			$config = $skin->getSimpleConfig();
			$version = isset( $config['version'] ) ? $config['version'] : 1;
		}
		if ( $version > 1 ) {
			$out->setProperty( 'simple-skin-toc', $po->getTOCHTML() );
			$out->enableTOC( false );
		}
	}
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
				$name = strtolower( $fileinfo->getFilename() );
				$rlModule = array(
					"skins.bacadabra.$name.styles" => $wgSFResourceBoilerplate + array(
						'position' => 'top',
						'styles' => array(
							"skins/$name/styles.less",
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

	protected static function registerAvailableSimpleSkins() {
		$factory = SkinFactory::getDefaultInstance();
		$dir = new DirectoryIterator( dirname( __FILE__ ) . '/../skins/' );
		foreach ( $dir as $fileinfo ) {
			if ( !$fileinfo->isDot() && $fileinfo->isDir() ) {
				$skinName = $fileinfo->getFilename();
				$skinKey = "bacadabra/" . strtolower( $skinName );
				$factory->register( $skinKey, $skinName, function () use ( $skinKey ) {
					$skin = new SkinBacadabra( $skinKey );
					$skin->setSimpleSkinName( $parts[1] );
					return $skin;
				} );
			}
		}
	}

	public static function onSetupAfterCache() {
		self::registerAvailableSimpleSkins();
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
		$userSkin = $context->getUser()->getOption( 'skin' );
		$userSkin = $context->getRequest()->getVal( 'useskin', $userSkin );
		$parts = explode( '/', $userSkin );
		if ( $parts[0] === 'bacadabra' ) {
			$skinName = 'SkinBacadabra';
			$skin = new $skinName( $context );
			$skin->setSimpleSkinName( $parts[1] );
		}
		return false;
	}
}
