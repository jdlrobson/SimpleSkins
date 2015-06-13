<?php
class SkinBacadabraTemplate extends BaseTemplate {
	protected $simpleSkin;

	protected function prepareLinksForTemplate( $array ) {
		$cleanedArray = array();
		foreach ( $array as $key => $val ) {
			if ( isset( $val['msg'] ) ) {
				$val['text'] = wfMessage( $val['msg'] )->text();
			}
			if ( isset( $val['class'] ) && is_array( $val['class'] ) ) {
				$val['class'] = implode( ' ', $val['class'] );
			}
			// This is horrible - but needed for Whatlinks here
			if ( !isset( $val['text'] ) ) {
				$val['text'] = wfMessage( explode( '-', $val['id'] )[1] )->text();
			}
			$cleanedArray[] = $val;
		}
		return $cleanedArray;
	}

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
		if ( file_exists( "$path/config.json" ) ) {
			$string = file_get_contents("$path/config.json");
			$tdata = json_decode( $string, true );
		} else {
			$tdata = array();
		}

		$nav = $data['content_navigation'];
		$namespaces = $nav['namespaces'];
		$views = $nav["views"];
		$actions = $nav["actions"];
		if ( isset( $actions["unwatch"] ) ) {
			$actions['watch'] = $actions["unwatch"];
			unset( $actions['unwatch'] );
		}

		// cleanup toolbox urls
		$toolboxUrls = array();

		// cleanup personal urls
		$personalUrls = $this->prepareLinksForTemplate( $data['personal_urls'] );
		$toolboxUrls = $this->prepareLinksForTemplate( $this->getToolbox() );

		$footerRows = array();
		foreach( $data['footerlinks'] as $rowKey => $rowParts ) {
			$row = array();
			foreach( $rowParts as $element ) {
				if ( $data[$element] ) {
					$row[] = $data[$element];
				}
			}
			$footerRows[] = $row;
		}
		$tdata = array_merge( array(
			'sitename' => $data['sitename'],
			'namespaces' => array_values( $namespaces ),

			'title' => $data['title'],
			'history' => isset( $views["history"] ) ? $views["history"] : false,
			'edit' => isset( $views["edit"] ) ? $views["edit"] : false,
			'actions' => $actions,

			'messages' => array(
				'toolbox' => wfMessage( 'toolbox' ),
				'otherlanguages' => wfMessage( 'otherlanguages' ),
			),
			'footerRows' => $footerRows,

			'personalUrls' => $personalUrls,
			'languageUrls' => $data['language_urls'],
			'toolboxUrls' => $toolboxUrls,

			'headelement' => $data['headelement'],
			'sidebarPrimaryLinks' => $data['sidebar']['navigation'],
			'bodytext' => $data['bodytext'],
			'bottomscripts' => $data['bottomscripts'],
			'icons' => array(
				'poweredby' => $data['poweredbyico'],
				'copyright' => $data['copyright'],
			),
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