<?php
    // Input Sanitization
    $stockname = strtolower(trim($_REQUEST['s'] ?? ''));
    if ($stockname === '') {
        exitWithMessage("Mã chứng khoán không hợp lệ. Vui lòng nhập lại.");
    }

    // Load and validate stock list
    $a_stock = JSON::readJsonFile('./data/in_stockname.json');

    if (!in_array($stockname, $a_stock)) {
        exitWithMessage("Có vẻ mã chứng khoán bạn đang tìm không có. Vui lòng nhập lại. Cảm ơn!");
    }

    // Set view mode
    $view = trim($_REQUEST['v'] ?? 'summary');

    // Load EOD data
    $eod = JSON::readJsonFile('./data/eod.json');
    $eod_by_stock = $eod->$stockname ?? null;

    // Load company info
    $company = JSON::readJsonFile('./data/company/'.$stockname.'.json');
    $markets_name = JSON::readJsonFile('./data/company_market_name.json');
    $markets_name = array_flip($markets_name);
    //$market_id = $markets_name[$company->superSector];

    // Load company finance
    $company_finance = JSON::readJsonFile('./data/company_finance.json');
    $finance = $company_finance->$stockname ?? [];
    
    $finance_current_year = $finance[0] ?? null;

    if (isset($finance[0]->eps, $finance[2]->eps) && $finance[2]->eps != 0) {
        $eps_percent = (($finance[0]->eps - $finance[2]->eps) / $finance[2]->eps) * 100;
    } else {
        $eps_percent = null;
    }

    // Load company news
    //$res = JSON::readJsonFile('https://iboard-api.ssi.com.vn/statistics/company/news?symbol='.strtoupper($stockname).'&pageSize=8&page=1&language=vn', true);
    //$news = $res->data ?? [];

    $percent_change = $eod_by_stock->percent_change ?? 0;
    $class = $percent_change > 0 ? 'price-up' : ($percent_change < 0 ? 'price-down' : 'price-open');

    // Utility function for clean error handling
    function exitWithMessage(string $message): void {
        echo '<div class="w-full h-full flex items-center justify-center">
                <span>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>
            </div>';
        exit;
    }
?>

<script type="text/javascript" src="./stockchart/charting_library/charting_library.standalone.js"></script>
<script type="text/javascript" src="./stockchart/datafeeds/udf/dist/bundle.js"></script>
<script type="module" src="./stockchart/src/main.js"></script>
<script>
    var stockname = '<?=$stockname?>';
</script>

