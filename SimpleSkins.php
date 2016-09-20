<?php

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'SimpleSkins' );
} else {
	die( 'This version of the SimpleSkins extension requires MediaWiki 1.25+' );
}
