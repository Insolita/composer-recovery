## Composer-recovery
Helper for recovery composer dependency list (if you lost composer.json) from composer.lock or vendor/composer/installed
.json

**NOTE**: It is not file-recovery tool, and it can`t recover composer.json data same as it was in original. It just
 extracts package list and concrete versions (and also hash for dev-master dependecies) from composer.lock, or, if it
  absent too, from vendor/composer/installed.json 
  
  If recover maked from installed.json - there are no way to separate default and dev dependencies
  
  See [tests/stub/app1_expected.json](tests/stub/app1_expected.json) as an example of result file
  
 **NOTE2**: If your project under vcs control, you don't need this package. You can easy restore composer.json from
  previous commit or another branch

####Installation:
 
`composer global require insolita/composer-recovery`

Ensure that your ~/.composer/vendor/bin directory declared in $PATH

`echo $PATH`

if not - you should add it in ~/.bashrc or ~/.profile

####Basic Usage:

`cd /var/www/myproject && composer-recovery`

#####Supported options:

   -p : path to project directory (by default - active directory where script was called)
   
   -o : path to directory where recovered_composer.json will be written (by default - same as project directory)
   
   -f : custom file name - (by default - recovered_dependecies.json)
   
#####Examples with options:

`composer-recovery -p /var/www/myproject/ -o /some/place/for/result/ -f mycomposer.json`

`composer-recovery -p . -o ../output/`   