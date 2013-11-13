<?php include('header.php'); ?>

<div class="portfolio">

	<div class="main-content">
		<h2>Portfolio</h2>		
		<div class="item">
                <a href="#"><img src="img/portraits.jpg" alt="Portraits" title="Portraits"/></a>
                <div class="caption">
                    <a href="portraits.php">Portraits</a>
                </div>
            </div>       
            <div class="item">
                <a href="#"><img src="img/petportraits.jpg" alt="Pet Portraits" title="Pet Portraits"/></a>
                <div class="caption">
                    <a href="pets.php">Pets</a>
                </div>
            </div>
            <div class="item">
                <a href="#"><img src="img/fine_art.jpg" alt="Fine Art" title="Fine Art"/></a>
                <div class="caption">
                    <a href="fine_art.php">Fine Art</a>
                </div>
            </div>
	</div>

	<?php include('nav.php'); ?>
</div>

	<script type="text/javascript">
		$(document).ready(function() {
			var move = -15;
			var zoom = 1.2;
			$('.item').hover(function() {
				width = $('.item').width() * zoom;
				height = $('.item').height() * zoom;
				$(this).find('img').stop(false,true).animate({'width':width, 'height':height, 'top':move, 'left':move}, {duration:200});
				$(this).find('div.caption').stop(false,true).fadeIn(200);
			},
			function() {
				$(this).find('img').stop(false,true).animate({'width':$('.item').width(), 'height':$('.item').height(), 'top':'0', 'left':'0'}, {duration:100});
				$(this).find('div.caption').stop(false,true).fadeOut(200);
			});
		});
	</script>

<?php include('footer.php'); ?>