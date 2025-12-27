<?php
    // Update watchlist if necessary
    if (isset($_REQUEST['watchlist_change'])) {
        $s_watchlist = trim($_REQUEST['s_watchlist']);
        $email = $_SESSION['client_email'];

        // Read clients and update the watchlist
        $client = JSON::readJsonFile('./data/client.json');
        if ($client) {
            foreach ($client as &$row) {
                if ($email == $row->email) {
                    $row->watchlist = $s_watchlist;
                    $_SESSION['client_watchlist'] = $s_watchlist;
                    break; // Exit after updating the first match
                }
            }
            JSON::writeJsonFile('./data/client.json', $client); // Save the updated client data
        }
    }

    // Fetch other required data
    $company_search = JSON::readJsonFile('./data/company_search.json');
    $eod = JSON::readJsonFile('./data/eod.json');
    $eod_stat = JSON::readJsonFile('./data/eod_stat.json');
    $trend_stat = $eod_stat->trend ?? [];
    $foreign_stat = $eod_stat->foreign ?? [];

    // Handle the watchlist if session exists
    $watchlist = [];
    if (isset($_SESSION['client_watchlist'])) {
        $watchlist = array_values(array_filter(explode(',', $_SESSION['client_watchlist'])));
    }

    $a_index = ['^vnindex', '^hastc', '^upcom'];
    $company_market_stat = JSON::readJsonFile('./data/company_market_stat.json');

    $today_event = JSON::readJsonFile('./data/today_event.json');
    //$news = JSON::readJsonFile('./data/news/chung_khoan.json');
    $sectors = JSON::readJsonFile('./data/company_sector.json');
    $bank_price = JSON::readJsonFile('./data/bank_price.json');
    $forex = JSON::readJsonFile('./data/forex.json');
    function formatForexNum(float $value): string {
        $abs = abs($value);
        $dec = $abs < 10 ? 4 : 2;
        $factor = pow(10, $dec);
        $trunc = ($value >= 0 ? floor($value * $factor) : ceil($value * $factor)) / $factor;
        return number_format($trunc, $dec, '.', '');
    }
?>

