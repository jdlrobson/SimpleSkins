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
			$legacy = isset( $config['legacy'] ) ? $config['legacy'] : false;
		} else {
			$legacy = false;
		}

		if ( !$legacy ) {
			$out->setProperty( 'simple-skin-toc', $po->getTOCHTML() );
			$po->setTOCEnabled( false );
		}
	}

	protected static function getFiles( $name, $subfolder = 'scripts',
		$standaloneFile = 'init.js'
	) {
		$files = array();
		$file = "skins/$name/$standaloneFile";
		if ( file_exists( dirname( __FILE__ ) . '/../' . $file ) ) {
			$files[] = $file;
		}
		$path = dirname( __FILE__ ) . "/../skins/$name/$subfolder";
		if ( file_exists( $path ) ) {
			$dir = new DirectoryIterator( $path );
			foreach ( $dir as $fileinfo ) {
				if ( !$fileinfo->isDot() ) {
					$file = $fileinfo->getFilename();
					$files[] = "skins/$name/$subfolder/$file";
				}
			}
		}
		return $files;
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
		$boilerplate = [
			'localBasePath' => dirname( __DIR__ ),
			'remoteExtPath' => 'SimpleSkins',
		];
		$dir = new DirectoryIterator( dirname( __FILE__ ) . '/../skins/' );
		foreach ( $dir as $fileinfo ) {
			if ( !$fileinfo->isDot() && $fileinfo->isDir() ) {
				$name = strtolower( $fileinfo->getFilename() );
				$rlModule = array(
					"skins.bacadabra.$name.styles" => $boilerplate + [
						'position' => 'top',
						'styles' => self::getFiles( $name, 'styles', 'styles.less' ),
					],
					"skins.bacadabra.$name.scripts" => $boilerplate + [
						'position' => 'top',
						'scripts' => self::getFiles( $name, 'scripts', 'init.js' ),
					],
				);
				$resourceLoader->register( $rlModule );
			}
		}
		return true;
	}

	protected static function registerAvailableSimpleSkins() {
		global $wgValidSkinNames;
		$factory = SkinFactory::getDefaultInstance();
		$dir = new DirectoryIterator( dirname( __FILE__ ) . '/../skins/' );
		foreach ( $dir as $fileinfo ) {
			if ( !$fileinfo->isDot() && $fileinfo->isDir() ) {
				$skinName = $fileinfo->getFilename();
				$skinKey = strtolower( $skinName );
				if ( file_exists( $fileinfo->getPath() . '/' . $skinName . '/template.mustache' ) &&
					!isset( $wgValidSkinNames[$skinKey] )
				) {
					$factory->register( $skinKey, $skinName, function () use ( $skinKey ) {
						$skin = new SimpleSkin( $skinKey );
						$skin->setSimpleSkinName( $skinKey );
						return $skin;
					} );
				}
			}
		}
	}

	public static function onSetupAfterCache() {
		self::registerAvailableSimpleSkins();
		return true;
	}
}
