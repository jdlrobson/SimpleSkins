<?php
/**
 * Inherit main code from SkinTemplate, set up the CSS and template.
 * @ingroup Skins
 */
class SimpleSkin extends SkinTemplate {
	public $template = 'SimpleSkinTemplate';
	public $useHeadElement = true;
	protected $simpleSkin;

	public function __construct() {
		global $wgSimpleSkinDefault;
		$this->simpleSkin = $wgSimpleSkinDefault;
	}
	/**
	 * initialize various variables and generate the template
	 * @return QuickTemplate
	 */
	protected function prepareQuickTemplate() {
		$tpl = parent::prepareQuickTemplate();
		$out = $this->getOutput();
		// FIXME: Should be configurable in config.json
		$out->addHeadItem( 'viewport',
			Html::element(
				'meta', array(
					'name' => 'viewport',
					'content' => 'initial-scale=1.0, user-scalable=yes, minimum-scale=0.25, ' .
						'maximum-scale=5.0, width=device-width',
				)
			)
		);
		return $tpl;
	}

	public function getSimpleConfig() {
		$path = __DIR__ . "/../skins/$this->simpleSkin";
		if ( file_exists( "$path/config.json" ) ) {
			$string = file_get_contents("$path/config.json");
			$tdata = json_decode( $string, true );
			return $tdata;
		} else {
			return array();
		}
	}

	protected function skinExists( $name ) {
		return file_exists( __DIR__ . "/../skins/$name" );
	}

	public function setSimpleSkinName( $name ) {
		if ( $this->skinExists( $name ) ) {
			$this->simpleSkin = $name;
			$this->skinname = $name;
		}
	}

	public function getSimpleSkinName() {
		return $this->simpleSkin;
	}

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		$name = $this->getSimpleSkinName();
		parent::setupSkinUserCss( $out );
		// Add the ResourceLoader module to the page output
		$out->addModuleStyles( "skins.bacadabra.$name.styles" );
		$out->addModules( "skins.bacadabra.$name.scripts" );
		$config = $this->getSimpleConfig();
		if ( isset( $config['styles'] ) ) {
			$out->addModuleStyles( $config['styles'] );
		}
		if ( isset( $config['scripts'] ) ) {
			$out->addModules( $config['scripts'] );
		}
	}
}
