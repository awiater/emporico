<div class="info-box" id="<?= !empty($name) ? $name : '' ?>">
    <span class="info-box-icon bg-<?= !empty($background) ? $background : ''?>">
        <i class="<?= $icon ?>"></i>
    </span>
        <div class="info-box-content">
            <span class="info-box-text"><?= !empty($header) ? $header : ''  ?></span>
            <span class="info-box-number"><?= !empty($text) ? $text : '' ?></span>
        </div>
</div>