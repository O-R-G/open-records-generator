OPEN RECORDS GENERATOR
======================
Version 2.9.7  
O-R-G inc.  
Last updated 4 November 2015

INSTRUCTIONS
------------

Your new website runs off of a database. You can easily modify this database by using OPEN-RECORDS-GENERATOR, a light-weight web interface to a robust content management system. You can access this interface by entering the following URL:

http://radioathenes.org/OPEN-RECORDS-GENERATOR/

To login, use Name: "NAME" / Password: "PASSWORD".

Once you've entered this and connected to the database, click "ENTER DATABASE..." to begin. You will now see a brief menu that corresponds to the structure of your website. Clicking on any of these choices will navigate you through the hierarchical menu tree that organizes your site. As you navigate, you will always see the previous level that you have just selected above you in sequential "bands" of alternating background colors. In fact, you will notice throughout that the interface of the OPEN-RECORDS-GENERATOR is organized around this interface metaphor.

To the right of the currently selected RECORD (in the currently selected RECORD band) you will see two options, listed as EDIT and DELETE. These apply to the currently selected RECORD. They are described here:

> EDIT . . .

All objects can be selected, and then modified by clicking "EDIT..." Each time you select an object for editing you will be provided 5 empty slots of uploading additional images. Once the images are uploaded you can add captions or delete them. All fields in this RECORD are editable including the URL, DATE and RANK fields.

> DELETE . . . 

All objects can be deleted. Before deleting an object the OPEN-RECORDS-GENERATOR will ask you to confirm your choice. If the object you wish to delete contains child objects these will be deleted as well. In this case you will be provided with a specific warning before continuing. 

> ADD OBJECT . . .

The OPEN-RECORDS-GENERATOR allows you to create and modify RECORDS within your database. A RECORD can contain text and images. Click "ADD OBJECT..." to create a new RECORD. You will see text fields and image upload options. All fields are optional, but if you do not give your object a name it will automatically be named "Untitled." In the date field you can enter a date in nearly any format and it will be converted to standard server time. For example, "02 Jan 06 6pm" and "January 2, 2006 18:00" will both become "2006-01-02 18:00:00." You can also use date commands like "today + 1 week." 

> LINK . . .

Similar to ADD OBJECT . . . , however LINK . . . lets you redundantly add a record in more than one place in the database. So for example, you might like the same project or text to appear in more than one place in the menu structure. This button allows that simple functionality. When you click on LINK . . . you will be presented with a pull-down menu of all of the available RECORDS in the database. Clicking one RECORD attaches it redundantly to this position in the menu. If there are any other RECORDS attached, these are also duplicated.

> INFO

This is the license agreement that accompanies the purchase of this software.

> GENERATE >

This button generates a live view of the website whose contents you are managing with OPEN-RECORDS-GENERATOR.

--

NOTES

1. As you use this interface, it should become increasingly transparent to you. As you work in OPEN-RECORDS-GENERATOR, use the GENERATE > button consistently to check your work and to see live changes you have just made to your website.

2. OPEN-RECORDS-GENERATOR automatically sorts object lists based on each object's fields. Objects are sorted by their Rank (ascending) field. So, to make a RECORD appear first in the Menu, give it a RANK of 1. Alternately, you could rank your RECORDS 100, 200, 300 and they would still appear in ascending order. Doing it this way makes it easier to add new RECORDS in between as needed without re-ranking the list.

3. OPEN-RECORDS-GENERATOR supports basic HTML commands within its fields. You can make text bold or create hyperlinks which will be rendered on your website. It will even allow full `<embed>` tags, such as from youtube.com, vimeo, issuu or other sites. For your reference, here's a short list of common html markup:

`<b>bold</b>`  
`<i>italic</i>`  
`<u>underline</u>`  
`<a href="http://www.example.com">A hyperlink</a>`  
`<a href="http://www.example.com" target="_blank">Opens in a new window</a>`  

VERSION HISTORY
---------------

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

