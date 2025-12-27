<?php
$id = intval(trim(($_REQUEST['id'])));
if (!$id) $id = 3000;
$eod = JSON::readJsonFile('./data/eod.json');
$company_name = JSON::readJsonFile('./data/company_search.json');
$a_stock = array_column($company_name, 'stockname');
$sector = JSON::readJsonFile('./data/company_sector.json');
$index = array_search($id, array_column($sector, 'industryCode'));
$detail = $index !== false ? $sector[$index] : (object)[
    'industryName' => (object)['vi' => 'Không xác định'],
    'percentChange' => 0,
    'industryCloseIndex' => 0,
    'listCompany' => [],
    'up' => 0, 'down' => 0, 'open' => 0
];
$list_company = $detail->listCompany;
$data = [];
foreach ($list_company as $row) {
    $stockname = strtolower($row->symbol);
    if (isset($eod->$stockname)) {
        $e = $eod->$stockname;
        $e->stockname = $stockname;
        $e->percent_change = FORMAT::formatUpChange($e->percent_change);
        $idx = array_search($stockname, $a_stock);
        $e->company_name = $company_name[$idx]->company_name ?? '';
        $e->price_bar = FORMAT::displayPriceInBar(
            $e->price_lowest,
            $e->price_highest,
            $e->price_open,
            $e->close
        ) ?? '';
        $data[] = $e;
    }
}
?>

<div class="p-4">
  <div class="w-full flex items-center justify-between">
    <div class="flex space-x-4 items-center">
      <h2><?= $detail->industryName->vi ?></h2>
      <div class="flex items-center space-x-3 text-[22px] font-bold <?= $detail->percentChange>0?'price-up':'price-down' ?>">
        <div><?= $detail->industryCloseIndex ?></div>
        <div><?= FORMAT::formatUpChange($detail->percentChange) ?>%</div>
      </div>
    </div>
    <div class="flex space-x-10">
        <div class="flex flex-col"><span class="text-[#c9cccf]">SL cổ phiếu</span><span><?= count($detail->listCompany) ?></span></div>
        <div class="flex flex-col"><span class="text-[#c9cccf]">Tăng giá</span><span class="price-up"><?= $detail->up ?></span></div>
        <div class="flex flex-col"><span class="text-[#c9cccf]">Giảm giá</span><span class="price-down"><?= $detail->down ?></span></div>
        <div class="flex flex-col"><span class="text-[#c9cccf]">Đứng giá</span><span class="price-open"><?= $detail->open ?></span></div>
    </div>
  </div>

  <div class="mt-6 space-y-4">
    <div class="flex space-x-1">
      <a href="#" data-sort="vol" class="menu rounded-xl selected px-4 py-2">Tích cực</a>
      <a href="#" data-sort="up" class="menu rounded-xl px-4 py-2">Tăng giá</a>
      <a href="#" data-sort="down" class="menu rounded-xl px-4 py-2">Giảm giá</a>
    </div>
    <div class="flex sticky top-0 z-10 border border-[#595959] items-center text-[#c9cccf] text-[12px]">
      <div class="w-[12.5%] py-2 px-2">Mã</div>
      <div class="w-[25%] py-2">Tên công ty</div>
      <div class="w-[12.5%] py-2 text-right pr-1">Giá</div>
      <div class="w-[12.5%] py-2 text-right pr-1">+/-</div>
      <div class="w-[12.5%] py-2 text-right pr-1">Khối lượng</div>
      <div class="w-[25%] py-2 text-center pr-1">Cao/thấp</div>
    </div>
    <div id="stock-table" class="max-h-[320px] overflow-y-auto"></div>
  </div>
</div>

<script>
  const stockData = <?= json_encode($data) ?>;

  function renderRows(data) {
      const container = document.getElementById('stock-table');
      container.innerHTML = '';
      data.forEach(c => {
          const pct = c.percent_change;
          const cls = pct>0?'price-up':(pct<0?'price-down':'price-open');
          const row = `
              <div id="${c.stockname}_row" data-id="${c.stockname}" class="live flex items-center border-b border-[#595959] h-[80px]">
                  <a href="quote.php?s=${c.stockname}" class="stockname w-[12.5%] py-1 px-2">${c.stockname.toUpperCase()}</a>
                  <div class="w-[25%] py-1 truncate" title="${c.company_name}">${c.company_name}</div>
                  <div class="w-[12.5%] py-1 text-right px-1"><span data-attr="close" data-value="${c.close}">${c.close}</span></div>
                  <div class="w-[12.5%] py-1 text-right px-1 ${cls}" data-attr="percent" data-value="${pct}">${pct}%</div>
                  <div class="w-[12.5%] py-1 text-right px-1" data-attr="volume" data-value="${c.volume}">${Number(c.volume).toLocaleString()}</div>
                  <div class="flex justify-center w-[25%] px-4"><div class="w-[60%]">${c.price_bar}</div></div>
              </div>`;
          container.innerHTML += row;
        });
  }

  function sortData(type) {
    let arr = [...stockData];
    if (type==='vol') arr.sort((a,b)=>b.volume-a.volume);
    if (type==='up') arr.sort((a,b)=>b.percent_change-a.percent_change);
    if (type==='down') arr.sort((a,b)=>a.percent_change-b.percent_change);
    renderRows(arr);
  }

  document.querySelectorAll('a[data-sort]').forEach(tab=>{
      tab.addEventListener('click', e=>{
          e.preventDefault();
          document.querySelectorAll('a[data-sort]').forEach(t=>t.classList.remove('selected'));
          tab.classList.add('selected');
          sortData(tab.getAttribute('data-sort'));
      });
  });

  // initial
  sortData('vol');
