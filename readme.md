# OPEN RECORDS GENERATOR
Version 3.5.0
O-R-G inc.  
Last updated 20 December 2023

## INSTRUCTIONS

Your new website runs off of a database. You can easily modify this database by using OPEN-RECORDS-GENERATOR, a light-weight web interface to a robust content management system. You can access this interface by entering the following URL:

http://domain.tld/open-records-generator/

To login, pick one of the following accounts (User Name) and the corresponding credential (Password). Each account has different permissions over the content of the website. You should use "main" unless you have a strong reason not to.

	admin : add, edit, and delete entries plus edit HTML
	main  : add, edit, and delete entries
	guest : view entries only, cannot modify

Once you've connected to the database, click "ENTER DATABASE..." to begin. You will now see a brief menu that corresponds to the structure of your website. Clicking on any of these choices will navigate you through the hierarchical menu tree that organizes your site. As you navigate, you will always see the previous level that you have just selected above you in sequential "bands" of alternating background colors. In fact, you will notice throughout that the interface of the OPEN-RECORDS-GENERATOR is organized around this interface metaphor.

To the right of the currently selected RECORD (in the currently selected RECORD band) you will see two options, listed as EDIT and DELETE. These apply to the currently selected RECORD. They are described here:

### EDIT . . .

All objects can be selected, and then modified by clicking "EDIT..." Each time you select an object for editing you will be provided 5 empty slots for uploading additional images. Once the images are uploaded you can add captions or delete them. All fields in this RECORD are editable including the URL, DATE and RANK fields. Certain fields contain rich text editing features, which allow for text to be bolded, italicized, indented, or linked. Images attached to the record can also be uploaded and inserted directly into any of the fields. Once an image inserted into a field, you can choose to delete the image. It will continue to remain accessible as a file on the server. 

### DELETE . . .

All objects can be deleted. Before deleting an object the OPEN-RECORDS-GENERATOR will ask you to confirm your choice. If the object you wish to delete contains child objects these will be deleted as well. In this case you will be provided with a specific warning before continuing.

### ADD OBJECT . . .

The OPEN-RECORDS-GENERATOR allows you to create and modify RECORDS within your database. A RECORD can contain text and images. Click "ADD OBJECT..." to create a new RECORD. You will see text fields and image upload options. All fields are optional, but if you do not give your object a name it will automatically be named "Untitled." In the date field you can enter a date in nearly any format and it will be converted to standard server time. For example, "02 Jan 06 6pm" and "January 2, 2006 18:00" will both become "2006-01-02 18:00:00." You can also use date commands like "today + 1 week." A typical convention of open-records-generator websites allows a user to choose to hide the object from being displayed while it is in a draft state by prepending a "." to its name. To publish, remove the ".".

### LINK. . .

Similar to ADD OBJECT . . . , however LINK . . . lets you lets you insert an existing record in more than one place in the database. So for example, you might like the same project or text to appear in more than one place in the menu structure. To link an entry, navigate to the location in OPEN-RECORDS-GENERATOR where you would like the entry to appear. When you click on LINK . . . you will be presented with a pull-down menu of all of the available RECORDS in the database. Clicking one RECORD attaches it redundantly to this position in the menu. If there are any other RECORDS attached, these are also mirrored. 

### COPY. . .

Copies an entry to a new location. Only available for admin use. This is different than linking as it creates a duplicate ENTRY rather than just a link to the SAME entry (which is what LINK...) does.

### INFO

This is the license agreement that accompanies the purchase of this software.

### GENERATE

This button generates a live view of the website whose contents you are managing with OPEN-RECORDS-GENERATOR.

### SETTINGS

There are three setting items. "maximum # of uploads" controls how many images can be added at a time per object. Once that many images are added, you must update the object and go back to reinsert additional images. This is to prevent losing too much work at any time. "default editor mode" determines the default mode of fields like Synposis, Detail, and Notes. It can be either Rich text or HTML. You can also toggle the editor's mode when editing a record. "order type" specifies how the records are ordered. By default, the records are ordered by Name, Rank, Begin, and modified date. Chronological order prioritizes the Begin factor; Alphabetical order prioritizes the Name factor. 

### LOG OUT

Logs the user out of the current session. Useful for changing users or terminating a session.

--

### NOTES

