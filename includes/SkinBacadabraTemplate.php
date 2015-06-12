<?php
class SkinBacadabraTemplate extends BaseTemplate {
	protected $simpleSkin;

	/**
	 * Template filter callback for Minerva skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function execute() {
		$sk = $this->getSkin();
		$name = $sk->getSimpleSkinName();

		$path = __DIR__ . "/../skins/$name";
		$templateParser = new TemplateParser( $path );
		$data = $this->data;
		$string = file_get_contents("$path/config.json");
		$tdata = json_decode( $string, true );

		$tdata = array_merge( array(
			'title' => $data['title'],
			'talk' => $data['content_navigation']['namespaces']["talk"],
			'history' => $data['content_navigation']["views"]["history"],
			'edit' => $data['content_navigation']["views"]["edit"],
			'move' => $data['content_navigation']["actions"]["move"],

			'headelement' => $data['headelement'],
			'sidebar' => $data['sidebar']['navigation'],

			'bodytext' => $data['bodytext'],
			'bottomscripts' => $data['bottomscripts'],
		), $tdata );
		if ( isset( $data['content_navigation']["actions"]["unwatch"] ) ) {
			$tdata['unwatch'] = $data['content_navigation']["actions"]["unwatch"];
		}
		if ( isset( $data['content_navigation']["actions"]["watch"] ) ) {
			$tdata['unwatch'] = $data['content_navigation']["actions"]["watch"];
		}
		echo $templateParser->processTemplate( "template", $tdata );
	}
}