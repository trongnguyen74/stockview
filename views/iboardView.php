<?php
    $view = isset($_REQUEST['v']) ? trim($_REQUEST['v']) : 'vn30';

    $eod = JSON::readJsonFile('./data/eod.json');
    $a_index = array('^vnindex', '^hastc', '^upcom', '^vn30');

    function getStockLive($market_id, $eod){
        $company_market = JSON::readJsonFile('./data/company_market.json');
        $a_stock = $company_market[$market_id];
        $stock_live = array();
        foreach($a_stock as $stockname){
            array_push($stock_live, $eod->$stockname);
        }    
        return $stock_live;
    }

    switch ($view) {
        case 'hose':
            $stock_live = getStockLive(0, $eod);
            break; 
        case 'hnx':
            $stock_live = getStockLive(1, $eod);
            break;            
        case 'upcom':
            $stock_live = getStockLive(2, $eod);
            break;                                   
        case 'vn30':
            $stock_live = getStockLive(3, $eod);
            break;  
        case 'hnx30':
            $stock_live = getStockLive(4, $eod);
            break; 
    }
    function setClassPrice($price, $floor, $ceiling, $open){
        $class='';
        if( $price == $floor )
            $class = "price-floor";
        elseif( $price == $ceiling )
            $class = "price-ceiling";
        elseif( $price < $open )
            $class = "price-down";
        elseif( $price > $open )
            $class = "price-up";
        elseif( $price == $open )
            $class = "price-open";
        return $class;
    }

    $market_stat = JSON::readJsonFile('./data/company_market_stat.json');
?>

