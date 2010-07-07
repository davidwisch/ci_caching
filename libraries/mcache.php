<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Mcache Class
*
* @author David Wischhusen <davewisch@gmail.com>
*/
class Mcache{

	public $CI;
	public $servers;
	public $memcached;
	public $cache_name;
	public $cache_expires;

	/**
	* Mcache constructor
	*
	* @access public
	*/
	public function Mcache(){
		$this->CI = &get_instance();

		$this->servers = $this->CI->config->item('memcached_servers');

		$this->memcached = new Memcache;

		foreach($this->servers as $server){
			$this->memcached->addServer($server['host'], $server['port']);
		}
		
		$this->cache_name = false;

		define('ONE_HOUR', 3600);
		define('TWO_HOURS', 7200);
		define('FIVE_HOURS', 18000);
		define('ONE_DAY', 86400);
		define('ONE_WEEK', 604800);
		define('MAXIMUM', 0);
	}

////////////REGULAR CACHING
	/**
	* Places data in the cache
	*
	* @access public
	* @param string
	* @param mixed
	* @param int
	* @return bool
	*/
	public function put($key, $value, $expires=MAXIMUM){
		return $this->_cache($key, $value, 0, $expires);
	}

	/**
	* Retrieves data from the cache
	*
	* @access public
	* @param string
	* @return mixed
	*/
	public function get($key){
		return $this->_retrieve($key);
	}

	/**
	* Deletes data from the cache
	*
	* @access public
	* @param string
	* @return bool
	*/
	public function expire($key){
		return $this->memcached->delete($key);
	}

/////////////FRAGMENT CACHING
	/**
	* Start caching a markup fragment
	*
	* @access public
	* @param string
	* @param int
	* @return bool
	*/
	public function start_fragment($name=false, $expires=MAXIMUM){
		if($name === false){
			$back = debug_backtrace();
			$str = md5($this->CI->uri->uri_string().'||'.$back[0]['line']);
			$this->cache_name = md5($str);
		}
		else{
			$this->cache_name = $name;
		}

		if($cache = $this->get($this->cache_name)){
			$this->cache_on = false;
			echo $cache;
			return false;
		}
		//start cache
		$this->cache_on = true;
		$this->cache_expires = $expires;
		ob_start();
		return true;
	}

	/**
	* End a markup fragment
	*
	* @access public
	*/
	public function end_fragment(){
		if($this->cache_on){
			$contents = ob_get_contents();
			ob_end_clean();
			$this->_cache($this->cache_name, $contents, $this->cache_expires);
			echo $contents;
		}
	}

////////////SPECIFICS
	/**
	* Places a boolean into cache
	*
	* Because Memcache::get can return a boolean as a status, storing
	* booleans can be problematic, this is a workaround
	*
	* @access public
	* @param string
	* @param mixed
	* @param int
	* @return bool
	*/
	public function put_bool($key, $value, $expires=MAXIMUM){
		if(!is_bool($value)){return false;}

		$bool_construct = array(
			'type' => 'mcache:boolean',
			'value' => $value
			);
		return $this->_cache($key, $bool_construct, $expires);
	}

	/**
	* Retrieves a boolean from cache
	*
	* @access public
	* @param string
	* @return bool
	*/
	public function get_bool($key){
		$store = $this->_retrieve($key);
		if($store === false){return null;}

		if(isset($store['type']) && $store['type'] == 'mcache:boolean'){
			return $store['value'];
		}
		return null;
	}

	/**
	* Increments a value in the cache
	*
	* @access public
	* @param string
	* @param int
	* @return int
	*/
	public function increment($key, $increment=1){
		if($this->_retrieve($key) === false){
			$this->_cache($key, $increment);
			return $increment;
		}
		return $this->memcached->increment($key, $increment);
	}

	/**
	* Decrements a value in the cache
	*
	* @access public
	* @param string
	* @param int
	* @return int
	*/
	public function decrement($key, $decrement=1){
		return $this->memcached->decrement($key, $decrement);
	}

//////////////OTHER
	/**
	* Cleares all objects in the cache
	*
	* @access public
	* @return bool
	*/
	public function expire_all(){
		return $this->memcached->flush();
	}

/////////////PRIVATE
	/**
	* Internal function for placing an object in the cache
	*
	* @access private
	* @param string
	* @param mixed
	* @param int
	* @return bool
	*/
	private function _cache($key, $value, $expires=MAXIMUM){
		if($ret = $this->memcached->replace($key, $value, 0, $expires) === false){
			return $this->memcached->set($key, $value, 0, $expires);
		}
		return $ret;
	}

	/**
	* Internal function for retrieving an object from the cache
	*
	* @access private
	* @param string
	* @return mixed
	*/
	private function _retrieve($key){
		return $this->memcached->get($key);
	}
}

/* End of file mcache.php */
