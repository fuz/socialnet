<?php

require_once('static.php');
require_once('phamlp/haml/HamlParser.php');

/**
Basic helper utility for deciding whether or not a template needs re-parsing.
*/

class Templating {
	public $folder;
	public function __construct($base = TEMPLATES_DIR) {
		$this->folder = $base;
	}

	public function showTemplate($template) {
		$filename = $this->folder . $template . TEMPLATE_EXT;
		echo "<!-- Looking for template ", $filename, "-->";
		
		if (file_exists($filename) === FALSE) {
			echo "Cannot find template: ", $template, "(",$filename,")";
			return;
		}
		
		// get the compiled PHP template
		$compiled = COMPILED_DIR . filemtime($filename) . basename($filename, TEMPLATE_EXT) . COMPILEDTEMPLATE_EXT;
		// create a compiled template if there is none
		if (!file_exists($compiled)) {
			// now delete old templates if they exist:
			
			$this->purge_old($template);
		
			// compile the file
			$haml = new HamlParser(array(
				'ugly' => false,
				'helperFile' => HAML_HELPERS_FILE,
				'style' => 'nested')
			);
			$xhtml = $haml->parse($filename);
			$fp = fopen($compiled, 'w');
			fwrite($fp, $xhtml);
			fclose($fp);
			
		}
		return $compiled;
	}

	public function purge_old($template) {
		$files = scandir(COMPILED_DIR);
		$pattern = $template . COMPILEDTEMPLATE_EXT;
		$filefinder = function($match) use ($pattern) {
			return strrpos($match, $pattern) !== FALSE;
		};
	
		$expired = array_filter($files, $filefinder);

		foreach ($expired as $file) {
			unlink(COMPILED_DIR . $file);
		}
	}
}

?>