<div class="card" id="chart<?= $name ?>Tile" style="max-height:500px;">
    <div class="card-header d-flex"<?= !empty($header_style) ? ' style="'.$header_style.'"' : '' ?>>
        <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            <?= lang($header) ?>
        </h3>
        <span class="ml-auto">
            <?php if (!empty($tilePrintButton) && $tilePrintButton) :?>
            <button type="button" class="btn btn-sm btn-secondary float-right" onclick="printTile('#chart<?= $name ?>','<?= lang($header_chart) ?>')" data-toggle="tooltip" data-placement="top" title="<?= lang('system.buttons.print') ?>">
                <i class="fa fa-fas fa-print"></i>
            </button>
            <?php endif ?>
        </span>
    </div>
    <div class="card-body">
        <div class="tab-content p-0">
            <canvas id="chart<?= $name ?>" width="400" height="300"></canvas>
        </div>
    </div>
</div>
<script>
   
const chart<?= $name ?>Field = document.getElementById('chart<?= $name ?>').getContext('2d');
const chart<?= $name ?> = new Chart(chart<?= $name ?>Field, {
    type: '<?= empty($chart_type) ? 'bar' : $chart_type?>',
    data: {
        labels: ['<?=implode("','", array_keys($data))?>'],
        datasets: [{
            label: '<?= lang($header_chart) ?>',
            data: ['<?=implode("','", $data)?>'],
            backgroundColor: ['<?=implode("','", array_fill(0, count($data), 'rgba(52,58,64,0.3)'))?>'],
            borderColor: ['<?=implode("','", array_fill(0, count($data), 'rgba(52,58,64,1)'))?>'],
            borderWidth: 1,
            pointRadius:0
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

</script>