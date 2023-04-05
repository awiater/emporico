<?php if (empty($noscript)) :?>
<script>
<?php endif ?>
var time = new Date().getTime();
	
	$(document.body).bind("mousemove keypress", function(e) {
         time = new Date().getTime();
     });
	
	$(function () {
  		$('[data-toggle="tooltip"]').tooltip();
  		$('.alert').alert();
  		setTimeout(refresh, 10000);
	});
	
	function refresh() {
         if(new Date().getTime() - time >= 600000) 
             window.location='<?= url_from_route('logout') ?>';
         else 
             setTimeout(refresh, 10000);
     }
<?php if (empty($noscript)) :?>
</script>
<?php endif ?>

