<?php if (empty($divMode)) :?>
`<table style="box-sizing: border-box; height: <?= empty($height) ? '40px' : $height?>; margin: 0 auto 10px auto; padding:2px; width: 100%;">
    <tr>
        <td style="box-sizing: border-box;width:50%;vertical-align: top; margin: 0; padding: 0;"></td>
        <td style="box-sizing: border-box;width:50%;vertical-align: top; margin: 0; padding: 0;"></td>
    </tr>
</table>`
<?php else :?>
`<div class="container m-0 p-0 col-12">
    <div class="row h-100">
        <div class="col-xs-12 col-md-6"></div>
        <div class="col-xs-12 col-md-6"></div>
    </div>
</div>
<?php endif ?>`