<div class="p-2 space-y-3 bg-[#252a31]">
    <div class="grid grid-cols-4 gap-6">
        <?php 
            foreach($a_index as $i=>$index):
                $last = $eod->$index;
                $name = str_replace('^', '', ($index == '^hastc') ? 'hnx' : $index);
                $percent = $last->percent_change;
                $stat = $market_stat[$i];
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
            <div class="relative py-2 px-4">
                <div id="<?=$index?>-spline" data-id-spline="<?=$index?>" class="mx-auto w-[60%] h-20 chart-spline"></div>
                <div class="live" data-id="<?=$last->stockname?>">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl uppercase stockname"><?=$name?></h2>
                        <span>
                            <span class="text-[16px]">
                                <span class="<?=$class?>" data-attr="close" data-value="<?=$last->close?>"><?=$last->close?></span>
                            </span>&nbsp;
                            <span class="<?=$class?>" data-attr="price_change" data-value="<?=$last->price_change?>"><?=FORMAT::formatUpChange($last->price_change)?></span>&nbsp; 
                            <span class="<?=$class?>" data-attr="percent_change" data-value="<?=$last->percent_change?>"><?=FORMAT::formatUpChange($last->percent_change)?> %</span>
                        </span>
                    </div>
                    <div class="flex justify-between text-[12px]">
                        <div>KL: <span data-attr="volume" data-value="<?=$last->volume?>"><?=number_format($last->volume)?></span></div>
                        <div>GT: <span data-attr="value" data-value="<?=$last->value?>"><?=number_format($last->value)?></span> tỷ</div>
                        <div class="flex space-x-2">
                            <div class="flex price-up space-x-1">
                                <div><i class="fa-solid fa-up-long"></i></div>
                                <div><?=$stat->up?></div>
                            </div>
                            <div class="flex price-down space-x-1">
                                <div><i class="fa-solid fa-down-long"></i>                                </div>
                                <div><?=$stat->down?></div>
                            </div>
                            <div class="flex price-open space-x-1">
                                <div><i class="fa-solid fa-arrows-left-right"></i></div>
                                <div><?=$stat->open?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;?>
    </div>
    <div class="mt-5">
        <div class="w-full flex items-center h-[5%] w-fit border-b-4 border-[#2d72d2]">
            <a href="?v=hose" class="menu rounded-t-md <?=($view == 'hose') ?' selected':''?>">HOSE</a>
            <a href="?v=hnx" class="menu rounded-t-md <?=($view == 'hnx') ?' selected':''?>">HNX</a>
            <a href="?v=upcom" class="menu rounded-t-md <?=($view == 'upcom') ?' selected':''?>">UPCOM</a>
            <a href="?v=vn30" class="menu rounded-t-md <?=($view == 'vn30') ?' selected':''?>">VN30</a>
            <a href="?v=hnx30" class="menu rounded-t-md <?=($view == 'hnx30') ?' selected':''?>">HNX30</a>
        </div>
        <div class="flex sticky top-0 z-10 border-b border-t border-l border-[#595959] items-center text-center text-[13px]">
            <div class="w-1/23 border-r border-[#595959] py-2">Mã</div>
            <div class="w-1/23 border-r border-[#595959] py-2">TC</div>
            <div class="w-1/23 border-r border-[#595959] py-2">Trần</div>
            <div class="w-1/23 border-r border-[#595959] py-2">Sàn</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GM 3</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLM 3</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GM 2</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLM 2</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GM 1</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLM 1</div>
            <div class="w-1/23 border-r border-[#595959] py-2 bg-[#373740]">Giá khớp</div>
            <div class="w-1/23 border-r border-[#595959] py-2 bg-[#373740]">KL khớp</div>
            <div class="w-1/23 border-r border-[#595959] py-2 bg-[#373740]">+/-</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GB 1</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLB 1</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GB 2</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLB 2</div>
            <div class="w-1/23 border-r border-[#595959] py-2">GB 3</div>
            <div class="w-1/23 border-r border-[#595959] py-2">KLB 3</div>
            <div class="w-1/23 border-r border-[#595959] py-2">Cao</div>
            <div class="w-1/23 border-r border-[#595959] py-2">Thấp</div>
            <div class="w-1/23 border-r border-[#595959] py-2">NN mua</div>
            <div class="w-1/23 border-r border-[#595959] py-2">NN bán</div>
        </div>
        <div class="w-full max-h-[260px] overflow-y-auto text-[14px]">
            <div class="min-w-[640px]">
                <?php 
                    foreach ($stock_live as $i => $row):
                        if($row->stockname == '') continue;
                        if($row->price_change > 0){
                            $class = 'price-up';
                        }
                        else if($row->price_change < 0){
                            $class = 'price-down';
                        }
                        else if($row->price_change == 0){
                            $class = 'price-open';
                        }                
                ?>
                    <div class="flex <?=($i%2==0) ? 'bg-[#1a1a1a]' : ''?> border-b border-[#595959] live" data-id="<?=$row->stockname?>">
                        <div class="w-1/23 text-center border-r border-l border-[#595959] py-1 uppercase font-bold text-[#00b8e6]"><a href="quote.php?s=<?=$row->stockname?>"><?=$row->stockname?></a></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="open" data-value="<?=$row->open?>" class="price-open"><?=$row->open?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="ceiling" data-value="<?=$row->ceiling?>" class="price-ceiling"><?=$row->ceiling?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="floor" data-value="<?=$row->floor?>" class="price-floor"><?=$row->floor?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="gm3" data-value="<?=$row->gm3?>" class="<?=setClassPrice($row->gm3, $row->floor, $row->ceiling, $row->open)?>"><?=$row->gm3?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klm3" data-value="<?=$row->klm3/100?>"><?=number_format($row->klm3/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span class="<?=setClassPrice($row->gm2, $row->floor, $row->ceiling, $row->open)?>" data-attr="gm2" data-value="<?=$row->gm2?>"><?=$row->gm2?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klm2" data-value="<?=$row->klm2/100?>"><?=number_format($row->klm2/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="gm1" data-value="<?=$row->gm1?>" class="<?=setClassPrice($row->gm1, $row->floor, $row->ceiling, $row->open)?>"><?=$row->gm1?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klm1" data-value="<?=$row->klm1/100?>"><?=number_format($row->klm1/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] bg-[#2f2f37] py-1 pr-1"><span data-attr="close" data-value="<?=$row->close?>" class="<?=setClassPrice($row->close, $row->floor, $row->ceiling, $row->open)?>"><?=$row->close?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] bg-[#2f2f37] py-1 pr-1"><span data-attr="current_volume" data-value="<?=$row->current_volume?>"><?=number_format($row->current_volume/1000)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] bg-[#2f2f37] py-1 pr-1"><span data-attr="percent_change" data-value="<?=$row->percent_change?>" class="<?=$class?>"><?=FORMAT::formatUpChange($row->percent_change)?>%</span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="gb1" data-value="<?=$row->gb1?>" class="<?=setClassPrice($row->gb1, $row->floor, $row->ceiling, $row->open)?>"><?=$row->gb1?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klb1" data-value="<?=$row->klb1/100?>"><?=number_format($row->klb1/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="gb2" data-value="<?=$row->gb2?>" class="<?=setClassPrice($row->gb2, $row->floor, $row->ceiling, $row->open)?>"><?=$row->gb2?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klb2" data-value="<?=$row->klb2/100?>"><?=number_format($row->klb2/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="gb3" data-value="<?=$row->gb3?>" class="<?=setClassPrice($row->gb3, $row->floor, $row->ceiling, $row->open)?>"><?=$row->gb3?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="klb3" data-value="<?=$row->klb3/100?>"><?=number_format($row->klb3/100)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="price_highest" data-value="<?=$row->price_highest?>" class="<?=setClassPrice($row->price_highest, $row->floor, $row->ceiling, $row->open)?>"><?=$row->price_highest?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1"><span data-attr="price_lowest" data-value="<?=$row->price_lowest?>" class="<?=setClassPrice($row->price_lowest, $row->floor, $row->ceiling, $row->open)?>"><?=$row->price_lowest?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1" align="right"><span data-attr="foreigner_buy_volume" data-value="<?=$row->foreigner_buy_volume?>"><?=number_format($row->foreigner_buy_volume/1000)?></span></div>
                        <div class="w-1/23 text-right border-r border-[#595959] py-1 pr-1" align="right"><span data-attr="foreigner_sell_volume" data-value="<?=$row->foreigner_sell_volume?>"><?=number_format($row->foreigner_sell_volume/1000)?></span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { drawSplineChart } from './js/highcharts-spline.js';

    $('.live').each(function(){
        $(this).find('[data-attr]').each(function(){
            var el = $(this);
            var name = $(this).attr('data-attr');
            var value = parseInt($(this).attr('data-value'));
            if(value == 0 && name != 'price_change' && name != 'percent_change'){
                $(this).html('');
            }
        });
    });

    let a_spline = <?php echo json_encode($a_index); ?>;
    let api = URL_ROOT + '/api/get_chart_spline.php';
    a_spline.forEach(function(stockname){
        drawSplineChart(api, stockname);
    });
</script>

<script type="text/javascript" src="js/pako.js"></script>
<script type="text/javascript" src="js/socket.io.js"></script>




<script>
let view = '<?=$view?>';

const tradingHours = () => {
    const now = new Date();
    const day = now.getDay();
    const hour = now.getHours();
    return day !== 0 && day !== 6 && hour >= 9 && hour <= 15;
};

const stockSet = new Set();
const boardData = {};
const updateQueue = new Map();
let rafScheduled = false;

const queueUpdate = (stockname, key, value, cssClass) => {
    const el = document.querySelector(`[data-id="${stockname}"] [data-attr="${key}"]`);
    if (!el) return;
    updateQueue.set(el, { value, cssClass });
    if (!rafScheduled) {
        rafScheduled = true;
        requestAnimationFrame(applyQueuedUpdates);
    }
};

const applyQueuedUpdates = () => {
    updateQueue.forEach(({ value, cssClass }, el) => {
        el.innerHTML = value || '';
        el.className = cssClass;
        el.classList.add('animated-update');
        el.style.transition = 'background-color 0.2s, color 0.2s';
        el.style.backgroundColor = '#4d4d4d';
        el.style.color = '#fff';

        setTimeout(() => {
            el.classList.remove('animated-update');
            el.style.backgroundColor = '';
            el.style.color = '';
        }, 500);
    });
    updateQueue.clear();
    rafScheduled = false;
};

const formatNumber = x => x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

const getStockOnlineStockname = () => {
    document.querySelectorAll('.live').forEach(row => {
        const stockname = row.getAttribute('data-id').toLowerCase();
        stockSet.add(stockname);
        boardData[stockname] = {};
        row.querySelectorAll('[data-attr]').forEach(cell => {
            const attr = cell.getAttribute('data-attr');
            const value = cell.getAttribute('data-value');
            boardData[stockname][attr] = value;
        });
    });
};

const changeAffectedFS = (stockname, key, newValue, priceType = 0) => {
    if (!boardData[stockname]) return;
    const oldValue = boardData[stockname][key];
    if (newValue == oldValue) return;

    let cssClass = '';
    const d = new Date();
    const hour = d.getHours();

    if (priceType === 1) {
        const ceiling = parseFloat(boardData[stockname]['ceiling']);
        const floor = parseFloat(boardData[stockname]['floor']);
        const open = parseFloat(boardData[stockname]['open']);
        if (newValue == ceiling) cssClass = 'price-ceiling';
        else if (newValue == floor) cssClass = 'price-floor';
        else if (newValue > open) cssClass = 'price-up';
        else if (newValue < open) cssClass = 'price-down';
        else cssClass = 'price-open';
    } else if (key === 'price_change' || key === 'percent_change') {
        cssClass = newValue > 0 ? 'price-up' : newValue < 0 ? 'price-down' : 'price-open';
    }

    boardData[stockname][key] = newValue;

    if (priceType === 2) {
        const el = document.querySelector(`[data-id="${stockname}"] [data-attr="close"]`);
        if (el) el.className = cssClass;
    }

    if (document.querySelector(`[data-id="${stockname}"] [data-attr="${key}"]`)) {
        let displayValue = newValue;
        if (key === 'percent_change') displayValue += '%';
        if (parseFloat(displayValue) === 0 && key !== 'price_change' && key !== 'percent_change') displayValue = '';
        queueUpdate(stockname, key, displayValue, cssClass);
    }
};

const getAffectedFastStock = b64Data => {
    if (!b64Data) return;

    const binStr = atob(b64Data);
    const byteArray = Uint8Array.from([...binStr].map(ch => ch.charCodeAt(0)));
    const data = pako.inflate(byteArray);
    const str = new TextDecoder().decode(data);
    const rows = str.split('\n').filter(Boolean);

    rows.forEach(row => {
        const fields = row.split('|');
        const stockname = fields[0];
        if (!stockSet.has(stockname)) return;

        const isIndex = ['^vnindex', '^vn30', '^hastc', '^upcom'].includes(stockname);
        const close = parseFloat(fields[7]);
        const priceChange = parseFloat(fields[9]);
        const open = parseFloat(fields[26]);
        const percentChange = isIndex ? fields[10] : ((priceChange / open) * 100).toFixed(2);

        if (isIndex) {
            changeAffectedFS(stockname, 'close', close);
            changeAffectedFS(stockname, 'price_change', priceChange, 2);
            changeAffectedFS(stockname, 'percent_change', percentChange, 2);
            changeAffectedFS(stockname, 'volume', fields[8]);
            changeAffectedFS(stockname, 'value', fields[27]);
            return;
        }

        const hour = new Date().getHours();

        const parseVolume = v => formatNumber(parseFloat(v.replace(/,/g, '')) / 100);

        const updates = {
            gm3: fields[1],
            klm3: parseVolume(fields[2]),
            gm2: fields[3],
            klm2: parseVolume(fields[4]),
            gm1: fields[5] || (parseInt(fields[6]) > 0 ? (hour < 12 ? 'ATO' : 'ATC') : ''),
            klm1: parseVolume(fields[6]),
            close,
            price_change: priceChange,
            percent_change: percentChange,
            gb1: fields[11] || (parseInt(fields[12]) > 0 ? (hour < 12 ? 'ATO' : 'ATC') : ''),
            klb1: parseVolume(fields[12]),
            gb2: fields[13],
            klb2: parseVolume(fields[14]),
            gb3: fields[15],
            klb3: parseVolume(fields[16]),
            price_highest: fields[18],
            price_lowest: fields[19],
            current_volume: parseVolume(fields[8]),
            foreigner_buy_volume: parseVolume(fields[21]),
            foreigner_sell_volume: parseVolume(fields[22])
        };

        Object.entries(updates).forEach(([key, value]) => {
            const isPrice = ['gm1','gm2','gm3','gb1','gb2','gb3','close','price_highest','price_lowest'].includes(key) ? 1 : 0;
            changeAffectedFS(stockname, key, value, isPrice);
        });
    });
};

if (tradingHours() && typeof io !== 'undefined') {
    let api;
    switch (view) {
        case 'hose':
        case 'vn30':
            api = 'https://www.cophieu68.vn:7764'; break;
        case 'hnx':
        case 'hnx30':
            api = 'https://www.cophieu68.vn:7767'; break;
        case 'upcom':
            api = 'https://www.cophieu68.vn:7770'; break;
    }
    const socket = io.connect(api);
    socket.on('news', getAffectedFastStock);
}

document.addEventListener('DOMContentLoaded', getStockOnlineStockname);
</script>