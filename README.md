<h3>Simpliest console application example.</h3>

*Required PHP version >= 5.3*

Simple console app skeleton to create console commands in PHP.
Easy way to add allowed options for command on configure.

It can takes console args like:
```
$ php index.php demo -c path-to-config -p password
$ php index.php demo --config="path-to-config with whitespaces"
```

And it can set parametric options (like -c, -p, --config  in example strings) or trigger options (without params).
You can view to App/Command/Demo.php file to take a look example of creating command with options.

<h4>How to create new command</h4>

New command creates in three steps (look for App/Command/FilesList.php file example):

**1. Create a new class in _Command/_ folder and extends it from _Command_ class.**
```php
namespace Command;

class FilesList extends Command
{
}
```

**2. Add protected _configure()_ method to class, and add little piece of code like that:**
```php
protected configure() {
    $this->setName('list') // this is the name for command. Used on command calls.
        ->setDescription('Show list of files in directory')
        ->addParametricOption('f', 'folder', 'set path to folder. For example: $ php index.php list -f path/to/folder');
}
```

This code means: 
- I want create 'list' command
- Help description of command is 'Show list of files in directory'
- it has parametric (with specific value) option '-f', with alias '--folder' that can take some value.

**3. Add public _execute()_ method to class, and write your work code here**
```php
public function execute()
{
    // if no option 'f' given
    // option checks only by original name, even in command line it used as alias '--folder'
    $path = $this->getOptionValue('f');
    if ( is_null($path) || !is_null($this->getOptionValue('h'))) {
        $this->output($this->getHelp());
        exit(1);
    }
    
    $dirPath = realpath(ROOT_DIR . DIRECTORY_SEPARATOR . $path);
    // Deny view directories not in ROOT_DIR
    if (mb_strlen($dirPath) < mb_strlen(ROOT_DIR)) {
        throw new \Exception("You are not allowed to view this folder");
    }

    // check for existing and readable
    if (!file_exists($dirPath)) {
        throw new \Exception("Directory {$path} not exists");
    }
    if (!is_readable($dirPath)) {
        throw new \Exception("Directory {$path} not readable. Check for permissions.");
    }
    if (!is_dir($dirPath)) {
        throw new \Exception("{$path} is not directory");
    }
    // get list
    $list = scandir($dirPath);
    $list = array_filter($list, function($item) {
            return !in_array($item, array('.', '..'));
        });
    // display list
    $this->output($list);
}
```
This code check for value of '-f' (or '--folder') option value and get list of files in directory.

**4. And finally add this command to ConsoleApplication in index.php**
```php
// ...
$app = new ConsoleApplication();
$app->addCommand(new \Command\Demo());
$app->addCommand(new \Command\FilesList());
$app->run();

```

**THAT ALL**

Now you can use this command like that:
```
$ php index.php list -f App/Command
$ php index.php list --folder="App"
```
