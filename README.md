# logger
A simple logging utility with rotation, concurrency, repeat counting and search support.

This library provides the ability for your application to log debug messages, info messages, warnings and errors to a text file.  The library supports rotation, counting sequentially repeated messages, writing to the same file from multiple processes,  and querying the files.  It is suitable for applications that need various amounts of debugging, logging or auditing capabilities.

# Features
- Provides a simple, extensible mechanism for allowing an application to perform logging.
- Provides a logging interface
- FileLogger class Can count identical sequental messages
- Can track separate (or combined) keys.  Known as a "section" and an "item"
- AutoRotateFileLogger supports log rotation based on file size (kilobytes) or age (hours) and maximum number of files.
- Uses exclusive locking to lock filed for writing
- Supports simple querying of saved log files

# Simple logging to a file
```
require '../vendor/autoload.php'
use calguy1000\logger\FileLogger as FileLogger;
$logger = new FileLogger('logfile.txt');
$logger->debug('A debug message');
$logger->info('An information message');
$logger->warn('A warning message');
$logger->error('A fatal error');
```

# Using the auto-rotate logger
```
require '../vendor/autoload.php'
use calguy1000\logger\AutoRotateFileLogger as AutoRotateFileLogger;
$logger = new AutoRotateFileLogger('logfile.txt',24,2000,30);
$logger->debug('A debug message');
$logger->info('An information message');
$logger->warn('A warning message');
$logger->error('A fatal error');
```

# Querying the logs
```
require '../vendor/autoload.php'
use calguy1000\logger\Logger as Logger;
$parms = [ 'filename'=>'logfile.txt','limit'=>50,'msg'=>'*fatal*` ];
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
