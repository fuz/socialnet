<?php

class SocialHelpers extends HamlHelpers {

	public static function link_to($block, $atts, $display) {
		$link = "<a";
		foreach($atts as $att => $value) {
			$link .= ' ' . $att . '="' . $value . '"';
		}
		$link .= '>' . $display . '</a>';
		return $link;
	}


	public static function person_link($block, $display, $guid) {
		$data = array(
			'href'=> page_filename(SECTION_USERPROFILE) . '?' . $guid,
			'title' => 'written by ' . $display
			);
		return self::link_to($block, $data, $display);
	}

	public static function userprofile($block, $guid) {
		$udb = StaticAccounts::getUDB();
		$profile = $udb->getProfile($guid);
		return $profile;
	}

	public static function profile($block, $guid, $data) {
		$profile = self::userprofile($block, $guid);
		return $profile->$data;
	}

}

?>