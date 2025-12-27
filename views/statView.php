<?php
$view = isset($_REQUEST['v']) ? trim($_REQUEST['v']) : 'vn30';

$eod = JSON::readJsonFile('./data/eod.json');

function getTreeMapData($market_id, $eod){
    $company_market = JSON::readJsonFile('./data/company_market.json');
    $a_stock = $company_market[$market_id];

    $data = array();

    foreach($a_stock as $stockname){
        $obj = new stdClass();
        $obj->stockname = $stockname;
        $obj->percent_change = $eod->$stockname->percent_change;
        $obj->close = $eod->$stockname->close;;
        array_push($data, $obj);
    }  

    return $data;
}

$treemap_data = array();

switch ($view) {
    case 'vn30':
        $treemap_data = getTreeMapData(3, $eod);
        break;      
    case 'hnx30':
        $treemap_data = getTreeMapData(4, $eod);
        break;        
}
?>
<div class="h-full py-4 px-6 space-y-4">
    <div class="flex items-center h-[5%] space-x-1">
        <a href="stat.php?v=vn30" class="menu rounded-xl px-4 py-2 <?=($view == 'vn30') ?'selected':''?>">VN30</a>
        <a href="stat.php?v=hnx30" class="menu rounded-xl px-4 py-2 <?=($view == 'hnx30') ?'selected':''?>">HNX30</a>
    </div>

    <div class="h-[95%]">
        <div id="stock-treemap" class="grid grid-cols-6 gap-1"></div>
    </div>
</div>

<script>
    let view = <?php echo json_encode($view); ?>;
    let stocks = <?php echo json_encode($treemap_data); ?>;
    // Render the treemap based on the current stock data.
    function renderTreemap() {
        const container = document.getElementById('stock-treemap');
        // Clear any existing content.
        container.innerHTML = '';

        // Sort stocks by percentChange in descending order.
        stocks.sort((a, b) => b.percent_change - a.percent_change);

        // For each stock, create a block element.
        stocks.forEach(stock => {
            const block = document.createElement('a');
            block.className = 'live relative p-4 text-center text-[#FFF] text-[15px]';
            block.id = stock.stockname;

            if(view == 'market'){
                function getKeyByValue(object, value) {
                    return Object.keys(object).find(key => object[key] === value);
                }

                let markets_name = <?php echo json_encode($markets_name); ?>;
                let market_id = parseInt(getKeyByValue(markets_name, stock.stockname));
                block.href = URL_ROOT + '/market.php?id=' + market_id;
            }
            else{
                block.href = URL_ROOT + '/quote.php?s=' + stock.stockname;
            }

            // Change the background color based on whether the percent change is positive or negative.
            if (stock.percent_change > 0) {
                block.classList.add('bg-[#34ae7b]');
            } else if (stock.percent_change < 0){
                block.classList.add('bg-[#ed6561]');
            } else{
                block.classList.add('bg-[#f1ad57]');
            }

            // You can adjust the height/width dynamically; here, we use a fixed height.
            block.style.height = '80px';

            block.innerHTML = `
            <div class="font-bold uppercase whitespace-nowrap overflow-hidden">${stock.stockname}</div>
            <div><span class="percent_change text-sm">${stock.close + ' / ' + stock.percent_change}</span>%</div>
            <div class="absolute right-2 top-2 flex justify-center">
                <div class="arrow-up hidden text-[#00802b] text-[20px]"><i class="fa-solid fa-arrow-up"></i></div>
                <div class="arrow-down hidden text-[#b30000] text-[20px]"><i class="fa-solid fa-arrow-down"></i></div>
                <div class="arrow-minus hidden text-[#ffff00] text-[20px]"><i class="fa-solid fa-minus"></i></div>
            </div>
            `;
            container.appendChild(block);
        });
    }

    renderTreemap();
</script>

<?php if( (Date("H")>=9 && Date("D") != "Sun" &&  Date("D") != "Sat") && (Date("H")<=15 ) ): ?>
    <script type="text/javascript" src="js/pako.js"></script>
    <script src="https://www.cophieu68.vn:7764/socket.io/socket.io.js"></script>
    <script>
        if( typeof(io) !== 'undefined' ){
            switch(view) {
                case 'vn30':
                    var socket = io.connect('https://www.cophieu68.vn:7764');
                    break;
                case 'hnx30':
                    var socket = io.connect('https://www.cophieu68.vn:7767');
                    break;
            }
            socket.on('news', function (data) {
                getRealtimeStock(data);
            });
        }

        function getRealtimeStock(b64Data){
            if( b64Data == "" ) return;

            var strData     = atob(b64Data);

            // Convert binary string to character-number array
            var charData    = strData.split('').map(function(x){return x.charCodeAt(0);});

            // Turn number array into byte-array
            var binData     = new Uint8Array(charData);

            // Pako magic
            var data        = pako.inflate(binData);

            // Convert gunzipped byteArray back to ascii string:
            var strData     = String.fromCharCode.apply(null, new Uint16Array(data));

            var obj = new Array();
            var tmp_array_data = new Array();
            tmp_array_data = strData.split("\n");
            if( tmp_array_data.length > 0 ){
                for(var i=0; i<tmp_array_data.length; i++){
                    obj[i] = new Array();
                    obj[i] = tmp_array_data[i].split("|");
                }
            }

            var total = obj.length;
            if ( total > 0 ){
                for( i=0; i<total; i++){
                    var stockname = obj[i][0];
                    let obj_by_stock = stocks.find((o) => { return o.stockname === stockname });

                    if(obj_by_stock){
                        var price_change = obj[i][9];
                        var open = obj[i][26];
                        var new_percent = (price_change/open*100).toFixed(2);
                        var old_percent = obj_by_stock.percent_change;
                        
                        if(new_percent != old_percent){
                            obj_by_stock.percent_change = new_percent;
                            let el = '';
                            if(new_percent > 0){
                                el = 'arrow-up';
                            }
                            else if(new_percent < 0){
                                el = 'arrow-down';
                            }
                            else{
                                el = 'arrow-minus';
                            }
                            $('#' + stockname).find('.' + el).removeClass('hidden');
                        }
                    }
                }
            }
            setTimeout( function() { renderTreemap(); }, 1000 );
        }
    </script>
<?php endif; ?>