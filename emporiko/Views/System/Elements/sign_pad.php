<div class="<?= $class ?>">
    <canvas  width="350" height="80" style="touch-action: none; user-select: none;background-color: <?=$pad_color?>;margin-top: 3px;margin-left: 3px;" id="signPadCanvas_<?= $id ?>"></canvas>
    <input type="hidden" name="<?= $name ?>" value="<?= !empty($value) ? $value: '' ?>" id="signPadField_<?= $id ?>">
    <?php if (!empty($button) && $button!=FALSE) :?>
    <div class="ml-1 p-2">
        <button type="button" onclick="signPad_<?= $id ?>Clear()" class="<?= $button ?>">Clear</button>
    </div>
    <?php endif; ?>
</div>
<script>
    const signPad_<?= $id ?>=new SignaturePad(document.getElementById('signPadCanvas_<?= $id ?>'));
    <?php if (!empty($value)) :?>
        signPad_<?= $id ?>.fromDataURL('<?= $value ?>');
    <?php endif ?>
    <?php if (!empty($changeEvent) && $changeEvent) :?>
     signPad_<?= $id ?>.addEventListener("endStroke", () => {
            $('#signPadField_<?= $id ?>').val(signPad_<?= $id ?>.toDataURL());//image/png svg+xml
        }, { once: true });
    <?php endif ?>
    function signPad_<?= $id ?>Clear()
    {
        signPad_<?= $id ?>.clear();
        $('#signPadField_<?= $id ?>').val('');
    }
</script>