</script>

<?php
    if( (Date("H")>=9 && Date("D") != "Sun" &&  Date("D") != "Sat") && (Date("H")<=15 ) ):
?>

<script type="text/javascript" src="js/pako.js"></script>
<script src="https://www.cophieu68.vn:7764/socket.io/socket.io.js"></script>
<script type="text/javascript">
function addCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function removeAffectedFS(stockname){
    var el = $('[data-id="' + stockname + '"]').find('[data-attr="close"]');
	  el.removeClass('bg-[#42b883] bg-[#e84a5f] bg-[#f1ad57]');
}

function fsClose(stockname, close, open){
    var sclass = ''; 
    if(close > open) sclass = 'bg-[#42b883]';
    else if(close < open) sclass = 'bg-[#e84a5f]';
    else if(close == open) sclass = 'bg-[#f1ad57]';

    var el = $('[data-id="' + stockname + '"]').find('[data-attr="close"]');
    el.addClass(sclass);
    el.html(close.toFixed(2));
    setTimeout("removeAffectedFS('" + stockname + "')", 1*500);
}

function fsPercent(stockname, percent){
    var sclass = ''; 
    if(percent > 0) sclass = 'price-up';
    else if(percent < 0) sclass = 'price-down';
    else if(percent == 0) sclass = 'price-open';

    var el = $('[data-id="' + stockname + '"]').find('[data-attr="percent"]');
    el.removeClass('price-up price-down price-open');
    el.addClass(sclass);
    if(percent > 0) percent = '+' + percent;
    el.html(percent + '%');
}

function fsVolume(stockname, volume){
    var el = $('[data-id="' + stockname + '"]').find('[data-attr="volume"]');
    el.html(addCommas(volume));
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function getAffectedFastStock(b64Data){
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

	var obj = [];
	var tmp_data = strData.split("\n");
	if( tmp_data.length > 0 ){
      for(var i=0; i<tmp_data.length; i++){
          obj[i] = new Array();
          obj[i] = tmp_data[i].split("|");
      }
	}
	if ( obj.length > 0 ){
      for( i=0; i<_a_board_length; i++){
          var stockname = _a_board_fs[i].stockname;
          var fs = obj.find(subArr => subArr[0] === stockname);
          var currentData = stockData.find(subArr => subArr.stockname === stockname);

          if(fs){
              var close = parseFloat(fs[7]);
              var price_change = fs[9];
              var open = fs[26];
              var percent = (price_change/open*100).toFixed(2);
              var volume = fs[8];

              if(close != _a_board_fs[i].close){
                fsClose(stockname, close, open);
                _a_board_fs[i].close = close;
                currentData.close = close;
              }
              if(percent != _a_board_fs[i].percent){
                fsPercent(stockname, percent);
                _a_board_fs[i].percent = percent;
                currentData.percent_change = percent;
              }
              if(Number(volume) != _a_board_fs[i].volume){
                fsVolume(stockname, volume);
                _a_board_fs[i].volume = volume;
                currentData.volume = volume;
              }
          }
      } // end for data
	}// endif total
}

var _a_board_fs = [];
var _a_board_length = 0;
window.onload = function(){
    $('.live').each(function(){
        var s_obj = {
            stockname: $(this).attr('data-id')
        };

        $(this).find('[data-attr]').each(function(){
            var attr = $(this).attr('data-attr');
            var value = parseFloat($(this).attr('data-value'));
            s_obj[attr] = value;
            _a_board_fs.push(s_obj);
        });
        _a_board_length = _a_board_fs.length;
    });
    var socket = io.connect('https://www.cophieu68.vn:7764');
    socket.on('news', function (data) {
        getAffectedFastStock(data);
        //console.log(_a_board_fs);
    });
};
</script>
<?php endif; ?>