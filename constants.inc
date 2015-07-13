<?php
/**

TODO: make this sense as much of this stuff as possible from Drupal
TODO: make the rest configurable via an admin interface within Drupal

*/

define("USING_TAXONOMY", 1);                                                                          #are you using taxonomy module?  TODO: auto detect this setting

define("FEDORA_UID", 'fedoraAdmin');                                                                  #uid used to access FEdora
define("FEDORA_PWD", 'fedoraAdmin');                                                                  #pwd used to access FEdora
define("FEDORA_SERVER_API_M", "http://fedora.org:8080/fedora/wsdl?api=API-M");               #URL to SOAP API-M
define("FEDORA_SERVER_API_A", "http://fedora.org:8080/fedora/wsdl?api=API-A");               #URL to SOAP API-A

define("DATABASE_NAME", "drupal");                                                                  #name of database on server
define("DATABASE_USER", "uid");                                                                      #UID for accessing DB
define("DATABASE_PWD", "password");                                                                   #PWD for accessing DB
define("DATABASE_SERVER", "localhost");                                                               #name of DB server.  usually localhost

define("FILE_PATH", "/var/www/drupal/sites/default/files/");                                        #this is where you hae DRupal putting files.  TODO: autodetect this from Drupal
define("FILE_HTTP_PATH", "http://drupalsite.org/testcite/sites/default/files/");              #HTTP path to where files are (you ARE putting files somewhere accessibly by HTTP, right?!)

define("BIBLIO_PID_FIELD", "biblio_custom1");                                                         #which column within the Biblio table do you store Fedora PID?
define("FEDORA_NAMESPACE", "demo:");                                                                   #what do you want the digital object PID namespace to be?  Leave the colon out, this will be added automatically.  Also, the number following the colon will be the same as the NID within the Biblio table.

?>
