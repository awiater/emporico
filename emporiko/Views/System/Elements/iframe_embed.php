<?php if (!empty($responsive) && $responsive) :?>
<div class="embed-responsive<?= !empty($ratio) ? ' embed-responsive-'.$ratio:''?>">
  <iframe class="embed-responsive-item" src="<?= !empty($url) ? $url:''?>" allowfullscreen></iframe>
</div>
<?php else :?>
<iframe style="width:<?= !empty($width) ? $width:'100%'?>;height:<?= !empty($height) ? $height:'100vh'?>;" src="<?= !empty($url) ? $url:''?>" frameborder="<?= !empty($frameborder) && $frameborder ? 1:'0'?>" allowfullscreen></iframe>
<?php endif; ?>