<div class="flex flex-col p-4 h-full box-border">
    <h1 class="bold text-[20px]"><?=strtoupper($eod_by_stock->stockname)?> : <?=$company->companyName?></h1>
    <div class="text-[22px]">
        <span id="price_box" class="<?=$class?>">
            <span id="stockname_close"><?=$eod_by_stock->close?></span>&nbsp;
            <span id="stockname_price_change"><?=FORMAT::formatUpChange($eod_by_stock->price_change)?></span>&nbsp;
            <span id="stockname_percent_change">(<?=FORMAT::formatUpChange($eod_by_stock->percent_change)?>%)</span>
        </span>
    </div>
    <div class="w-full flex items-center h-[6%] w-fit border-b-4 border-[#2d72d2] mt-4">
        <a class="menu rounded-t-md selected" data-tab="summary">Tổng quan</a>
        <a class="menu rounded-t-md" data-tab="profile">Hồ sơ</a>
        <a class="menu rounded-t-md" data-tab="finance">Tài chính</a>
        <!--a class="menu rounded-t-md" data-tab="news">Tin tức</a-->
    </div>
    <div id="summary" class="tab space-x-8 flex h-full border-1 border-[#595959]">
        <div id="tv_chart_container" class="w-[900px] h-full"></div>
        <div class="flex-auto h-full px-3 py-3">
                <div class="w-full flex justify-between">
                    <div class="w-[48%]">
                        <div class="text-right p-1 bg-[#008000] text-[#FFF]">Đặt mua</div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_klm1"><?=number_format($eod_by_stock->klm1/100)?></span>
                            <span id="stockname_gm1"><?=$eod_by_stock->gm1?></span>
                        </div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_klm2"><?=number_format($eod_by_stock->klm2/100)?></span>
                            <span id="stockname_gm2"><?=$eod_by_stock->gm2?></span>
                        </div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_klm3"><?=number_format($eod_by_stock->klm3/100)?></span>
                            <span id="stockname_gm3"><?=$eod_by_stock->gm3?></span>
                        </div>
                    </div>
                    <div class="w-[48%]">
                        <div class="p-1 bg-[#FF0000] text-[#FFF]">Đặt bán</div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_gb1"><?=$eod_by_stock->gb1?></span>
                            <span id="stockname_klb1"><?=number_format($eod_by_stock->klb1/100)?></span>
                        </div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_gb2"><?=$eod_by_stock->gb2?></span>
                            <span id="stockname_klb2"><?=number_format($eod_by_stock->klb2/100)?></span>
                        </div>
                        <div class="flex justify-between p-1">
                            <span id="stockname_gb3"><?=$eod_by_stock->gb3?></span>
                            <span id="stockname_klb3"><?=number_format($eod_by_stock->klb3/100)?></span>
                        </div>
                    </div>
                </div>
                <div class="space-y-4 mt-2 pt-2 border-t border-[#595959]">
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">TC - Mở cửa</div>
                        <div>
                            <span class="price-open"><?=$eod_by_stock->open?></span> - 
                            <span class="price-open"><?=$eod_by_stock->price_open?></span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">Trần - sàn</div>
                        <div>
                            <span class="price-ceiling"><?=$eod_by_stock->ceiling?></span> - 
                            <span class="price-floor"><?=$eod_by_stock->floor?></span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">Thấp - cao (1d)</div>
                        <div>
                            <span class="price-down"><?=$eod_by_stock->price_lowest?></span> - 
                            <span class="price-up"><?=$eod_by_stock->price_highest?></span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">Khối lượng</div>
                        <div id="stockname_volume"><?=number_format($eod_by_stock->volume)?></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">NN mua - NN bán</div>
                        <div>
                            <span id="foreigner_buy_volume"><?=number_format($eod_by_stock->foreigner_buy_volume)?></span> - 
                            <span id="foreigner_sell_volume"><?=number_format($eod_by_stock->foreigner_sell_volume)?></span>
                        </div>
                    </div>
                </div>
        </div>
    </div>
    <div id="profile" class="hidden tab flex h-full border-1 border-[#595959]">
        <div class="flex w-full h-full space-x-20 p-4">
            <div class="w-[30%] space-y-3">
                <h2 class="border-b border-[#f2f2f2]">Thông tin cơ bản</h2>
                <div class="flex justify-between">
                    <div>Mã SIC</div>
                    <div><?=$company->symbol?></div>
                </div>
                <div class="flex justify-between">
                    <div>Tên ngành</div>
                    <div class="stockname"><?=$company->superSector?></div>
                </div>
                <div class="flex justify-between">
                    <div>Mã ngành ICB</div>
                    <div><?=$company->subSectorCode?></div>
                </div>
                <div class="flex justify-between">
                    <div>Năm thành lập</div>
                    <div><?=explode(' ', $company->foundingDate)[0]?></div>
                </div>
                <div class="flex justify-between">
                    <div>Vốn điều lệ</div>
                    <div><?=number_format($company->charterCapital/1000000000)?> tỷ</div>
                </div>
            </div>
            <div class="w-[30%] space-y-4">
                <h2 class="border-b border-[#f2f2f2]">Thông tin niêm yết</h2>
                <div class="flex justify-between">
                    <div>Ngày niêm yết</div>
                    <div><?=explode(' ', $company->listingDate)[0]?></div>
                </div>
                <div class="flex justify-between">
                    <div>Nơi niêm yết</div>
                    <div><?=$company->exchange?></div>
                </div>
                <div class="flex justify-between">
                    <div>Giá chào sàn</div>
                    <div><?=$company->firstPrice?></div>
                </div>
                <div class="flex justify-between">
                    <div>KL đang niêm yết</div>
                    <div><?=number_format($company->quantity)?></div>
                </div>
                <div class="flex justify-between">
                    <div>Thị giá vốn</div>
                    <div><?=number_format($company->listedValue/1000000000)?> tỷ</div>
                </div>
            </div>
            <div class="w-[40%] space-y-4">
                <h2 class="border-b border-[#f2f2f2]">Ban lãnh đạo</h2>
                <div class="overflow-x-auto h-[250px] space-y-4">
                    <?php foreach($company->leader as $row): ?>
                        <div>
                            <div><?=$row->fullName?></div>
                            <div><?=$row->positionName?></div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>
    </div>
    <div id="finance" class="hidden tab flex h-full space-x-4 border-1 border-[#595959] items-center">
        <div class="w-[550px] h-[90%]">
            <div class="loading h-full flex justify-center items-center">Đang lấy dữ liệu biểu đồ...</div>
            <div id="finance-column" class="w-full h-full hidden"></div>
        </div>      
        <div class="flex flex-col w-[700px] h-full space-y-2 p-4">
            <div class="flex w-full space-x-10">
                <div class="w-[30%] space-y-4">
                    <h2 class="border-b border-[#595959]">Định giá</h2>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">P/E</div>
                        <div><?=number_format($finance_current_year->pe, 2)?></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">P/B</div>
                        <div><?=number_format($finance_current_year->pb, 2)?></div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">EPS</div>
                        <div><?=number_format($finance_current_year->eps, 2)?><br/><span class="text-[14px] <?=($eps_percent > 0) ? 'price-up' : 'price-down'?>"><?=($eps_percent > 0) ? '+' : ''?><?=number_format($eps_percent,2)?> %</span></div>
                    </div>
                </div>
                <div class="w-[30%] space-y-4">
                    <h2 class="border-b border-[#595959]">Hiệu quả quản lý</h2>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">ROE</div>
                        <div class="<?=($finance_current_year->roe*100 > 20) ? 'price-up' : 'price-down'?>"><?=number_format($finance_current_year->roe*100, 2)?> %</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">ROA</div>
                        <div><?=number_format($finance_current_year->roa*100, 2)?> %</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">ROIC</div>
                        <div><?=number_format($finance_current_year->roic*100, 2)?> %</div>
                    </div>
                </div>
                <div class="w-[40%] space-y-4">
                    <h2 class="border-b border-[#595959]">Khả năng sinh lời</h2>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">Biên LN gộp</div>
                        <div class="<?=($finance_current_year->grossProfitMargin*100 > 18) ? 'price-up' : 'price-down'?>"><?=number_format($finance_current_year->grossProfitMargin*100, 2)?> %</div>
                    </div>
                    <div class="flex justify-between">
                        <div class="text-[#c9cccf]">Biên LN ròng</div>
                        <div><?=number_format($finance_current_year->netProfitMargin*100, 2)?> %</div>
                    </div>
                </div>
            </div>
            <div class="w-full space-y-4">
                <h2 class="border-b border-[#595959]">Sức mạnh tài chính</h2>
                <div class="flex justify-between">
                    <div class="space-y-4 w-[230px]">
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">Tổng nợ/VCSH</div>
                            <div><?=number_format($finance_current_year->debtEquity, 2)?></div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">Tổng nợ/Tổng TS</div>
                            <div><?=number_format($finance_current_year->debtAsset, 2)?></div>
                        </div>
                    </div>
                    <div class="space-y-4 w-[230px]">
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">Thanh toán nhanh</div>
                            <div><?=number_format($finance_current_year->quickRatio, 2)?></div>
                        </div>
                        <div class="flex justify-between">
                            <div class="text-[#c9cccf]">Than toán hiện hành</div>
                            <div><?=number_format($finance_current_year->currentRatio, 2)?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>      
    </div>
    <!--div id="news" class="hidden tab h-full w-full">
        <div class="h-[355px] overflow-y-auto space-y-4">
            <?php foreach($news as $i=> $row): ?>
                <a href="company_news_detail.php?s=<?=$stockname?>&id=<?=($id+1)?>" class="flex space-x-4 h-fit items-center py-2">
                    <img src="<?=$row->imageUrl?>" alt="News Thumbnail" class="w-32 h-fit object-cover rounded">
                    <div class="flex-1">
                        <h2 class="font-semibold mb-2"><?=$row->title?></h2>
                        <p class="text-sm text-gray-400">Ngày đăng: <?=$row->publicDate?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div-->
</div>

<script type="module">
    import { drawColumnChart } from './js/highcharts-column.js';

    $("a").click(function(){
        let id = $(this).attr("data-tab");
        $(".tab").addClass('hidden');
        $("#" + id).removeClass('hidden');
        $("a").removeClass('selected');
        $(this).addClass('selected');
        if(id == 'finance'){
            if($("#finance-column").is(':empty')){
                const stockname = '<?=$stockname?>'; // Fallback example
                const api = `${URL_ROOT}/api/get_chart_finance.php`;
                drawColumnChart(api, stockname);
            }
        }
    })
</script>

<?php require_once (dirname(__DIR__).'/live/faststock_one.php'); ?>