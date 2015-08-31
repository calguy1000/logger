# logger
A simple logging utility with rotation, concurrency, repeat counting and search support.

This library provides the ability for your application to log debug messages, info messages, warnings and errors to a text file.  The library supports rotation, counting sequentially repeated messages, writing to the same file from multiple processes,  and querying the files.  It is suitable for applications that need various amounts of debugging, logging or auditing capabilities.

# Features
- Can count identical sequental messages
- Can track separate (or combined) keys.  Known as a "section" and an "item"
- Supports log rotation based on file size (kilobytes) or age (hours)
- Manage disk space by only keeping a maximum number of log files.
- Uses exclusive locking to lock filed for writing
- Supports simple querying of saved log files
- Supports three different access methods depending upon your needs or preferences.

There are three separate ways to log to a text file:
- The calguy1000\logger\Logger class
- The calguy1000\logger\SimpleLogger class - A singleton class
- Individual debugging functions.

# Using the static functions
```
// initialize the system
require '../vendor/autoload.php'
use calguy1000\logger as logger;
logger\init('logfile.txt');
logger\debug('A debug message');
logger\info('An information message');
logger\warn('A warning message');
logger\error('A fatal error`);
```

# Using the SimpleLogger class
```
require '../vendor/autoload.php'
use calguy1000\logger\SimpleLogger as logger;
logger::init('logfile.txt');
logger::debug('A debug message');
logger::info('An information message');
logger::warn('A warning message');
logger::error('A fatal error`);
```

# Using the Logger Class
```
require '../vendor/autoload.php'
use calguy1000\logger\Logger as Logger;
$logger = new Logger('logfile.txt');
$logger->debug('A debug message');
$logger->info('An information message');
$logger->warn('A warning message');
$logger->error('A fatal error');
```

# Querying the logs
```
require '../vendor/autoload.php'
use calguy1000\logger\Logger as Logger;
$parms = array('filename'=>'logfile.txt','limit'=>50,'msg'=>'*fatal*`);
$query = new Logger\query($parms);
$rs = $query->execute();
foreach( $rs as $key => $item ) {
   print_r($item);
}
```

# Limitations
- Each log string can be a maximum of 512 bytes
- Log files can be a maximum of 50mb in size
- Log files can be a maximum of 60 days old.
