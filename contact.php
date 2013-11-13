<?php include('header.php'); ?>

<div class="contact">

	<div class="main-content">
		
		<h2>Contact Ryerson Photography</h2>

            <p class="contact-info">Phone: 404.654.3674 | Email: <a href="mailto:ben@ryersonphotography.com">ben@ryersonphotography.com</a></p>

			<ul class="social clearfix">
                <li><a class="facebook ir" href="http://www.facebook.com/RyersonPhotography">Facebook</a></li>
                <li><a class="flickr ir" href="http://www.flickr.com/photos/ryersonphotography">Flickr</a></li>
                <li><a class="twitter ir" href="http://twitter.com/ryersonphoto">Twitter</a></li>
            </ul>

            <a class="inline button" href="#contact-form" rel="facebox">Fill out the Contact Form</a>
            
            <div style="display:none;">
            	<div id="contact-form">
            		<iframe height="517" allowTransparency="true" frameborder="0" scrolling="no" style="width:425px;border:none"  src="https://benryerson.wufoo.com/embed/s7x3k7/"><a href="https://benryerson.wufoo.com/forms/s7x3k7/" title="Send me a message!">Fill out my Wufoo form!</a></iframe>
            	</div>
            </div>
	
	</div>

	<?php include('nav.php'); ?>
</div>

	<script src="js/mylibs/jquery.colorbox-min.js"></script>
	<script>
		jQuery(document).ready(function () {
			$(".inline").colorbox({inline:true, width:"500px"});
		});
	</script>

<?php include('footer.php'); ?>