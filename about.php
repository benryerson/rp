<?php include('header.php'); ?>

<div class="about">

	<div class="main-content">
		<h2>About Ryerson Photography</h2>
		<p>
			My name is Ben Ryerson. I am the owner and sole photographer of
			Ryerson Photography. My wife and I live in Atlanta with our two
			children, our daughter (born in April of 2005) and our son (born in
			June of 2008). My children have been my inspiration and have been the
			subject of much of my work; you’ll find their pictures all over the
			site. My photography currently hangs in many private and corporate
			collections. Please take a look at the pricing and read a little
			about how my photo sessions work. Be sure to <a href="contact.php">contact
				me</a> with any questions!
		</p>

		<p class="pAlignCenter">
			<a class="inline button" href="#testimonials" rel="facebox">Testimonials</a>
			<a class="inline button" href="#pricing" rel="facebox">Pricing &amp; Such</a>
		</p>
	</div>

	<div style="display: none;">
		<div id="testimonials" class="modal">

			<h2>Client Testimonials</h2>

			<p>
				<em>&#8220;The pictures go beyond being just &#8221;candid&#8221;.
					Ben caught absolutely priceless expressions and moments that will
					forever say more, and mean more, than any videotape or photo taken
					by a distracted family member could ever mean to us. We can't thank
					Ben enough for his services.&#8221;</em>
			</p>

			<span>Amy &amp; Mark Frank</span>


			<p>
				<em>&#8220;Ben has a natural ease with people that makes it
					possible for him to capture their true personality. The unique
					angles, framing, and lighting techniques Ben uses produce finished
					photos that are truly works of art.&#8221;</em>
			</p>

			<span>Sarah &amp; Stuart Tinker</span>


			<p>
				<em>&#8220;I was so touched by the photos Ben took of my daughter.
					We have done portrait studio sittings before, but none of them have
					been able to capture the subtle nuances of my daughter's
					expressions the way Ben's photos did. I felt like he saw the
					special little individual she is and brought that out in his
					work.&#8221;</em>
			</p>

			<span>Alethea Olsen</span>

		</div>

	</div>

	<div style="display: none;">
		<div id="pricing" class="modal clearfix">

			<h2>Pricing &amp; Such</h2>

			<p>Ryerson Photography specializes in making candid fine art
				photographs of you and your family. I'm happy to work in different
				settings and am also available for events. I'll even take pictures
				of your pets!</p>
				
			<p>
				<strong>Portraits</strong><br />1 hour session | $100 | 50-60 images
			</p>

			<p>
				<strong>First Year</strong><br /> 5 one-hour sessions | $400 | 50-60
				images/session<br /> <em>First Year Sessions cover your baby's
					birth, 3 months, <br />6 months, 9 months, and 1 year</em>
			</p>

			<p>
				<em>We have additional services and pricing available. Please get in
					touch with any questions!</em>
			</p>

			<a class="button" href="contact.php">Contact us!</a>

		</div>

	</div>

	<?php include('nav.php'); ?>
</div>

	<script src="js/mylibs/jquery.colorbox-min.js"></script>
	<script>
		jQuery(document).ready(function () {
			$(".inline").colorbox({inline:true, width:"400px"});
		});
	</script>
  
<?php include('footer.php'); ?>