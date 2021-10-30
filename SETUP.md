# Setup
Built with Ye Olde CodeIgniter 3.1 (required PHP version >= 5.6).  
Copy all files to the host and configure the [`base_url`](application/config/config.php#L26) and the [DB settings](application/config/database.php#L76).  

If the site is running under a path the [.htaccess](.htaccess#L10) file `RewriteBase /` line needs to be updated to reflect that path.  

Configure the [`autoload[config]`](application/config/autoload.php#L106) value to match an existing config file (_fnf, ofcra, 3cb, 242ns, localhost_), or create a new one.  


## Database
The table structure is in the [db.skeleton.sql](.sql/db.skeleton.sql) file. (MySQL/MariaDB format)  

Import the skeleton first, and then the seed if you want to.


## Admin access
The admin interface can be accessed via a URL with a fixed token tied to a user name.  

Use the _`base_url`/login/**username**/[admin_key](application/config/localhost.php#L4)_ URL to reveal the actual login URL for **username**.  

_The admin_key is left at the default value for the demo sites for now. You're welcome to mess around, but please don\'t screw up anything intentionally._ 🥺