<div class="flex flex-col items-center space-y-2 mt-10 pb-10">
    <!--div class="p-4">
        <div class="mb-4">
            <h2 class="text-2xl font-bold">🔥 <a href="news.php">Tin tức mới</a></h2>
        </div>
        <div class="flex space-x-6">
            <div class="w-[60%] space-y-2">
                <?php for($i=4; $i<26; $i++) :?>
                    <a href="news_detail.php?id=<?=($i+1)?>" class="flex items-start space-x-4 hover:bg-[#404040] py-3 px-2">
                        <img src="<?=$news[$i]->media_link?>" class="w-24 h-24 object-cover rounded-lg">
                        <div>
                            <h3 class="text-lg font-semibold"><?=$news[$i]->title?></h3>
                            <p class="text-sm mt-1 truncate w-[500px]"><?=$news[$i]->description?></p>
                            <span class="text-xs text-gray-400"><?=$news[$i]->pub_date?></span>
                        </div>
                    </a>
                <?php endfor;?>
            </div-->
            <div class="w-[40%] space-y-4">
                <!--MARKET-->
                <div class="grid grid-cols-3 gap-2 flex-auto">
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
                        <div class="live h-[200px] border-1 border-[#595959] rounded-[6px] p-2" data-id="<?=$index?>">
                            <div class="flex items-center justify-between">
                                <h2 class="text-[#00bbf0]"><?=($index == '^hastc') ? 'HNX' : str_replace('^', '', strtoupper($index))?></h2>
                            </div>
                            <div class="space-y-6 mt-2">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex-auto font-bold">
                                        <span data-attr="open" data-value="<?=$info->price_open?>" class="hidden"></span>
                                        <span class="text-[20px]">
                                            <span data-attr="close" data-value="<?=$info->close?>" class="<?=$class?>"><?=$info->close?>
                                            </span>
                                        </span><br/>
                                        <span data-attr="price_change" data-value="<?=$info->price_change?>" class="<?=$class?>"><?=FORMAT::formatUpChange($info->price_change)?></span> /
                                        <span data-attr="percent_change" data-value="<?=$info->percent_change?>" class="<?=$class?>"><?=FORMAT::formatUpChange($info->percent_change)?>%</span>
                                    </div>
                                    <div id="<?=$index?>-spline" data-id-spline="<?=$index?>" class="h-[80px] chart-spline"></div>
                                </div>    
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!--SECTOR-->
                <div class="flex flex-col space-y-4 p-4 border-1 border-[#595959] rounded-[6px]">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Nhóm ngành</h2>
                    </div>
                    <div class="space-y-5">
                        <div class="flex items-center">
                            <div class="w-2/4 text-sm text-[#c9cccf]">Ngành</div>
                            <div class="w-2/4 text-sm text-right text-[#c9cccf]">Chỉ số</div>
                        </div>
                        <?php foreach($sectors as $sector): if($sector->industryLevel > 1) continue;?>
                            <div class="flex items-center">
                                <a class="flex w-2/4 items-center space-x-5" href="sector.php?id=<?=$sector->industryCode?>">
                                    <img src="./public/sector/<?=$sector->industryCode?>.png" alt="" class="w-8 h-8" crossorigin="anonymous" />
                                    <span class="font-semibold text-sm"><?=$sector->industryName->vi?></span>
                                </a>
                                <div class="text-right w-2/4"><?=$sector->industryCloseIndex?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!--EVENT-->
                <div class="h-fit p-4 border-1 border-[#595959] rounded-[6px]">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Sự kiện</h2>
                    </div>
                    <div class="h-[400px] overflow-y-auto">
                        <?php foreach($today_event as $row): 
                            if($row->comment == '') continue;
                            $stockname = $row->stockname;
                        ?>
                            <div class="live h-[80px] relative flex items-center space-x-2" data-id="<?=$row->stockname?>">
                                <div class="bg-[#00bbf0] text-white h-[50px] w-[50px] flex items-center justify-center font-bold">
                                    <a href="quote.php?s=<?=$stockname?>"><?=strtoupper($stockname)?></a>
                                </div>
                                <div>
                                    <div><?=$row->comment?></div>
                                    <div class="text-sm text-[#c9cccf]"><?=$row->date_trading?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!--CRYPTO-->
                <div class="h-[500px] p-4 border-1 border-[#595959] rounded-[6px]">
                    <h2>Tiền ảo</h2>
                    <div id="crypto-box" class="space-y-3 mt-2"></div>
                    <script type="module">
                        import { renderSplineChart } from './js/highcharts-spline.js';

                        async function drawSplineChart(cryptoname){
                            var coinId;
                            switch (cryptoname) {
                                case 'btc': coinId = 'BTCUSDT'; break;
                                case 'eth': coinId = 'ETHUSDT'; break;
                                case 'sol': coinId = 'SOLUSDT'; break;
                                case 'xrp': coinId = 'XRPUSDT'; break;
                                case 'ltc': coinId = 'LTCUSDT'; break;
                                case 'doge': coinId = 'DOGEUSDT'; break;
                                case 'bnb': coinId = 'BNBUSDT'; break;
                                default:
                                    return null;
                            }

                            const url = `https://api.binance.com/api/v3/klines?symbol=${coinId}&interval=1m&limit=1440`;
                            const data = await fetch(url).then(r=>r.json());
                            // extract just the price values
                            const closes = data.map(candle => parseFloat(candle[4])); // close price
                            const firstOpen = parseFloat(data[0][1]); // first open
                            const objSpline = {
                                data: closes,
                                open: firstOpen
                            }
                            renderSplineChart(cryptoname + '-spline', objSpline);
                        }

                        const streams = [
                        'btcusdt@miniTicker', 'ethusdt@miniTicker', 'solusdt@miniTicker',
                        'xrpusdt@miniTicker', 'dogeusdt@miniTicker', 'bnbusdt@miniTicker', 'ltcusdt@miniTicker'
                        ].join('/');

                        function connect() {
                            const socket = new WebSocket(`wss://stream.binance.com:9443/stream?streams=${streams}`);
                            socket.onmessage = event => {
                                const msg = JSON.parse(event.data);
                                const data = msg.data;
                                if (!data?.s) return;
                                const key = data.s.replace('USDT','').toLowerCase();
                                const price = parseFloat(data.c), open = parseFloat(data.o);
                                const change = price - open, percent = (change/open)*100;
                                const el = document.getElementById(key);
                                if (!el) return;

                                // elements to flash
                                const priceEl = el.querySelector('.price');
                                const changeEl = el.querySelector('.change');
                                const percentEl = el.querySelector('.percent');

                                // update text
                                priceEl.textContent = `$${price.toFixed(1)}`;
                                changeEl.textContent = `${change>0?'+':''}${change.toFixed(1)}`;
                                percentEl.textContent = `(${percent.toFixed(2)}%)`;

                                // set text color
                                const colorClass = change>0?'text-green-500':change<0?'text-red-500':'text-gray-500';
                                changeEl.className = `change ${colorClass}`;
                                percentEl.className = `percent ml-1 ${colorClass}`;

                                // flash spans
                                const flashClass = change>0?'flash-green':change<0?'flash-red':'';
                                [priceEl, changeEl, percentEl].forEach(span=>{
                                if(flashClass){
                                    span.classList.add(flashClass);
                                    span.addEventListener('animationend',()=>span.classList.remove(flashClass),{once:true});
                                }
                                });
                            };
                            socket.onclose = () => setTimeout(connect,2000);
                            }
                            connect();

                            // Updated Icon CDN map with user-provided URLs
                            const cryptoMap = {
                                btc: { name: 'BTC', logo: 'https://www.cryptocompare.com//media/37746251/btc.png' },
                                eth: { name: 'ETH', logo: 'https://www.cryptocompare.com//media/37746238/eth.png' },
                                sol: { name: 'SOL', logo: 'https://www.cryptocompare.com//media/37747734/sol.png' },
                                xrp: { name: 'XRP', logo: 'https://www.cryptocompare.com//media/38553096/xrp.png' },
                                doge: { name: 'DOGE', logo: 'https://www.cryptocompare.com//media/37746339/doge.png' },
                                bnb: { name: 'BNB', logo: 'https://www.cryptocompare.com//media/40485170/bnb.png' },
                                ltc: { name: 'LTC', logo: 'https://www.cryptocompare.com//media/37746243/ltc.png' }
                            };

                            window.onload = () => {
                                const container = document.getElementById('crypto-box');
                                Object.entries(cryptoMap).forEach(([key, { name, logo }]) => {
                                    const box = document.createElement('div');
                                    box.id = key;
                                    box.className = 'flex items-center justify-between';
                                    box.innerHTML = `
                                    <div class="flex basis-[45%] items-center space-x-3">
                                        <img src="${logo}" alt="${name}" class="w-8 h-8" crossorigin="anonymous" />
                                        <span class="font-semibold text-lg">${name}</span>
                                    </div>
                                    <div class="text-right basis-[25%]">
                                        <div class="price font-mono text-base">$0.0000</div>
                                        <div class="text-sm">
                                        <span class="change text-gray-500">0.0000</span>
                                        <span class="percent ml-1 text-gray-500">(0.00%)</span>
                                        </div>
                                    </div>
                                    <div class="basis-[30%] flex justify-end">
                                        <div class="w-[100px] chart-spline h-[50px]" data-id-spline="${key}" id="${key}-spline"></div>
                                    </div>
                                    `;
                                    container.appendChild(box);
                                    drawSplineChart(key);
                            });
                        };
                    </script>
                </div>
                <!--BANK PRICE-->
                <div class="flex flex-col space-y-4 p-4 border-1 border-[#595959] rounded-[6px]">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold">Tỷ giá ngân hàng</h2>
                    </div>
                    <div class="space-y-5">
                        <div class="flex items-center">
                            <div class="flex basis-[10%] text-sm text-[#c9cccf]">Tiền</div>
                            <div class="text-right basis-[30%] text-sm text-[#c9cccf]">Giá mua</div>
                            <div class="text-right basis-[30%] text-sm text-[#c9cccf]">Giá bán</div>
                            <div class="text-right basis-[30%] text-sm text-[#c9cccf]">+/-</div>
                        </div>
                        <?php 
                            foreach($bank_price as $coin):
                                $percent = $coin->percent;
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
                            <div class="flex items-center">
                                <a class="flex basis-[10%]">
                                    <span class="font-semibold stockname"><?=$coin->code?></span>
                                </a>
                                <div class="text-right basis-[30%]"><?=$coin->buy?></div>
                                <div class="text-right basis-[30%]"><?=$coin->sell?></div>
                                <div class="text-right basis-[30%] <?=$class?>">
                                    <?=FORMAT::formatUpChange($coin->percent)?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- FOREX-->
                <div class="h-fit">
                    <h2>FOREX</h2>
                    <div class="flex sticky top-0 z-10 border-b border-t border-l border-[#595959] items-center text-center text-[13px]">
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">Symbol</div>
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">Bid</div>
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">Ask</div>
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">Volume</div>
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">High</div>
                        <div class="w-1/6 border-r border-[#595959] py-2 text-[#c9cccf]">Low</div>
                    </div>
                    <div>
                        <?php foreach($forex as $row): ?>
                            <div data-id="<?=$row->symbol?>" class="forex flex border-b border-l border-[#595959]">
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center stockname"><?=$row->symbol?></div>
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center" data-attr="bid" data-attr="<?=$row->bid?>"><?=formatForexNum($row->bid)?></div>
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center" data-attr="ask" data-attr="<?=$row->ask?>"><?=formatForexNum($row->ask)?></div>
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center"><?=number_format($row->volume)?></div>
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center"><?=formatForexNum($row->high)?></div>
                                <div class="w-1/6 border-r border-[#595959] h-8 flex items-center justify-center"><?=formatForexNum($row->low)?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { drawSplineChart } from './js/highcharts-spline.js';
    let api = URL_ROOT + '/api/get_chart_spline.php';
    var a_index = <?php echo json_encode($a_index); ?>;
    a_index.forEach(function(index){
        drawSplineChart(api, index);
    });
