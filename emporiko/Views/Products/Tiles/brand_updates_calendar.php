<div style="max-height:420px;overflow-y: scroll;">
            <table class="table" id="brands_updates_tile_print">
                <thead class="bg-warning">
                    <th class="text-center"><?= formatDate('now','-1 month','M Y') ?></th>
                    <th class="text-center"><?= formatDate('now',FALSE,'M Y') ?></th>
                    <th class="text-center"><?= formatDate('now','+1 month','M Y') ?></th>
                    <th class="text-center"><?= formatDate('now','+2 month','M Y') ?></th>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?php foreach($brands_prev as $brand) :?>
                            <div class="card text-center">
                                <div class="w-100">
                                    <img src="<?=parsePath($brand['prb_logo'])?>" width="96" height="73" alt="<?= $brand['prb_name'] ?>">
                                </div>
                                <?= $brand['prb_name'] ?>
                            </div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php foreach($brands_curr as $brand) :?>
                            <div class="card text-center">
                                <div class="w-100">
                                    <img src="<?=parsePath($brand['prb_logo'])?>" width="96" height="73" alt="<?= $brand['prb_name'] ?>">
                                </div>
                                <?= $brand['prb_name'] ?>
                            </div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php foreach($brands_next as $brand) :?>
                            <div class="card text-center">
                                <div class="w-100">
                                    <img src="<?=parsePath($brand['prb_logo'])?>" width="96" height="73" alt="<?= $brand['prb_name'] ?>">
                                </div>
                                <?= $brand['prb_name'] ?>
                            </div>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php foreach($brands_after as $brand) :?>
                            <div class="card text-center">
                                <div class="w-100">
                                    <img src="<?=parsePath($brand['prb_logo'])?>" width="96" height="73" alt="<?= $brand['prb_name'] ?>">
                                </div>
                                <?= $brand['prb_name'] ?>
                            </div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>