<?php 
    if(isset($_REQUEST['s'])){
        $stockname = trim($_REQUEST['s']);
    }
    else{
        $stockname = 'vnm';
    }
?>

<script type="text/javascript" src="./stockchart/charting_library/charting_library.standalone.js"></script>
<script type="text/javascript" src="./stockchart/datafeeds/udf/dist/bundle.js"></script>
<script type="module" src="./stockchart/src/main_multitab.js"></script>
<script>
    var stockname = '<?=$stockname?>';
</script>


<div class="flex flex-col m-0 h-full space-y-2 p-2">
    <div id="chart-tabs" class="flex items-center">
        <button
        id="add-tab-btn"
        class="px-4 py-1 hover:bg-[#6c697b] focus:outline-none flex items-center"
        title="Add new chart tab"
        >
        <i class="fa-solid fa-plus"></i>
        </button>
    </div>

    <div id="chart-containers" class="relative flex-auto bg-white"></div>
</div>

