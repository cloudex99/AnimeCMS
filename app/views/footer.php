    </div>
</div>
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>

<?php if(PAGE==='episode' || PAGE==='cartoon_episode'): ?>
<script type='text/javascript' src="/js/episode.js"></script>
<?php endif;?>

<footer class="footer">
    <div class="container bg-light text-right">
        <span class="text-muted">©2018 AnimeCMS All Rights Reserved. | Powered by <a href="http://animeapi.com/docs.html">AnimeAPI</a></span>
        <span><?php echo '<b>Total Execution Time:</b> '.round(( microtime(true) - TIME_START), 5). ' seconds';?></span>
    </div>
</footer>
</body>
</html>