<?php

class SkinBacadabraTemplate extends BaseTemplate {
	protected $simpleSkin;

	protected function prepareLinksForTemplate( $array ) {
		$cleanedArray = array();
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) && !isset( $val['links'] ) ){
				if ( isset( $val['msg'] ) ) {
					$val['text'] = wfMessage( $val['msg'] )->text();
				}
				if ( isset( $val['class'] ) && is_array( $val['class'] ) ) {
					$val['class'] = implode( ' ', $val['class'] );
				}
				// This is horrible - but needed for Whatlinks here
				if ( !isset( $val['text'] ) && isset( $val['id'] ) ) {
					$parts = explode( '-', $val['id'] );
					if ( isset( $parts[1] ) ) {
						$key = $parts[1];
						$val['text'] = wfMessage( $key )->text();
					}
				}
				$val['name'] = $key;

				$cleanedArray[] = $val;
			}
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
		$out = $sk->getOutput();
		$name = $sk->getSimpleSkinName();

		$path = __DIR__ . "/../skins/$name";
		$templateParser = new TemplateParser( $path );
		$data = $this->data;
		$tdata = $sk->getSimpleConfig();

		$msgObj = array();
		if ( isset( $tdata['messages'] ) ) {
			foreach ( $tdata['messages'] as $msgKey ) {
				$msgObj[$msgKey] = wfMessage( $msgKey )->text();
			}
		}
		$tdata['messages'] = array_merge( $msgObj, array(
			'toolbox' => wfMessage( 'toolbox' ),
			'otherlanguages' => wfMessage( 'otherlanguages' ),
		) );

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

		$historyLink = isset( $views["history"] ) ? $views["history"] : false;
		$title = $this->getSkin()->getTitle();
		$pageLanguage = $title->getPageViewLanguage();

		if ( $historyLink ) {
			$revId = $this->getSkin()->getRevisionId();
			$timestamp = Revision::getTimestampFromId( $title, $revId );
			$rev = Revision::newFromId( $revId );

			$historyLink["info"] = $data['lastmod'];
			$historyLink["edit-timestamp"] = wfTimestamp( TS_UNIX, $timestamp );

			if ( $rev ) {
				$userId = $rev->getUser();
			} else {
				$userId = false;
			}
			if ( $userId ) {
				$revUser = User::newFromId( $userId );
				$revUser->load( User::READ_NORMAL );
				$historyLink["editor-username"] = $revUser->getName();
				$historyLink["editor-gender"] = $revUser->getOption( 'gender' );
			}
		}

		$showHidden = $sk->getUser()->getBoolOption( 'showhiddencats' ) ||
			$title->getNamespace() == NS_CATEGORY;

		$tdata = array_merge( $actions, array(
			'sitename' => $data['sitename'],
			'namespaces' => array_values( $namespaces ),

			// language
			'userlangattributes' => $data['userlangattributes'],
			'page' => array(
				'displayTitle' => $data['title'],
				'isArticle' => $out->isArticle(),
				'exists' => $title->exists(),
				'isMainPage' => $title->isMainPage(),
				'isSpecialPage' => $title->isSpecialPage(),
				'language' => array(
					'code' => $pageLanguage->getHtmlCode(),
					'dir' => $pageLanguage->getDir(),
				),
				'hasLanguages' => count( $data['language_urls'] ) > 1,
				'toc' => $out->getProperty( 'simple-skin-toc' ),
			),
			'subtitle' => $data['subtitle'],

			'indicators' => $this->getIndicators(),

			'history' => $historyLink,
			'view' => isset( $views["view"] ) ? $views["view"] : false,
			'edit' => isset( $views["edit"] ) ? $views["edit"] : false,

			'footerRows' => $footerRows,

			'personalUrls' => $personalUrls,
			'languageUrls' => $data['language_urls'],
			'toolboxUrls' => $toolboxUrls,

			'headelement' => $data['headelement'],
			'sidebarPrimaryLinks' => $data['sidebar']['navigation'],
			'bodytext' => $data['bodytext'],
			'reporttime' => $data['reporttime'],
			'bottomscripts' => $data['bottomscripts'],
			'debug' => MWDebug::getDebugHTML( $this->getSkin()->getContext() ),
			'icons' => array(
				'poweredby' => $data['poweredbyico'],
				'copyright' => $data['copyright'],
			),

			'search' => array(
				'action' => $data['wgScript'],
				'input' => $this->makeSearchInput( array( 'id' => 'searchInput', 'class' => 'search' ) ),
				'button' => $this->makeSearchButton( 'go', array( "id" => "searchGoButton", "class" => "searchButton" ) ),
				'buttonfulltext' => $this->makeSearchButton( "fulltext", array( "id" => "mw-searchButton", "class" => "searchButton" ) ),
			)
		), $tdata );

		// Enrich data with categories if they exist.
		$allCats = $out->getCategoryLinks();
		if ( !empty( $allCats['normal'] ) || !empty( $allCats['hidden'] ) ) {
			$tdata['page']['categories'] = array_merge( $out->getCategoryLinks(), array(
				'link' => array(
					'href' => SpecialPage::getTitleFor( 'Special:Categories' )->getLocalUrl(),
					'text' => wfMessage( 'categories' ),
				),
				'allHidden' => empty( $allCats['normal'] ) && !( !empty( $allCats['hidden'] ) && $showHidden ),
			) );
		}

		$undelete = $sk->getUndeleteLink();
		if ( $undelete ) {
			$tdata['restore'] = array(
				'title' => $undelete,
				'text' => wfMessage( 'undeletebtn' ),
				'href' => SpecialPage::getTitleFor( 'Undelete', $title->getPrefixedDBkey() )->getLocalUrl(),
			);
		}

		// move, watch, delete
		foreach( $actions as $key => $action ) {
			$tdata[$key] = $action;
		}

		echo $templateParser->processTemplate( "template", $tdata );
	}
}