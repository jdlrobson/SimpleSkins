# SimpleSkins
MediaWiki extension to make skin generation easier

This reduces a skin down to template file, a css file, a JavaScript file and a static json file.
It abstracts a way a lot of the confusion there might be in creating a skin.

Not production ready but hopefully a talking point.

To install:
1) Add to your LocalSettings

  require_once "$IP/extensions/SimpleSkins/SimpleSkins.php";
  
2) View your wiki with your new Simple skin.

3) Create your own skin under the skins folder

4) Turn your new skin on in your LocalSettings with this simple line:

	$wgSFDefaultSimpleSkin = '<Folder name>';
