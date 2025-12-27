<?php
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : '1';
    $eod = JSON::readJsonFile('./data/eod.json');
    $markets = JSON::readJsonFile('./data/company_market.json');
    $markets_name = JSON::readJsonFile('./data/company_market_name.json');
    $markets_change = JSON::readJsonFile('./data/company_market_change.json');
    $company_search = JSON::readJsonFile('./data/company_search.json');
    $company_name = array();
    foreach($company_search as $row){
        $company_name[$row->stockname] = $row->company_name;
    }
    $name = $markets_change[$id - 5] -> name;
    $percent_change = $markets_change[$id - 5] -> percent_change;
    $a_stock = $markets_change[$id - 5]->stock_percent;

    $market_stat = JSON::readJsonFile('./data/company_market_stat.json');
    $stat = $market_stat[$id];
?>
<div class="p-4">
    <div>
        <div class="flex justify-between items-center">
            <h2 class="space-x-2">
                <span><?=($name)?></span>
                <span class="<?=($percent_change > 0 ? 'price-up' : 'price-down')?>">
                    <?=FORMAT::formatUpChange($percent_change)?> %
                </span>
            </h2>
            <div class="flex space-x-6">
                <div class="flex text-green-500 space-x-1">
                    <div><i class="fa-solid fa-up-long"></i></div>
                    <div><?=$stat->up?></div>
                </div>
                <div class="flex text-red-500 space-x-1">
                    <div><i class="fa-solid fa-down-long"></i>                                </div>
                    <div><?=$stat->down?></div>
                </div>
                <div class="flex text-yellow-500 space-x-1">
                    <div><i class="fa-solid fa-arrows-left-right"></i></div>
                    <div><?=$stat->open?></div>
                </div>
            </div>
        </div>
        <div class="flex space-x-8">
            <div class="h-[75vh] flex justify-center overflow-y-auto mt-4">
                <table class="table-fixed box-content" width="100%">
                    <thead>
                        <th class="w-[300px]">Mã</th>
                        <th align="right">Giá</th>
                        <th align="right">% thay đổi</th>
                        <th align="right">% ảnh hưởng</th>
                        <th align="right">Khối lượng</th>
                        <th align="center">Biểu đồ</th>
                    </thead>
                    <tbody>
                        <?php foreach($a_stock as $stockname => $percent_effect): ?>
                            <tr class="live" data-id="<?=$stockname?>">
                                <td>
                                    <span class="flex flex-col px-2">
                                        <a class="stockname" href="quote.php?s=<?=$stockname?>"><?=$stockname?></a>
                                        <span class="text-[12px]"><?=$company_name[$stockname]?></span>
                                    </span>
                                </td>
                                <td align="right"><span data-attr="close" data-value="<?=$close?>"><?=$eod->$stockname->close?></span></td>
                                <td align="right">
                                    <span data-attr="percent_change" class="<?=($eod->$stockname->percent_change > 0 ? 'price-up' : 'price-down')?>">
                                        <?=FORMAT::formatUpChange($eod->$stockname->percent_change)?>%
                                    </span>
                                </td>
                                <td align="right"><?=number_format($percent_effect*100,2)?> %</td>
                                <td align="right"><span data-attr="volume"><?=number_format($eod->$stockname->volume)?></span></td>
                                <td align="center"><div class="max-w-[80px] h-[60px] chart-spline" data-id-spline="<?=$stockname?>" id="<?=$stockname?>-spline"></div>                            </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { drawSplineChart } from './js/highcharts-spline.js';
    let api = URL_ROOT + '/api/get_chart_spline.php';
    $('.live').each(function(){
        var stockname = $(this).attr('data-id');
        stockname = stockname.toLowerCase();
        drawSplineChart(api, stockname);
    });
</script>

<?php require_once (dirname(__DIR__).'/live/faststock_all_brief.php'); ?>