SITES
-----
+ [www.o-r-g.com](http://www.o-r-g.com/)
+ [www.dextersinister.org](http://www.dextersinister.org/)
+ [shop.dextersinister.org](http://shop.dextersinister.org/)
+ [www.sinisterdexter.org](http://www.sinisterdexter.org/)
+ [www.wallspacegallery.com](http://www.wallspacegallery.com/)
+ [transmissiondifficulties.vancouverartinthesixties.com](http://transmissiondifficulties.vancouverartinthesixties.com/)
+ [www.objectif-exhibitions.org](http://www.objectif-exhibitions.org/)
+ [www.ninarappaport.com](http://www.ninarappaport.com/)
+ [www.theshowroom.org](http://www.theshowroom.org/)
+ [www.visualartsworkshop.org](http://www.visualartsworkshop.org/)
+ [processingprocessing.org](http://processingprocessing.org/)
+ [portabledocumentformats.org](http://portabledocumentformats.org/)
+ [www.dot-dot-dot.us](http://www.dot-dot-dot.us/)
+ [cavs.mit.edu](http://cavs.mit.edu/)
+ [www.welcometolab.org](http://www.welcometolab.org/)
+ [www.antrimcaskey.com](http://www.antrimcaskey.com/)
+ [www.foldingpatterns.com](http://www.foldingpatterns.com/)
+ [www.sarahoppenheimer.com](http://www.sarahoppenheimer.com/)  
+ [www.maxprotetch.com](http://www.maxprotetch.com/)
+ [www.spatialinformationdesignlab.org](http://www.spatialinformationdesignlab.org/)
+ [www.wordswithoutpictures.org](http://www.wordswithoutpictures.org/)
+ [www.pictureswithoutwords.org](http://www.pictureswithoutwords.org/)
+ [www.projectprojects.com](http://www.projectprojects.com/)
+ [www.whitecolumns.org](http://www.whitecolumns.org/)
+ [www.damelioterras.com](http://www.damelioterras.com/)
+ [www.clipstampfold.com](http://www.clipstampfold.com/)
+ [www.masdesigned.com](http://www.masdesigned.com/)
+ [www.nationaldesignawards.org](http://www.nationaldesignawards.org/)
+ [www.elkelehmann.com](http://www.elkelehmann.com/)
+ [www.patternfoundry.com](http://www.patternfoundry.com/)
+ [www.solomonplanning.com](http://www.solomonplanning.com/)
+ [www.jonathandsolomon.com](http://www.jonathandsolomon.com/)
+ [www.306090.org](http://www.306090.org/)
+ [www.solomonworkshop.com](http://www.solomonworkshop.com/)
+ [www.cornelljournalofarchitecture.org](http://www.cornelljournalofarchitecture.org/)
+ [www.c-o-o-l.org](http://www.c-o-o-l.org/)
+ [www.mgmtdesign.com](http://www.mgmtdesign.com/)
+ [www.omiami.org](http://www.omiami.org/)
+ [www.dsalcoda.org](http://www.dsalcoda.org/)
+ [www.t-y-p-o-g-r-a-p-h-y.org](http://www.t-y-p-o-g-r-a-p-h-y.org/)
+ [www.servinglibrary.org](http://www.servinglibrary.org/)
+ [www.c-i-r-c-u-l-a-t-i-o-n.org](http://www.c-i-r-c-u-l-a-t-i-o-n.org/)
+ [www.theartistsinstitute.org](http://www.theartistsinstitute.org/)
+ [www.modernart.net](http://www.modernart.net/)
+ [www.clusternetwork.eu](http://www.clusternetwork.eu/)
+ [www.zenazezza.org](http://www.zenazezza.org/)
+ [www.s-i-m-p-l-i-c-i-t-y.org](http://www.s-i-m-p-l-i-c-i-t-y.org/)
+ [www.clusternetwork.eu](http://www.clusternetwork.eu/)
+ [www.zenazezza.org](http://www.zenazezza.org/)
+ [www.templecontemporary.info](http://www.templecontemporary.info/)
+ [www.zenazezza.org](http://www.zenazezza.org/)
+ [www.wattis.org](http://www.wattis.org/)
+ [www.g-e-s-t-a-l-t.org](http://www.g-e-s-t-a-l-t.org/)
+ [www.radioathenes.org](http://www.radioathenes.org/)