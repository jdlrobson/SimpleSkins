<?php

class SimpleSkinTemplate extends BaseTemplate {
	protected $simpleSkin;

	protected function getOptionalSiteLinkData( $desc, $pageKey ) {
		$sk = $this->getSkin();
		if ( $sk->msg( $desc )->inContentLanguage()->isDisabled() ) {
			// then it is disabled, for all languages.
			return false;
		} else {
			// Otherwise, we display the link for the user, described in their
			// language (which may or may not be the same as the default language),
			// but we make the link target be the one site-wide page.
			$title = Title::newFromText( $sk->msg( $pageKey )->inContentLanguage()->text() );

			if ( !$title ) {
				return false;
			}

			return array(
				'text' => $sk->msg( $desc )->escaped(),
				'href' => $title->getLocalUrl(),
			);
		}
	}

	protected function getSiteLinkData() {
		$data = array(
			'mainpage' => $this->data['nav_urls']['mainpage'],
		);

		$disclaimer = $this->getOptionalSiteLinkData( 'disclaimers', 'disclaimerpage' );
		$about = $this->getOptionalSiteLinkData( 'aboutsite', 'aboutpage' );
		if ( $about ) {
			$data['about'] = $about;
		}
		if ( $disclaimer ) {
			$data['disclaimer'] = $disclaimer;
		}
		return $data;
	}

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

	protected function getFooterData() {
		$data = $this->data;
		$footer = array();
		foreach( $data['footerlinks'] as $rowKey => $rowParts ) {
			$row = array();
			foreach( $rowParts as $element ) {
				if ( $data[$element] ) {
					$row[] = $data[$element];
				}
			}
			$footer[$rowKey] = $row;
		}
		return $footer;
	}

	/**
	 * Template filter callback for Minerva skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 *
	 * @access private
	 */
	function getTemplateParserData() {
		$sk = $this->getSkin();
		$data = $this->data;
		$tdata = $sk->getSimpleConfig();
		$out = $sk->getOutput();

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

		$views = $nav["views"];
		$actions = $nav["actions"];
		if ( isset( $actions["unwatch"] ) ) {
			$actions['watch'] = $actions["unwatch"];
			unset( $actions['unwatch'] );
		}

		// cleanup toolbox urls
		$toolboxUrls = array();

		// cleanup personal urls
		if ( isset( $data['personal_urls']['notifications'] ) ) {
			// clean up Echo to have a more standard structure:
			$data['personal_urls']['notifications']['badge'] = $data['personal_urls']['notifications']['text'];
			$data['personal_urls']['notifications']['text'] = SpecialPage::getTitleFor( 'Notifications' )->getText();
		}
		$personalUrls = $this->prepareLinksForTemplate( $data['personal_urls'] );
		$toolboxUrls = $this->prepareLinksForTemplate( $this->getToolbox() );

		$historyLink = isset( $views["history"] ) ? $views["history"] : false;
		$title = $this->getSkin()->getTitle();
		$pageLanguage = $sk->getLanguage();

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

		// clean up namespaces
		$namespaces = $nav['namespaces'];
		// Generates XML IDs from namespace names
		$subjectId = $title->getNamespaceKey( '' );
		$talkId = $subjectId === 'main' ? 'talk' : "{$subjectId}_talk";

		$showHidden = $sk->getUser()->getBoolOption( 'showhiddencats' ) ||
			$title->getNamespace() == NS_CATEGORY;

		$languageData = array(
			'code' => $pageLanguage->getHtmlCode(),
			'dir' => $pageLanguage->getDir(),
		);
		$tdata = array_merge( $actions, array(
			'skin' => [
				'name' => $sk->getSkinName(),
			],
			'page' => array(
				'indicators' => $this->getIndicators(),
				'isArticle' => $out->isArticle(),
				'exists' => $title->exists(),
				'isMainPage' => $title->isMainPage(),
				'isSpecialPage' => $title->isSpecialPage(),
				'language' => $languageData,
				'toc' => $out->getProperty( 'simple-skin-toc' ),
				'html' => $data['bodytext'],
			),
			'menu' => array(
				'primary' => $data['sidebar']['navigation'],
				'personal' => $personalUrls,
				'toolbox' => $toolboxUrls,
			),
			'history' => $historyLink,
			'view' => isset( $views["view"] ) ? $views["view"] : false,
			'edit' => isset( $views["edit"] ) ? $views["edit"] : false,

			'footer' => $this->getFooterData(),

			'SKIN_START' => $data['headelement'],
			'SKIN_END' => MWDebug::getDebugHTML( $sk->getContext() ) .
					$data['bottomscripts'] . $data['reporttime'] . '</body></html>',
			'site' => array(
				'links' => $this->getSiteLinkData(),
				'name' => $data['sitename'],
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
		if ( isset( $data['title'] ) && $data['title'] ) {
			$tdata['page']['displayTitle'] = array(
				'text' => $data['title'],
				'language' => $tdata['page']['language'],
			);
		}
		if ( isset( $data['subtitle'] ) && $data['subtitle'] ) {
			$tdata['page']['subtitle'] = array(
				'text' => $data['subtitle'],
			);
		}
		if ( isset( $data['language_urls'] ) && $data['language_urls'] ) {
			$tdata['page']['languages'] = array(
				'current' => $languageData,
				'heading' => wfMessage( 'otherlanguages' ),
				// FIXME: https://phabricator.wikimedia.org/T104660
				'href' => SpecialPage::getTitleFor( 'MobileLanguages', $title->getPrefixedDBkey() )->getLocalUrl(),
				'links' => $data['language_urls'],
			);
		}

		if ( !$title->isSpecialPage() ) {
			$tdata = array_merge( $tdata, array(
				'talk' => $namespaces[$talkId],
				'view' => $namespaces[$subjectId],
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
		return $tdata;
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
		$tdata = $this->getTemplateParserData();

		$templateParser = new TemplateParser( $path );
		try {
			echo $templateParser->processTemplate( "template", $tdata );
		} catch ( Exception $e ) {
			$templateParser = new TemplateParser( __DIR__ . "/../skins/" );
			$tdata['page']['html'] = Html::openElement( 'div', [ 'class' => 'errorbox'] ) .
				wfMessage( 'ext-simple-skin-error-loading', $tdata['skin']['name'] )->parse() .
				Html::closeElement( 'div' ) .
				$tdata['page']['html'];
			echo $templateParser->processTemplate( "error", $tdata );
		}
	}
}