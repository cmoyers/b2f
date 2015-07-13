**************************************************************************
*                                                                        *
*     ######     #####    #######                                        *
*     ##  ###   ##  ###   #######                                        *
*     ##  ##       ###    ##                                             *
*     #####       ###     ####                                           *
*     ##  ##     ###      ####                                           *
*     ##  ###   ###       ##                                             *
*     ######    #######   ##                   by cminor9@gmail.com      *
*                                                                        *
**************************************************************************                                                                


What is this thing?

      It's a very specialized Drupal -> Fedora commons glue layer.  It takes records
      from Biblio (a drupal module) and sticks them into Fedora Commons.  It just
      works for Biblio, and it is designed to be totally transparent to the end user.
      As in, no buttons to push.  You put something into Biblio, it goes into Fedora.

      You'll want PHP 5, Apache 2, and MySQL 5.  Also, Fedora 3x is the target
      version of Fedora.  Drupal 6 is the target version of Drupal.

      For PHP, you will want PHP SOAP enabled.



Here are the components that comprise this program:

      Constants:
            Ok, so this isn't a class.  Include this when you include the classes.  It
            sets all kinds of things that we'll refer to throughout the code.
            Why do I use constants here?  Quite frankly, its so that I don't have
            to type that ridiculous $ character that PHP requires
            before variables.  That annoys me.  So I made 'em constants.  It just
            reads better to me that way.  What I'd like to see with this page is for the
            constants to be defined more intelligently.  As in, not hard-coded values
            you have to change every time you move to a diff server.

      Biblio:
            This deals with all things Biblio.  It gets a record (or records, if you tell
            it to) from Biblio, and finds all authors, keywords, and attachments
            for the record(s).  Once it does this, it populates some properties with
            recordsets so that the data can be accessed in other parts of the program
            without having to fetch the data again.

            Since we are using two methods of getting data from Biblio (all nodes
            last updated since a certain date versus a single record) the methods in here
            that get data are forked and designed to return the same recordset (just with
            one record or multiple records.)  This Class in particular is designed to
            work with sets of data efficiently as well as work with a single row.  If some
            thing seem overly abstracted, this is why.

      Fedora:
            The most complex of the bunch.  It uses Fedora Common's SOAP API to find, add,
            and update digital objects.  It also add/edits/inactivated datastreams
            Documentation for the APIs is found here (subject to change, since it looks like
            it already has a few times):
                  https://fedora-commons.org/confluence/display/FCR30/API-A
                  and
                  https://fedora-commons.org/confluence/display/FCR30/API-M-LITE
   
            The methods which handle adding/updating datastreams are separated by the type
            of datastream (externally managed, inline XML) because different vars must be
            set for these diff types of datastreams.

            One assumption that is made is that EACH digital object will have a DC datastream
            (which is a very minimal datastream that Fedora appears to automatically create), a
            dublin core datastream (also dc) which contains a lot more data about he object,
            and a bibtex datastream.  On update, if these do not exist for some reason, they will
            be recreated and added.

            For some odd reason, when adding an inline datastream (inline, as in it contains inline XML)
            you cannot specify the XML.  You have to feed it a file.  When updating the same
            datastream, you *can* specify the XML.  Thanks Fedora API for being wonky.  So you
            have to add a dummy.xml file (the installation instructions deal with this) which you
            attach to the datastream, then you can immediately update it with the correct content.

            This was written for Fedora 3x.  If using a different version, it may or may not work.

      Bibtex:
            takes data and whips up a bibtex file.  It creates the bibtex file on the FS and then
            returns the file path to the calling method.  This code was largely adapted from Biblio's
            own bibtex export code.  We are writing to the filesystem.

            If you want to store something other than Bibtex in the Fedora repo, simple.  Just use this
            class as a model to write your own.  Make it return the same stuff, accept the same params
            and you should be able to simply replace references to Bibtex with your new class.  In theory.




To install this:
      1) copy this directory to your drupal install's scripts dir, usually %drupal%/scripts/.

      2) in table.sql, change the use statement to whatever your db schema is named.  The use
            statement is in the first line of code.

      3) within this dir, there is a dummy.xml file.  You need to move this to wherever you
            have drupal set to store files.  The default location is %drupal%/sites/default/files.
            Set the permissions to 777 (sudo chmod 777 %filepath%) (this file is just a placeholder,
                  better to exclude permissions issues in case you run into trouble.  So set it to
                  777 already)

      4) go into constants.inc and set these to reflect your environment.  There are sufficient
            comments in that file to allow you to see what to do.

            4.1) drupal should already handle this, but it bears repeating: MAKE DARN SURE that
                  apache prevents users from requesting .inc files.  Else your connection details,
                  including UIDs and PWDs will be exposed.  Again, this should be taken care of in
                  the apache.conf files, but try to view constants.inc in your browser.  You should
                  not be able to.
   
      5) you should be able to go to index.html now and run this process one of the ways outlined
            on that page.  If you'd like to schedule this process on a recurring basis, there
            instructions on that page that will allow you to do so.

      6) load up that Fedora Repo, baby!




Misc:

      the script should be relatively self healing.  If, for example, it bombs out and messes up a
            bibtex file or doesn't include a datastream, running it again for whichever node failed
            should fix the problem.  Obviously, if you run it based on last update (the incremental
            option on index.html) it won't try to process a node unless you make an updat to it.
            But each run tries to create a bibtex file and tries to add datastreams that aren't there
            but should be (like the dc datastream, and bibtex).

      if you get SOAP errors, try using print_r around whatever you are trying to pass to SOAP
            You might have an unexpected value in one of these arrays that SOAP doesn't like.
            During development I had to do a lot of this, esp since the Fedora SOAP API documentation
            is so lacking when it comes to examples of working code

      I tried to make this as simple as possible.  If you can write PHP and connect to a database you
            should be able to understand this.  The only bit that is kind of painful is the SOAP stuff,
            mainly because the PHP SOAP interface's error reporting is quite vague at times.


TODO

      * make the constants file get that data from Drupal.  This will save headaches
      * improve the error handling, stick error handling in log to see exactly where a
            failure happens
      * integrate admin features into Drupal?  Heck, make this thing a Drupal module?
      * Check out Fedora's REST API (after it is out of "experimental" status, that is)
      * if you run this against a HUGE amount of data (thousands of rows) it will take a 
            few minutes to run.  For me, it was running about 1000/min.  This should
            be a fairly rare situation though.  Make sure your server won't timeout.


      
