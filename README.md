# ci-output-minifier
CodeIgniter css, js and html minifier. This code was created in CodeIgniter 3.0.1 but I assume, it would work for 2.x as well. Please test it yourself before integrating it in your project
# What is ci-output-minifier
It's simply a hook that preprocesses your request before outputing it to browser and combines and minifies all the CSS and JS into one single minified JS and CSS files respectively. This file is stores in cached/js/ and cached/css/ under root. PLEASE NOTE: No modification is done to the CSS/JS code as this minification is carried out by regular expression matching, which means, the path to any imports/URLs are not changed as well. To support that, it also copies any such referenced fonts/images under cached/fonts and cached/images/ directories.
Please ensure that your 'cached' directory has enough permissions for the hook to work.

#How does it work?
Download and unzip the package. Copy 'resource.php' from application/hooks and copy it under the same path (ie, application/hooks) in your project.
In your config/hooks.php, add the following towards the bottom of the file:<br>
<code><pre>
$hook['pre_controller'][] = array(
    'class'     => 'Resource',
    'function'  => 'init',
    'filename'  => 'resource.php',
    'filepath'  => 'hooks'
);
$hook['display_override'][] = array(
    'class'     => 'Resource',
    'function'  => 'renderHTML',
    'filename'  => 'resource.php',
    'filepath'  => 'hooks'
);</pre>
</code>
Now, if you want any css/js to be minified, include it within your template in following manner:<br>
<code><pre>
\<link rel="text/stylesheet" href="<?php echo Resource::css('assets/css/index.css')?>" />
\<link rel="text/stylesheet" href="<?php echo Resource::css('assets/css/guest.css')?>" />
\<script rel="text/javascript" href="<?php echo Resource::js('assets/js/jquery1.10.2.min.js')?>">\</script>
\<script rel="text/javascript" href="<?php echo Resource::js('assets/js/index.js')?>">\</script></pre>
</code>
This would create two files, one under js and other under css in name of index.guest.min.css and jquery1.10.2.index.min.js. These files will be created on only first time load of the page. On further requests, it would simply load that file.
Also note that if any of the included css/js files are modified, the minified js/css will be auto updated.
#Note
You could rather merge all the files inspite of having to specify them via call to 'Resource::css' or 'Resource::js'. Just edit the resource.php and remove the check for 'in_array' at lines 72 and 119 respectively.
Hope that helps!