1. As you use this interface, it should become increasingly transparent to you. As you work in OPEN-RECORDS-GENERATOR, use the GENERATE > button consistently to check your work and to see live changes you have just made to your website.
2. OPEN-RECORDS-GENERATOR automatically sorts object lists based on each object's fields. Objects are sorted by their Rank (ascending) field. So, to make a RECORD appear first in the Menu, give it a RANK of 1. Alternately, you could rank your RECORDS 100, 200, 300 and they would still appear in ascending order. Doing it this way makes it easier to add new RECORDS in between as needed without re-ranking the list.
3. OPEN-RECORDS-GENERATOR supports rich text editing within its Synopsis, Detail, and Notes fields. You are easily able to make text bold, add links, and embed images that are uploaded to that record. You can also use toggle the field mode to allow for HTML markup. This will allow you to use the full extent of the HTML markup language, including <embed> tags, such as from youtube.com, vimeo, issuu or other sites. 
4. RECORDS can be hidden from the website by prepending a "." to the Name field. This will make it still accessible to those with a direct URL and editable, but will not be shown in other parts of the website.
5. It is recommended that you limit the file size of images below 1MB to avoid a long loading time. 

## VERSION HISTORY
+ 2.0 -- July 2005: complete overhaul including interface
+ 2.2 -- January 2006: fixes and incremental improvements
+ 2.3 -- February 2007: image rank dropdown menu
+ 2.4 -- April 2011: incremental and register globals htaccess fix
+ 2.5 -- June 2011: incremental
+ 2.6 -- October 2011: sort by rank, image resizing (300 >> 72 dpi) using simpleImage extension, added LINK... function
+ 2.7 -- June 2013:  fix register\_globals query string issue for php 5+ for servers with register\_globals set to off
+ 2.8 -- July 2013: add .htaccess and .htpasswd authentication, requires using .htpasswd to set user pass combination (use htpasswd -c /home/pwww/.htpasswd main to generate .htpwd file)
+ 2.9 -- March 2014: configure for running on localhost including date\_default\_timezone\_set('America/New\_York') config.php and systemDatabase.php. removed php auth completely, and works with .htaccess and .hpasswd only
  - 2.9.5 -- August 2014: add date begin field, add notes field
  - 2.9.6 -- April 2015: change link page to designate O-R-G hierarchy
  - 2.9.7 -- November 2015: add copy function, settings page
+ 3.0.0 -- January 2016: add clean URL schema
+ 3.1.0 -- October 2018: add full wyswig editor
+ 3.1.1 -- July 2019: update readme.md
+ 3.3.0 -- June 2020: add wysiwyg functionality to master and cleanup repository including updated readme.md and repo transferred to O-R-G github team.
+ 3.3.1 -- August 19 2021: fix php 8 warnings, add html wrap and additional validation, update url validation, optimize any get_all() and unlinked_list()
+ 3.5.0 -- December 10 2023: integrate recursive search when processing large directory trees and add divToBr() function to retaiun simple html markup in sql databases


