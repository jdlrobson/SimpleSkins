<?php
/**
 * Inherit main code from SkinTemplate, set up the CSS and template.
 * @ingroup Skins
 */
class SkinBacadabra extends SkinTemplate {
	public $skinname = 'bacadabra';
	public $stylename = 'bacadabra';
	public $template = 'SkinBacadabraTemplate';
	public $useHeadElement = true;
	// FIXME: Make variant configurable per user
	protected $simpleSkin;

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

	public function __construct() {
		global $wgSFDefaultSimpleSkin;
		$this->simpleSkin = $wgSFDefaultSimpleSkin;
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
	}
}
