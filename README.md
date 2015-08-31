# logger
A simple logging utility with rotation, concurrency, repeat counting and search support.

This library provides the ability for your application to log debug messages, info messages, warnings and errors to a text file.  The library supports rotation, counting sequentially repeated messages, writing to the same file from multiple processes,  and querying the files.  It is suitable for applications that need various amounts of debugging, logging or auditing capabilities.

There are three separate ways to log to a text file:
- The calguy1000\logger\Logger class
- The calguy1000\logger\SimpleLogger class - A singleton class
- Individual debugging functions.