## SITES
+ [o-r-g.com](http://www.o-r-g.com/)
+ [dextersinister.org](http://www.dextersinister.org/)
+ [shop.dextersinister.org](http://shop.dextersinister.org/)
+ [sinisterdexter.org](http://www.sinisterdexter.org/)
+ [wallspacegallery.com](http://www.wallspacegallery.com/)
+ [transmissiondifficulties.vancouverartinthesixties.com](http://transmissiondifficulties.vancouverartinthesixties.com/)
+ [objectif-exhibitions.org](http://www.objectif-exhibitions.org/)
+ [ninarappaport.com](http://www.ninarappaport.com/)
+ [theshowroom.org](http://www.theshowroom.org/)
+ [visualartsworkshop.org](http://www.visualartsworkshop.org/)
+ [processingprocessing.org](http://processingprocessing.org/)
+ [portabledocumentformats.org](http://portabledocumentformats.org/)
+ [dot-dot-dot.us](http://www.dot-dot-dot.us/)
+ [cavs.mit.edu](http://cavs.mit.edu/)
+ [welcometolab.org](http://www.welcometolab.org/)
+ [antrimcaskey.com](http://www.antrimcaskey.com/)
+ [foldingpatterns.com](http://www.foldingpatterns.com/)
+ [sarahoppenheimer.com](http://www.sarahoppenheimer.com/)
+ [maxprotetch.com](http://www.maxprotetch.com/)
+ [spatialinformationdesignlab.org](http://www.spatialinformationdesignlab.org/)
+ [wordswithoutpictures.org](http://www.wordswithoutpictures.org/)
+ [pictureswithoutwords.org](http://www.pictureswithoutwords.org/)
+ [projectprojects.com](http://www.projectprojects.com/)
+ [whitecolumns.org](http://www.whitecolumns.org)
+ [damelioterras.com](http://www.damelioterras.com)
+ [clipstampfold.com](http://www.clipstampfold.com)
+ [masdesigned.com](http://www.masdesigned.com)
+ [nationaldesignawards.org](http://www.nationaldesignawards.org)
+ [elkelehmann.com](http://www.elkelehmann.com)
+ [patternfoundry.com](http://www.patternfoundry.com)
+ [solomonplanning.com](http://www.solomonplanning.com)
+ [jonathandsolomon.com](http://www.jonathandsolomon.com)
+ [306090.org](http://www.306090.org)
+ [solomonworkshop.com](http://www.solomonworkshop.com)
+ [cornelljournalofarchitecture.org](http://www.cornelljournalofarchitecture.org)
+ [c-o-o-l.org](http://www.c-o-o-l.org)
+ [mgmtdesign.com](http://www.mgmtdesign.com)
+ [omiami.org](http://www.omiami.org)
+ [dsalcoda.org](http://www.dsalcoda.org)
+ [t-y-p-o-g-r-a-p-h-y.org](http://www.t-y-p-o-g-r-a-p-h-y.org)
+ [servinglibrary.org](http://www.servinglibrary.org)
+ [c-i-r-c-u-l-a-t-i-o-n.org](http://www.c-i-r-c-u-l-a-t-i-o-n.org)
+ [theartistsinstitute.org](http://www.theartistsinstitute.org)
+ [modernart.net](http://www.modernart.net)
+ [clusternetwork.eu](http://www.clusternetwork.eu)
+ [zenazezza.org](http://www.zenazezza.org)
+ [s-i-m-p-l-i-c-i-t-y.org](http://www.s-i-m-p-l-i-c-i-t-y.org)
+ [zenazezza.org](http://www.zenazezza.org)
+ [templecontemporary.info](http://www.templecontemporary.info)
+ [wattis.org](http://www.wattis.org)
+ [g-e-s-t-a-l-t.org](http://www.g-e-s-t-a-l-t.org/)
+ [radioathenes.org](http://www.radioathenes.org/)
+ [kunstverein-muenchen.de](http://www.kunstverein-muenchen.de)
+ [amiesiegel.net](http://amiesiegel.net/)
+ [framenoir.com](http://framenoir.com/)
+ [tschumi.com](http://www.tschumi.com/)
+ [k-u-r-a.it](https://k-u-r-a.it/)
+ [theartreport.org](https://theartreport.org/)
+ [radioathenes.tv](http://staging.radioathenes.tv/)
+ [materiaabierta.com](https://www.materiaabierta.com)
+ [ica.art](https://www.ica.art/)
+ [n-y-c.org](https://www.n-y-c.org)
+ [w-w-w.o-r-g.net](https://w-w-w.o-r-g.net)
+ [www.r-e-s-e-a-r-c-h.org](http://www.r-e-s-e-a-r-c-h.org)
+ [www.m-u-l-t-i-p-l-i-c-i-t-y.org](http://www.m-u-l-t-i-p-l-i-c-i-t-y.org)
+ [www.songwork.org](http://www.songwork.org)
+ [idojisu.world](https://idojisu.world/)
+ [www.publicartsuwon.com/2021](https://www.publicartsuwon.com/2021)
+ [www.justinbeal.com](https://justinbeal.com/)
+ [www.teigerfoundation.org](https://www.teigerfoundation.org)
+ [www.giornopoetrysystems.org/](https://www.giornopoetrysystems.org)

## DEV NOTES
for basic password protection, create the an `.htpasswd` file with the following command:

`htpasswd -c /PATH/TO/HTPASSWD`

and then create an `.htaccess` file in the OPEN-RECORDS-GENERATOR directory:

`AuthUserFile /PATH/TO/HTPASSWD`  
`AuthName "OPEN-RECORDS-GENERATOR"`  
`AuthType Basic`  
`Require valid-user`  

requires mysql database configuration using the following template: `db/3.3.sql` and corresponding credentials added in `config/config.php`.

requires license for commercial use in `static/license.txt`.

set permissions 777 for config/settings.store to allow preferences storage

copy config-sample.php to config.php, modify as required
