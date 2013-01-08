Featured Carousel
=================

Author: Devlin Junker  
Version: 0.2  
Last Updated: 1/6/2013  
  
  
  
The Featured Carousel Plugin is a Fully customizable Carousel for the home page of a Wordpress website. To include the carousel on your home page, call the php function `featured_carousel_display()` on your home page where you want the carousel to appear. You can then customize the settings from the options page to size the carousel appropriately for your layout.  
  
  
  
Options
-------
Once you activate the plugin, a tab will appear on the admin menu near the middle. The tab will take you to the options page where you can modify the carousel settings.
  
  
### Slide Settings
------------------
At the top of the Slide Settings, the optimal image size is shown. This is calculated based on the container height and width settings as well as the content height and width, and overlap setting to determine how big the image should be to fit perfectly inside of the image area.  

#### Slide Count
The number of slides to be displayed. The options page will be updated to reflect the number of pages after the page is submitted.

#### URL
The URL that the viewer is taken to when clicking on the slide or content.

#### Title
Title displayed in the Content Box for the Slide.

#### Display Title?
If checked, the title will be visible in the Content Box. If not, the title will be hidden.

#### Text
Text displayed in the Content Box for the Slide.

#### Image
The URL of the image to be displayed on the Slide.

#### Cropped?
If checked, the image will be cropped and stretched to take up the entire image area. If not checked, the entire image will be displayed with the Image Background Color visible if the image dimension ratio is not the optimal size.  
  
  
### Container Settings
----------------------

#### Container ID
The HTML Element ID for the DIV that will hold the container.

#### Container Width
The width of the entire carousel in pixels.

#### Container Height
The height of the entire carousel in pixels.
  

### Content Settings
--------------------

#### Content Location
The Location of the Content Box. Options: Left, Right.

#### Content Width
Width of the content box. Percent relative to the entire carousel.

#### Content Height
Height of the content box. Percent relative to the entire carousel.

#### Content Padding
Padding along the inside edges of the Content Box in Pixels.

#### Content Background Color
Hexidecimal representation of the background color for the content box.

#### Content Text Color
Hexidecimal representation of the content text color.

#### Title Size
Size of the Content Title HTML element. Options: Header 1, Header 2, Header 3, Header 4, Header 5, Header 6, Bold, Normal

#### Title Location
Location of the Title in the Content Box. Options: Separate Line, Same Line.

#### Content Overlap
If checked, the Content Box will overlap the Image. If not checked, the Content Box will sit next to the image.

#### Content Opacity
Determines the Background Color Opacity of the Content Box if it overlaps the Image.
  

### Image Settings
------------------

#### Image Location
Aligns the image inside of the image area. If the image is not optimal size, the image will appear in this part of the area. Options: Left, Middle, Right.

#### Image Background Color
Hexidecimal representation of the color that will appear behind the image if the image does not take up the entire image area.
  
  
### Controller Settings
-----------------------

#### Controller Visible?
If checked, the controller will appear on the carousel. If not checked, the controller will be hidden.

#### Controller Location
Determines the controller location in the carousel. Options: Content Top Left, Content Top Middle, Content Top Right, Content Bottom Left, Content Bottom Middle, Content Bottom Right, Image Top Left, Image Top Middle, Image Top Right, Image Bottom Left, Image Bottom Middle, Image Bottom Right.

#### Controller Background Opacity
Determines the Controller Background Opacity.

#### Controller Background Color
Hexidecimal representation of the controller background color.

#### Controller Active Selector Color
Hexidecimal representation of the background color of the active slide selector on the controller. 

#### Controller Active Text Color
Hexidecimal representation of the text color of the active slide selector on the controller.

#### Controller Inactive Selector Color
Hexidecimal representation of the background color of the inactive slide selectors on the controller. 

#### Controller Inactive Text Color
Hexidecimal representation of the text color of the inactive slide selectors on the controller. 
  
  
### Transition Settings
-----------------------

#### Effect
Determines the transition effect for the slides in the carousel. Options: None, Fade, Cover, Toss, Uncover, Wipe, Blind X, Blind Y, Scroll Up, Scroll Down, Scroll Left, Scroll Right, Slide X, Slide Y.

#### Delay
Length of time, in seconds, that each slide is visible.

#### Duration
Length of time, in seconds, that the transition occurs over.
