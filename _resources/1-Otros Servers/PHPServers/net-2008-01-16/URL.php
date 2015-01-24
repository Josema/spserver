<?
// Require class
require_once("HttpURLConnection.php");

/**
 * Class for getting information about URL's
 * 
 * @author	Gustavo Gomes
 * @author	Sven Wagener
 * @version	0.9
 */
final class URL {
	
	private $url = "";
	
	private $protocol;
	
	private $host;
	
	private $path;
	
	private $file;
	
	private $query;
	
	private $userInfo;
	
	/**
	* Constructor of class url
	* 
	* @param	string $url - the complete url
	*/
	public function __construct($url) {
		if (eregi(".",$url) && !eregi("://",$url))
			$url = "http://".$url;

		$this->url = $url;
		$urlInfo = parse_url($this->url);
		$this->host = (isset($urlInfo['host'])) ? $urlInfo['host'] : "";
		$this->path = (isset($urlInfo['path'])) ? $urlInfo['path'] : "";
		$this->ref = (isset($urlInfo['fragment'])) ? $urlInfo['fragment'] : "";
		$this->protocol = (isset($urlInfo['scheme'])) ? $urlInfo['scheme'] : "";
		$this->query = (isset($urlInfo['query'])) ? $urlInfo['query'] : "";
		$this->userInfo = (isset($urlInfo['user']) && isset($urlInfo['pass'])) ? $urlInfo['user'].":".$urlInfo['pass'] : "";
		
		if ($this->path == "")
			$this->path = "/";
	}
	
	public function getExternalForm() {
		return $this->url;
	}
	
	/**
	 * Returns if the parameter is equal to this url
	 * 
	 * @param	URL
	 */
	public function equals($url) {
		if (is_object($url) && $url instanceof URL)
			return ($this->url == $url->getExternalForm());
		else
			return false;
	}
	
	/**
	 * Returns the protocol used in this url
	 * 
	 * @return	string
	 */
	public function getProtocol() {
		return $this->protocol;
	}
	
	/**
	* Returns the host of the url
	* 
	* @return string - the host of the url
	*/
	public function getHost() {
		return $this->host;
	}
	
	/**
	* Returns the path of the url
	* 
	* @return string - the path of the url
	*/
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * Returns query string case it is defined
	 * 
	 * @return	string
	 */
	public function getQuery() {
		return $this->query;
	}
	
	/**
	 * Returns user informations case it is defined
	 * 
	 * @return	string
	 */
	public function getUserInfo() {
		return $this->userInfo;
	}
	
	/**
	 * Open a connection with for this url
	 * 
	 * @return	HttpURLCoonection
	 */
	public function openConnection() {
		return new HttpURLConnection($this);
	}
	
	/**
	* Returns the content of the url without the headers
	* 
	* @return string - the content
	*/
	public function getContent() {
		// Get a web page into a string
		return implode ("",file($this->url));
	}
	
	/**
	 * Sets all parameters of the url
	 * 
	 * @param	string $protocol
	 * @param	string $host
	 * @param	int $port
	 * @param	string $path
	 * @param	string $ref
	 */
	public function set($protocol,$host,$port,$path,$ref="") {
		$this->protocol = $protocol;
		$this->host = str_replace("/","",$host);
		$this->port = ($port > 0) ? $port : 80;
		$this->path = $path;
		$this->ref = $ref;
		$this->url = $protocol."://".$this->host.":".$this->port."/".$this->path;
		if ($this->ref != "")
			$this->url .= "#".$this->ref;
	}
}
?>