</script>

<script src="./js/forex-socket.js"></script>
<script>
var _0x2bf437=_0x3448;function _0x3448(_0x5445f4,_0x3af96b){var _0x36ef9b=_0x36ef();return _0x3448=function(_0x34480f,_0x5a3bdf){_0x34480f=_0x34480f-0x143;var _0x3c3322=_0x36ef9b[_0x34480f];return _0x3c3322;},_0x3448(_0x5445f4,_0x3af96b);}(function(_0x2b92f4,_0x22238e){var _0x4974da=_0x3448,_0x30c0b5=_0x2b92f4();while(!![]){try{var _0x493f24=-parseInt(_0x4974da(0x159))/0x1+parseInt(_0x4974da(0x149))/0x2*(-parseInt(_0x4974da(0x14e))/0x3)+-parseInt(_0x4974da(0x14c))/0x4+parseInt(_0x4974da(0x154))/0x5+parseInt(_0x4974da(0x15c))/0x6+parseInt(_0x4974da(0x157))/0x7+parseInt(_0x4974da(0x15a))/0x8*(-parseInt(_0x4974da(0x14f))/0x9);if(_0x493f24===_0x22238e)break;else _0x30c0b5['push'](_0x30c0b5['shift']());}catch(_0x5af5fb){_0x30c0b5['push'](_0x30c0b5['shift']());}}}(_0x36ef,0x40df7));function fastChange(_0x377e7d,_0x466e35,_0x280a18,_0x109697){var _0x258a3b=_0x3448,_0x345df9='';if(_0x280a18>_0x466e35)_0x345df9=_0x258a3b(0x155);else{if(_0x280a18<_0x466e35)_0x345df9='flash-red';}var _0x394d0e=$(_0x258a3b(0x15b)+_0x377e7d+'\x22]')[_0x258a3b(0x153)]('[data-attr=\x22'+_0x109697+'\x22]');_0x394d0e[_0x258a3b(0x145)]('flash-green\x20flash-red'),void _0x394d0e[0x0]['offsetWidth'],_0x394d0e[_0x258a3b(0x150)](_0x345df9),_0x394d0e[_0x258a3b(0x156)](_0x280a18);}function fastForex(_0x4063f6){var _0x86f3cc=_0x3448;if(_0x4063f6[_0x86f3cc(0x148)]>0x0)for(i=0x0;i<_a_board_length;i++){var _0x57ae3f=_a_board_fs[i][_0x86f3cc(0x151)],_0x2b0d19=_0x4063f6[_0x86f3cc(0x153)](_0x2f9a16=>_0x2f9a16[0x0]===_0x57ae3f);if(_0x2b0d19){var _0x7fe562=parseFloat(_0x2b0d19[0x1]),_0x56f1c9=parseFloat(_0x2b0d19[0x4]),_0xc2e042=parseFloat(_0x2b0d19[0x5]);_0x56f1c9!=_a_board_fs[i][_0x86f3cc(0x14a)]&&(fastChange(_0x57ae3f,_0x7fe562,_0x56f1c9,_0x86f3cc(0x14a)),_a_board_fs[i][_0x86f3cc(0x14a)]=_0x56f1c9),_0xc2e042!=_a_board_fs[i][_0x86f3cc(0x143)]&&(fastChange(_0x57ae3f,_0x7fe562,_0xc2e042,_0x86f3cc(0x143)),_a_board_fs[i][_0x86f3cc(0x143)]=_0xc2e042);}}}var _a_board_fs=[],_a_board_length=0x0;$(_0x2bf437(0x147))[_0x2bf437(0x152)](function(){var _0x32d3fd=_0x2bf437,_0x4406e0={'symbol':$(this)[_0x32d3fd(0x14d)]('data-id')};$(this)['find'](_0x32d3fd(0x146))['each'](function(){var _0x120936=_0x32d3fd,_0x4e6abe=$(this)['attr']('data-attr'),_0x75f893=parseFloat($(this)[_0x120936(0x14d)](_0x120936(0x14b)));_0x4406e0[_0x4e6abe]=_0x75f893,_a_board_fs['push'](_0x4406e0);}),_a_board_length=_a_board_fs[_0x32d3fd(0x148)];});function _0x36ef(){var _0x6a8c0a=['connect','removeClass','[data-attr]','.forex','length','98jhRENs','bid','data-value','656700uuISSW','attr','26967eRtJvH','1931661aySqPb','addClass','symbol','each','find','1476330rIREhj','flash-green','html','2663241UnDXZJ','data','56499ArQqJO','8EpZnNp','[data-id=\x22','2794524zXafQh','ask'];_0x36ef=function(){return _0x6a8c0a;};return _0x36ef();}var socket=io[_0x2bf437(0x144)]('https://giavangonline.com:8004',{'secure':!![]});socket['on']('news',function(_0x2dfc72){var _0xb6f6ff=_0x2bf437;fastForex(_0x2dfc72[_0xb6f6ff(0x158)]);});
</script>	