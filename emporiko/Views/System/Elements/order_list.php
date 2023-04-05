<ul class="list-group" id="<?= $args['id'] ?>" style="overflow-y: scroll;height:250px;">
    <?php $ind=0;foreach($options as $key=>$value) :?>
    <li class="list-group-item border<?= $args['value']==$ind ? ' bg-primary':''?>" data-sortable="true">
        <input type="hidden" name="<?= $args['name'] ?>[]" value="<?= $key ?>">
        <?= $args['_showorderpos'] ? $ind.'&nbsp;:&nbsp;' : ''?><?= $value ?>
    </li>
    <?php $ind++;endforeach; ?>
</ul>
<script>
  $( function() {
    $( "#<?= $args['id'] ?>" ).sortable({
        items: '[data-sortable="true"]'
    });
  } );
 </script>