<div class="info-box" id="<?= !empty($name) ? $name : '' ?>">
    
    <span class="info-box-icon bg-<?= !empty($background) ? $background : ''?>">
        <a href="<?= !empty($url) ? $url : ''  ?>"><i class="<?= $icon ?>"></i></a>
    </span>
        <div class="info-box-content">
            <span class="info-box-text"><?= !empty($header) ? $header : ''  ?></span>
            <span class="info-box-number">
                <a href="<?= !empty($url) ? $url : ''  ?>">
                    <?= !empty($text) ? $text : ''  ?>
                </a>
            </span>
        </div>
</div>