<?php 

/**
 * 
 */
class View {
	
	private $context;
	private $extension;
	
	public function __construct($extension = null)
	{
		$this->extension = $extension;
		$this->context = array();
	}
	
	public function assign($name, $value)
	{
		$this->context[$name] = $value;
		return $this;
	}
	
	public function resolve($file)
	{
		$path = is_dir(rtrim($file, '/')) ? rtrim($file, '/') . '/index' : $file;
		$check_list = array($path);
		if(!is_null($this->extension)) {
			$check_list[] = "$path.{$this->extension}.php";
			$check_list[] = "{$this->extension}.php";
		}
		$check_list[] = "$path.php";
		
		foreach($check_list as $pathname)
			if(file_exists($pathname))
				return $pathname;
		return null;
	}
	
	public function render($file, array $context = null)
	{
		$prev_context = $this->context;
		$path = $this->resolve($file);
		if(!file_exists($path)) {
			throw new Exception("$file is not found.");
		}
		if(is_array($context)) {
			$this->context = array_merge($prev_context, $context);
		}
		$result = self::renderFile($this, $path, $this->context);
		$this->context = $prev_context;
		return $result;
	}
	
	private static function renderFile(View $view)
	{
		try {
			extract(func_get_arg(2));
			ob_start();
			require(func_get_arg(1));
		} catch(Exception $e) {
			ob_end_clean();
			return new RuntimeException(__CLASS__ . '::Exception failed render `'.func_get_arg(1).'`', 0, $e);
		}
		return ob_get_clean();
	}
	
}

?>