<nav class="navbar navbar-expand-lg p-0 pr-1 pl-1 navbar-<?= $background ?> bg-<?= $background ?> w-100" id="<?= $name ?>">
  <?php if(!empty($text)) :?>
    <p class="navbar-brand m-0 mr-2"><?= lang($text==null ? '' : $text) ?></p>
  <?php endif ?> 
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#<?= $name ?>Content" aria-controls="<?= $name ?>Content" aria-expanded="false" aria-label="Toggle navigation">
    <i class="fas fa-bars"></i>
  </button> 
  <div class="collapse navbar-collapse p-<?= empty($padding) ? '0' : $padding ?> m-0 w-100" id="<?= $name ?>Content">
      <ul class="navbar-nav <?= empty($position) ?'mr':$position  ?>-auto m-0 p-0<?= !empty($ul_class) ? ' '.$ul_class : '' ?>">
      <?php foreach ($buttons as $button) :?>
          <?php if(is_subclass_of($button, '\EMPORIKO\Controllers\Pages\HtmlItems\HtmlItem')) :?>
          <li class="nav-item mr-1">
                <?= $button->render() ?>
          </li>
          <?php endif ?>
      <?php endforeach; ?>
      </ul>
  </div>
</nav>