# A Caching Library for CodeIgniter

***

## About

I thought the native caching functions in CodeIgniter were a bit lacking so I developed this.

## Usage

### Installation

Copy **libraries/mcache.php** to the libraries/ folder in your application directory.
Copy **config/appconfig.php** to the config/ folder in your application directory.

### Initialization

From a controller, simply load the library:

`$this->load->library('mcache');`

Alternativly, you can load the library from the config/autoload.php file.

### Configuration

This library takes only a single configuration option (found in config/appconfig.php).

* $config['memcached_servers'] - *An array of associative arrays, each containing a 'host' and 'port' key.  Set as many of these as you'd like.*

**Example**

	$config['memcached_servers'] = array(
			array(
				'host' => '127.0.0.1',
				'port' => 11211
			)
		);

### Usage

This library contains the following functions: **put()**, **get()**, **expire()**, **start_fragment()**, **end_fragment()**, **put_bool()**, **get_bool()**, **increment()**, **decrement()**, and **expire_all()**.

This library also defines a some constants that are helpful when setting expirations for cache keys.  The constants are: **ONE_HOUR**, **TWO_HOURS**, **FIVE_HOURS**, **ONE_DAY**, **ONE_WEEK**, and **MAXIMUM**.

`$this->mcache->put($key, $value, $expires=MAXIMUM);`

Places the key/value pair into cache.  The optional third parameter (which defaults to MAXIMUM) lets you specify (in seconds) how long until that key expires.

`$this->mcache->get($key);`

Retrieves the value of the given key.

`$this->mcache->expire($key);`

Expires the given key.

`$this->mcache->start_fragment($name=false, $expires=MAXIMUM);`

Starts a fragment cache.  If $name is omitted/false, a key is generated automatically.

`$this->mcache->end_fragment();`

Ends a fragment cache.

The fragment caching should be used similarly to this:


	<? if($this->mcache->start_fragment()){?>

	Text

	Text

	Text

	<? } $this->mcache->end_fragment() ?>

**The fragment caching is heavily based on: [http://codeigniter.com/wiki/Fragment_Caching_Library/](http://codeigniter.com/wiki/Fragment_Caching_Library/ "CodeIgniter Wiki Article").**

`$this->mcache->put_bool($key, boolean $value, $expires=MAXIMUM);`

Stores a boolean value in the cache.  Because of the return types of the underlying memcached function *get()*, it's not possible to determine if a return of 'false' is the value of the given key or a cache miss.  Therefore, this function should be used to store boolean values in cache.

`$this->mcache->get_bool($key);`

Retrieves a boolean from cache.

`$this->mcache->increment($key, $increment=1);`

Increments the value of key in the cache (or creates it if no value existed).

`$this->mcache->decrement($key, $decrement=1);`

Decrements the value of key in the cache.

`$this->mcache->expire_all();`

Cleares all objects in the cache.
