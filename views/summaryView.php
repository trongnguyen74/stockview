<?php
    // Fetch other required data
    $eod = JSON::readJsonFile('./data/eod.json');
    $eod_stat = JSON::readJsonFile('./data/eod_stat.json');
    $trend_stat = $eod_stat->trend ?? [];
    $foreign_stat = $eod_stat->foreign ?? [];

    $a_index = ['^vnindex', '^hastc', '^upcom'];
    $company_market_stat = JSON::readJsonFile('./data/company_market_stat.json');
?>

<div class="flex flex-col p-4">
    <div class="grid grid-cols-3 gap-6 p-2">
        <?php foreach($a_index as $i => $index): 
            $info = $eod->$index;
            $percent = $info->percent_change;
            $stat = $company_market_stat[$i];
            switch (true) {
                case $percent > 0:
                    $class = 'price-up';
                    break;
                case $percent < 0:
                    $class = 'price-down';
                    break;    
                default:
                    $class = 'price-open';
                    break;
            }
        ?>
            <div class="live h-[335px] rounded p-2 border-1 border-[#595959]" data-id="<?=$index?>">
                <div class="h-[120px] space-y-4">
                    <div class="flex items-center space-x-2 h-[100px] p-2">
                        <div class="flex-auto font-bold">
                            <h2 class="text-[#00bbf0]"><?=($index == '^hastc') ? 'HNX' : str_replace('^', '', strtoupper($index))?></h2>    
                            <span data-attr="open" data-value="<?=$info->price_open?>" class="hidden"></span>
                            <span class="text-[20px]">
                                <span data-attr="close" data-value="<?=$info->close?>" class="<?=$class?>"><?=$info->close?>
                                </span>
                            </span>
                            <span data-attr="price_change" data-value="<?=$info->price_change?>" class="<?=$class?>"><?=FORMAT::formatUpChange($info->price_change)?></span> /
                            <span data-attr="percent_change" data-value="<?=$info->percent_change?>" class="<?=$class?>"><?=FORMAT::formatUpChange($info->percent_change)?>%</span>
                        </div>
                        <div id="<?=$index?>-spline" data-id-spline="<?=$index?>" class="w-[40%] h-full chart-spline"></div>
                    </div>    
                    <div class="flex justify-between px-4">
                        <div class="flex space-x-2 price-up">
                            <div><i class="fa-solid fa-up-long"></i></div>
                            <div><?=$stat->up?></div>
                        </div>
                        <div class="flex space-x-2 price-down">
                            <div><i class="fa-solid fa-down-long"></i>                                </div>
                            <div><?=$stat->down?></div>
                        </div>
                        <div class="flex space-x-2 price-open">
                            <div><i class="fa-solid fa-arrows-left-right"></i></div>
                            <div><?=$stat->open?></div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-3 px-4">
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">Giá trị</div>
                            <div><span data-attr="value"><?=number_format($info->value)?></span> tỷ</div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">KL</div>
                            <div><span data-attr="volume"><?=number_format($info->volume)?></span></div>
                        </div>
                        <div class="py-8">
                            <?=FORMAT::displayPriceInBar($info->price_lowest, $info->price_highest, $info->price_open, $info->close)?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script type="module">
    import { drawSplineChart } from './js/highcharts-spline.js';
    import { drawPieChart } from './js/highcharts-pie.js';
    let api_spline = URL_ROOT + '/api/get_chart_spline.php';

    var a_index = <?php echo json_encode($a_index); ?>;

    a_index.forEach(function(index){
        drawSplineChart(api_spline, index);
    });

    let api_pie = URL_ROOT + '/api/get_chart_stat.php';
    drawPieChart(api_pie, 'trend', 'Số lượng CP tăng, giảm, không đổi', 'SL');
    drawPieChart(api_pie, 'foreign', 'NN mua / bán', 'KL');
</script>

<?php require_once (dirname(__DIR__).'/live/faststock_all_brief.php'); ?>